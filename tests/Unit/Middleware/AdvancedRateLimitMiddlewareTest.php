<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 *
 * Sistema UNN - Unit tests para AdvancedRateLimitMiddleware
 *
 * Spec: .kiro/specs/advanced-security-performance (task 8.4)
 *
 * Cobre os caminhos principais do middleware de rate limit:
 *   1. Bloqueio por User-Agent suspeito (DEFAULT_UA_PATTERNS - sqlmap)
 *   2. Bloqueio por threshold excedido na janela deslizante
 *   3. Whitelist: IP whitelisted nao e checado contra UA nem threshold
 *   4. Extensao de bloqueio em tentativas adicionais (Requirement 5.6)
 *   5. Fail-open: se backing stores falham, request continua via $next
 *   6. isWhitelisted le settings configuraveis (rate_limit_whitelist)
 *
 * Setup isolado por teste:
 *   - SQLite proprio com tabelas `settings` e `rate_limit_blocks`
 *   - storage_path() reapontado para um diretorio temporario
 *
 * Requirements: 5.1, 5.2, 5.4, 5.6, 5.7
 */

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AdvancedRateLimitMiddleware;
use App\Models\Setting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class AdvancedRateLimitMiddlewareTest extends TestCase
{
    private string $sqlitePath;
    private string $storageTemp;

    protected function setUp(): void
    {
        parent::setUp();

        // Banco SQLite isolado por arquivo (mesmo padrao usado em
        // PointsExchangeServiceValuationTest e similares).
        $this->sqlitePath = database_path('testing-advanced-rate-limit-middleware.sqlite');
        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }
        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('rate_limit_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip_address', 45);
            $table->string('reason', 100);
            $table->timestamp('blocked_until');
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });

        // Storage dedicado para os arquivos de janela deslizante.
        $this->storageTemp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'unn_rl_test_' . uniqid('', true);
        @mkdir($this->storageTemp, 0775, true);
        $this->app->useStoragePath($this->storageTemp);

        Setting::flushRuntimeCache();
    }

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();
        Carbon::setTestNow();

        if (class_exists(Mockery::class)) {
            Mockery::close();
        }

        DB::disconnect('sqlite');

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        if (!empty($this->storageTemp) && is_dir($this->storageTemp)) {
            $this->rmdirRecursive($this->storageTemp);
        }

        parent::tearDown();
    }

    private function rmdirRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path)) {
                $this->rmdirRecursive($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    private function makeRequest(string $userAgent, string $ip, string $path = '/'): Request
    {
        $request = Request::create($path, 'GET', [], [], [], [
            'REMOTE_ADDR' => $ip,
            'HTTP_USER_AGENT' => $userAgent,
        ]);

        // Garante que header User-Agent fique acessivel via $request->header().
        $request->headers->set('User-Agent', $userAgent);

        return $request;
    }

    // -----------------------------------------------------------------
    // 1. Bloqueio por User-Agent suspeito
    // -----------------------------------------------------------------

    public function test_blocks_request_with_sqlmap_user_agent(): void
    {
        $middleware = new AdvancedRateLimitMiddleware();
        $request = $this->makeRequest('sqlmap/1.5.6 (https://sqlmap.org)', '203.0.113.10');

        $nextInvoked = false;
        $next = function () use (&$nextInvoked) {
            $nextInvoked = true;
            return response('ok', 200);
        };

        $response = $middleware->handle($request, $next);

        $this->assertFalse(
            $nextInvoked,
            'next() nao deve ser invocado quando o User-Agent e suspeito'
        );
        $this->assertSame(403, $response->getStatusCode());

        // O bloqueio deve ser persistido em rate_limit_blocks (Requirement 5.3).
        $this->assertTrue(
            DB::table('rate_limit_blocks')
                ->where('ip_address', '203.0.113.10')
                ->where('reason', 'suspicious_user_agent')
                ->where('blocked_until', '>', Carbon::now())
                ->exists(),
            'Bloqueio por UA suspeito deve persistir em rate_limit_blocks'
        );
    }

    public function test_blocks_request_with_other_default_suspicious_user_agents(): void
    {
        $middleware = new AdvancedRateLimitMiddleware();

        $cases = [
            ['nikto/2.1.6', '203.0.113.21'],
            ['Mozilla/5.0 nuclei', '203.0.113.22'],
            ['gobuster/3.5', '203.0.113.23'],
        ];

        foreach ($cases as [$ua, $ip]) {
            $request = $this->makeRequest($ua, $ip);
            $response = $middleware->handle($request, fn () => response('ok', 200));

            $this->assertSame(
                403,
                $response->getStatusCode(),
                "UA suspeito '{$ua}' deveria gerar 403"
            );
        }
    }

    // -----------------------------------------------------------------
    // 2. Bloqueio por threshold excedido
    // -----------------------------------------------------------------

    public function test_blocks_request_when_threshold_exceeded(): void
    {
        Setting::set('rate_limit_threshold', '3');
        Setting::set('rate_limit_block_duration', '15');

        $middleware = new AdvancedRateLimitMiddleware();
        $next = fn () => response('ok', 200);
        $ip = '198.51.100.42';

        $statuses = [];
        for ($i = 0; $i < 5; $i++) {
            $request = $this->makeRequest('Mozilla/5.0 (legit-browser)', $ip);
            $statuses[] = $middleware->handle($request, $next)->getStatusCode();
        }

        // Primeiras 3 requisicoes ainda dentro do limite (count <= threshold).
        $this->assertSame(200, $statuses[0]);
        $this->assertSame(200, $statuses[1]);
        $this->assertSame(200, $statuses[2]);
        // A partir da 4a, count > threshold => 429 + bloqueio.
        $this->assertSame(429, $statuses[3]);
        $this->assertSame(429, $statuses[4]);

        $this->assertTrue(
            DB::table('rate_limit_blocks')
                ->where('ip_address', $ip)
                ->where('blocked_until', '>', Carbon::now())
                ->exists(),
            'Threshold excedido deve gerar bloqueio ativo em rate_limit_blocks'
        );
    }

    public function test_too_many_requests_response_includes_retry_after_header(): void
    {
        Setting::set('rate_limit_threshold', '2');
        Setting::set('rate_limit_block_duration', '10');

        $middleware = new AdvancedRateLimitMiddleware();
        $next = fn () => response('ok', 200);
        $ip = '198.51.100.77';

        $response = null;
        for ($i = 0; $i < 4; $i++) {
            $request = $this->makeRequest('Mozilla/5.0 (legit)', $ip);
            $response = $middleware->handle($request, $next);
        }

        $this->assertSame(429, $response->getStatusCode());
        $this->assertTrue(
            $response->headers->has('Retry-After'),
            'Resposta 429 deve conter cabecalho Retry-After'
        );
        $this->assertGreaterThan(0, (int) $response->headers->get('Retry-After'));
    }

    // -----------------------------------------------------------------
    // 3. Whitelist
    // -----------------------------------------------------------------

    public function test_allows_whitelisted_ip_even_with_suspicious_user_agent(): void
    {
        $whitelistedIp = '10.0.0.5';
        Setting::set('rate_limit_whitelist', json_encode([$whitelistedIp]));
        // Threshold pequeno para garantir que so a whitelist pode "salvar"
        // o IP de qualquer regra de bloqueio.
        Setting::set('rate_limit_threshold', '1');

        $middleware = new AdvancedRateLimitMiddleware();
        $next = fn () => response('ok', 200);

        $statuses = [];
        for ($i = 0; $i < 5; $i++) {
            // UA suspeito + volume acima do threshold: ambos cenarios que
            // bloqueariam um IP nao whitelisted.
            $request = $this->makeRequest('sqlmap/1.5.6', $whitelistedIp);
            $statuses[] = $middleware->handle($request, $next)->getStatusCode();
        }

        $this->assertSame([200, 200, 200, 200, 200], $statuses);
        $this->assertFalse(
            DB::table('rate_limit_blocks')->where('ip_address', $whitelistedIp)->exists(),
            'IP em whitelist nunca deve ser bloqueado'
        );
    }

    public function test_is_whitelisted_reads_settings_dynamically(): void
    {
        $middleware = new AdvancedRateLimitMiddleware();

        $this->assertFalse($middleware->isWhitelisted('1.2.3.4'));
        $this->assertFalse($middleware->isWhitelisted(''));

        Setting::set('rate_limit_whitelist', json_encode(['1.2.3.4', '5.6.7.8']));
        Setting::flushRuntimeCache();

        $this->assertTrue($middleware->isWhitelisted('1.2.3.4'));
        $this->assertTrue($middleware->isWhitelisted('5.6.7.8'));
        $this->assertFalse($middleware->isWhitelisted('9.9.9.9'));
    }

    // -----------------------------------------------------------------
    // 4. Extensao de bloqueio em tentativas adicionais
    // -----------------------------------------------------------------

    public function test_extends_block_on_additional_attempts(): void
    {
        Setting::set('rate_limit_block_increment', '5');

        $ip = '192.0.2.50';
        Carbon::setTestNow('2026-01-01 12:00:00');

        // Bloqueio pre-existente: now + 15min, attempts=1.
        $initialUntil = Carbon::now()->copy()->addMinutes(15);
        DB::table('rate_limit_blocks')->insert([
            'ip_address' => $ip,
            'reason' => 'rate_limit_exceeded',
            'blocked_until' => $initialUntil,
            'attempts' => 1,
            'created_at' => Carbon::now(),
        ]);

        $middleware = new AdvancedRateLimitMiddleware();
        $request = $this->makeRequest('Mozilla/5.0', $ip);
        $response = $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(429, $response->getStatusCode());

        $row = DB::table('rate_limit_blocks')->where('ip_address', $ip)->first();
        $this->assertNotNull($row);
        $this->assertSame(2, (int) $row->attempts, 'attempts deve incrementar a cada tentativa adicional');

        $newUntil = Carbon::parse($row->blocked_until);
        $this->assertTrue(
            $newUntil->greaterThan($initialUntil),
            'blocked_until deve ser estendido em tentativas adicionais'
        );
        // Property 6: extensao = increment a partir do blocked_until atual.
        $this->assertSame(
            $initialUntil->copy()->addMinutes(5)->getTimestamp(),
            $newUntil->getTimestamp(),
            'Nova janela = blocked_until anterior + rate_limit_block_increment'
        );
    }

    public function test_extends_block_repeatedly_on_each_additional_attempt(): void
    {
        Setting::set('rate_limit_block_increment', '5');

        $ip = '192.0.2.51';
        Carbon::setTestNow('2026-01-01 12:00:00');

        $initialUntil = Carbon::now()->copy()->addMinutes(15);
        DB::table('rate_limit_blocks')->insert([
            'ip_address' => $ip,
            'reason' => 'rate_limit_exceeded',
            'blocked_until' => $initialUntil,
            'attempts' => 1,
            'created_at' => Carbon::now(),
        ]);

        $middleware = new AdvancedRateLimitMiddleware();

        // Tres tentativas adicionais durante a janela de bloqueio.
        for ($i = 0; $i < 3; $i++) {
            $response = $middleware->handle(
                $this->makeRequest('Mozilla/5.0', $ip),
                fn () => response('ok', 200)
            );
            $this->assertSame(429, $response->getStatusCode());
        }

        $row = DB::table('rate_limit_blocks')->where('ip_address', $ip)->first();
        $this->assertSame(4, (int) $row->attempts);
        // 15min iniciais + 3 incrementos de 5min = 30min totais a partir de now.
        $this->assertSame(
            $initialUntil->copy()->addMinutes(5 * 3)->getTimestamp(),
            Carbon::parse($row->blocked_until)->getTimestamp()
        );
    }

    // -----------------------------------------------------------------
    // 5. Fail-open
    // -----------------------------------------------------------------

    public function test_fails_open_when_rate_limit_blocks_table_is_unavailable(): void
    {
        // Simula falha total do backing store: a tabela `rate_limit_blocks`
        // some. As chamadas internas a DB::table() lancam excecao, que e
        // tratada pelos try/catch internos (e, em ultima instancia, pelo
        // try/catch externo do handle()), garantindo que o request passe.
        Schema::drop('rate_limit_blocks');

        $middleware = new AdvancedRateLimitMiddleware();
        $nextInvoked = false;
        $next = function () use (&$nextInvoked) {
            $nextInvoked = true;
            return response('ok', 200);
        };

        $request = $this->makeRequest('Mozilla/5.0 (legit)', '203.0.113.99');
        $response = $middleware->handle($request, $next);

        $this->assertTrue($nextInvoked, 'Request deve fluir mesmo com DB indisponivel');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', (string) $response->getContent());
    }

    public function test_fails_open_when_storage_directory_is_not_writable(): void
    {
        // Aponta o storage para um caminho impossivel de criar/escrever:
        // um arquivo regular usado como "diretorio". O middleware tenta
        // mkdir/file_put_contents com supressao de erros (`@`) e segue.
        $sentinel = $this->storageTemp . DIRECTORY_SEPARATOR . 'sentinel.lock';
        file_put_contents($sentinel, 'lock');
        // Rebind storage para apontar para um path que nao pode virar diretorio.
        $this->app->useStoragePath($sentinel);

        $middleware = new AdvancedRateLimitMiddleware();
        $nextInvoked = false;
        $next = function () use (&$nextInvoked) {
            $nextInvoked = true;
            return response('ok', 200);
        };

        $request = $this->makeRequest('Mozilla/5.0 (legit)', '203.0.113.100');
        $response = $middleware->handle($request, $next);

        $this->assertTrue(
            $nextInvoked,
            'Request deve passar mesmo quando o storage de janela deslizante esta inacessivel'
        );
        $this->assertSame(200, $response->getStatusCode());
    }
}
