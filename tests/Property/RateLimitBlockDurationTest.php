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
 * Sistema UNN - Property test (Property 6) para extensao de bloqueio
 *
 * Spec: .kiro/specs/advanced-security-performance (task 8.3)
 *
 * Property 6: Block Duration Extension
 *
 *   Para qualquer initial_duration (em minutos) e qualquer N >= 0
 *   tentativas adicionais durante o periodo de bloqueio, o
 *   total_block_duration final no registro `rate_limit_blocks` SHALL
 *   ser:
 *
 *     total_block = initial_duration + (N x increment)
 *
 *   onde:
 *     - increment vem de Setting('rate_limit_block_increment') (default: 5);
 *     - cada tentativa adicional executa AdvancedRateLimitMiddleware::blockIp()
 *       com o increment configurado, e o middleware soma o increment ao
 *       valor atual de blocked_until (vide AdvancedRateLimitMiddleware::blockIp).
 *
 *   ESTRATEGIA:
 *   Congelamos o relogio com Carbon::setTestNow() para eliminar drift
 *   entre wall-clock e calculos do middleware. Verificamos a diferenca
 *   entre `blocked_until` e `created_at` da linha em rate_limit_blocks
 *   apos N+1 chamadas a blockIp() (1 inicial + N extensoes), em minutos.
 *
 *   Tambem validamos:
 *     - attempts == 1 + N apos as N extensoes;
 *     - extensoes mantem a mesma linha (uma unica row por IP ativo);
 *     - reason atualizado para o ultimo motivo informado.
 *
 * Validates: Requirements 5.6
 */

namespace Tests\Property;

use App\Http\Middleware\AdvancedRateLimitMiddleware;
use App\Models\Setting;
use Carbon\Carbon;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Tests\TestCase;

class RateLimitBlockDurationTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    /**
     * Increment usado nas extensoes (em minutos). Mantemos um valor
     * fixo conhecido para calcular o total esperado de forma exata,
     * espelhando rate_limit_block_increment configurado via Setting.
     */
    private const FIXED_INCREMENT = 5;

    /**
     * IP unico por iteracao para evitar interferencia entre rodadas
     * (cada iteracao usa o suffix do timestamp). Aqui guardamos um
     * contador para gerar IPs distintos por chamada de propriedade.
     */
    private int $ipCounter = 0;

    private AdvancedRateLimitMiddleware $middleware;

    /**
     * PHPUnit 10 removeu PHPUnit\Util\Test::parseTestMethodAnnotations()
     * no qual o Eris 0.14.x ainda se apoia. Retornar [] faz a trait
     * operar com defaults (100 iteracoes).
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    /**
     * Verifica conectividade com o banco antes de acionar RefreshDatabase.
     * Se o banco estiver indisponivel, marca o teste como skipped sem
     * falhar a suite (Property 6 depende de inserts reais em
     * rate_limit_blocks).
     */
    protected function setUp(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Banco de dados indisponivel: ' . $e->getMessage());
        }

        parent::setUp();

        // Limpa cache do Setting e injeta o increment fixo via cache
        // estatico para evitar I/O no banco a cada iteracao.
        Setting::flushRuntimeCache();
        $this->setSettingRuntime([
            'rate_limit_block_increment' => (string) self::FIXED_INCREMENT,
        ]);

        $this->middleware = app(AdvancedRateLimitMiddleware::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Setting::flushRuntimeCache();

        parent::tearDown();
    }

    /**
     * Property 6: total_block_duration == initial_duration + (N x increment)
     *
     * Geradores:
     *   - initial_duration: choose(1, 60) minutos. Espelha o range
     *     da setting `rate_limit_block_duration` (default 15) e mantem
     *     compatibilidade com o requirement (que e expressado em
     *     minutos no codigo, embora o prompt cite segundos).
     *   - n_attempts: choose(0, 10). N == 0 cobre o caso degenerado
     *     "sem extensoes" (total_block == initial_duration).
     *
     * Validates: Requirements 5.6
     */
    public function test_extension_is_linear_in_attempts(): void
    {
        $this
            ->forAll(
                Generators::choose(1, 60),  // initial_duration (minutos)
                Generators::choose(0, 10)   // N tentativas adicionais
            )
            ->then(function (int $initial, int $n): void {
                // Congela o relogio para que created_at e blocked_until
                // sejam computados a partir do mesmo "now".
                $now = Carbon::create(2024, 6, 15, 12, 0, 0);
                Carbon::setTestNow($now);

                // IP unico por iteracao para evitar carry-over entre
                // rodadas Eris. Limpa qualquer registro residual da
                // mesma linha (defensivo).
                $ip = $this->nextTestIp();
                DB::table('rate_limit_blocks')->where('ip_address', $ip)->delete();

                // 1) Bloqueio inicial: cria a linha com initial_duration.
                $this->middleware->blockIp($ip, $initial, 'initial');

                // 2) N tentativas adicionais durante o periodo de
                //    bloqueio. Cada chamada estende em FIXED_INCREMENT.
                for ($i = 0; $i < $n; $i++) {
                    $this->middleware->blockIp(
                        $ip,
                        self::FIXED_INCREMENT,
                        'rate_limit_exceeded'
                    );
                }

                // Recupera a unica linha persistida para o IP.
                $rows = DB::table('rate_limit_blocks')
                    ->where('ip_address', $ip)
                    ->get();

                $this->assertCount(
                    1,
                    $rows,
                    "Property 6 violada: esperado 1 registro para IP {$ip}, obtido " . $rows->count()
                );

                $row = $rows->first();
                $createdAt = Carbon::parse($row->created_at);
                $blockedUntil = Carbon::parse($row->blocked_until);

                // total_block em minutos == diff entre blocked_until e
                // created_at. Usamos diffInMinutes(absolute=true) para
                // obter o numero inteiro de minutos.
                $totalBlock = $createdAt->diffInMinutes($blockedUntil, true);
                $expected = $initial + ($n * self::FIXED_INCREMENT);

                $this->assertSame(
                    $expected,
                    (int) $totalBlock,
                    sprintf(
                        'Property 6 violada: total_block=%d minutos, esperado=%d '
                            . '(initial=%d + N=%d x increment=%d). '
                            . 'created_at=%s blocked_until=%s ip=%s',
                        (int) $totalBlock,
                        $expected,
                        $initial,
                        $n,
                        self::FIXED_INCREMENT,
                        $createdAt->toDateTimeString(),
                        $blockedUntil->toDateTimeString(),
                        $ip
                    )
                );

                // attempts == 1 (insercao inicial) + N (extensoes).
                $this->assertSame(
                    1 + $n,
                    (int) $row->attempts,
                    sprintf(
                        'Property 6 violada: attempts=%d, esperado=%d para n=%d (ip=%s)',
                        (int) $row->attempts,
                        1 + $n,
                        $n,
                        $ip
                    )
                );

                // Reason persistido: insercao usa "initial"; extensoes
                // sobrescrevem para "rate_limit_exceeded". Quando N == 0,
                // a reason permanece a do bloqueio inicial.
                $expectedReason = $n > 0 ? 'rate_limit_exceeded' : 'initial';
                $this->assertSame(
                    $expectedReason,
                    (string) $row->reason,
                    sprintf(
                        'Property 6 violada: reason=%s, esperado=%s para n=%d',
                        $row->reason,
                        $expectedReason,
                        $n
                    )
                );
            });
    }

    /* -------------------------------------------------------------- */
    /* Helpers                                                         */
    /* -------------------------------------------------------------- */

    /**
     * Gera um IPv4 unico no espaco de teste 203.0.113.0/24
     * (RFC 5737 TEST-NET-3) por iteracao, evitando colisao com IPs
     * de outros testes da suite Property.
     */
    private function nextTestIp(): string
    {
        $this->ipCounter++;
        $octet = $this->ipCounter % 250 + 1; // 1..250

        return "203.0.113.{$octet}";
    }

    /**
     * Injeta valores diretamente no cache estatico do Setting para
     * evitar dependencia de DB durante a iteracao de propriedades.
     *
     * @param array<string, mixed> $values
     */
    private function setSettingRuntime(array $values): void
    {
        $reflection = new ReflectionClass(Setting::class);

        $cacheProp = $reflection->getProperty('runtimeCache');
        $cacheProp->setAccessible(true);

        $loadedProp = $reflection->getProperty('runtimeCacheLoaded');
        $loadedProp->setAccessible(true);

        $cacheProp->setValue(null, $values);
        $loadedProp->setValue(null, true);
    }
}
