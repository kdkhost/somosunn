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
 * Sistema UNN - Unit tests para AnomalyDetectorService
 *
 * Spec: .kiro/specs/advanced-security-performance (task 15.4)
 *
 * Cobre:
 *   1) recordLoginAttempt(ip, success=false) - cada falha incrementa
 *      contador na janela deslizante; success=true e ignorado.
 *   2) recordUpload(userId) - conta uploads por usuario na janela.
 *   3) recordWebhook(source, valid=false) - conta webhooks invalidos.
 *   4) checkThresholds() - retorna anomalias recentes nao auto-bloqueadas.
 *   5) Thresholds configuraveis via Setting (nao hardcoded). Defaults
 *      sao usados quando nada e configurado.
 *   6) Notificacao de email despachada via SendGenericTemplateEmail
 *      quando anomalia e detectada (Bus::fake).
 *   7) Auto-block via WAF (waf_ip_blocklist) quando habilitado em
 *      settings; auto_blocked = true no AnomalyEvent.
 *   8) Fallback (Requirement 11.7): falha do dispatch de notificacao
 *      preserva o registro em anomaly_events sem propagar excecao;
 *      notified_at permanece null.
 *
 * Setup isolado por teste:
 *   - SQLite em arquivo proprio com tabelas users, settings,
 *     anomaly_events, waf_ip_blocklist (mesmo padrao usado em
 *     AdvancedRateLimitMiddlewareTest).
 *   - Cache::driver('array') para janela deslizante.
 *
 * Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6, 11.7
 */

namespace Tests\Unit\Services;

use App\Jobs\SendGenericTemplateEmail;
use App\Models\AnomalyEvent;
use App\Models\Setting;
use App\Services\AnomalyDetectorService;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

class AnomalyDetectorServiceTest extends TestCase
{
    private string $sqlitePath;
    private AnomalyDetectorService $service;

    /**
     * O job SendGenericTemplateEmail (producao) declara
     * `public string $queue` que entra em conflito com a trait
     * `Queueable` sob as regras estritas de composicao de PHP 8.5+.
     * O composer.json restringe PHP a <=8.4, entao em ambientes
     * suportados o caminho de notificacao roda normalmente. Sob
     * 8.5+ pulamos apenas os testes que disparam a notificacao,
     * preservando os demais. Removivel quando producao migrar para
     * 8.5 ajustando o tipo da propriedade.
     */
    private function skipIfNotificationLoadIncompatible(): void
    {
        if (PHP_VERSION_ID >= 80500) {
            $this->markTestSkipped(
                'SendGenericTemplateEmail e incompativel com PHP 8.5+ '
                . '(composer.json: ^8.1|^8.2|^8.3|^8.4). Teste valido '
                . 'apenas em PHP <= 8.4.'
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // SQLite isolado por teste (mesmo padrao do
        // AdvancedRateLimitMiddlewareTest).
        $this->sqlitePath = database_path('testing-anomaly-detector-service.sqlite');
        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }
        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();

        // Cache em memoria para a janela deslizante.
        config()->set('cache.default', 'array');
        Cache::store('array')->flush();

        Setting::flushRuntimeCache();
        $this->resetSettingRuntimeCache();

        $this->service = new AnomalyDetectorService();
    }

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();
        $this->resetSettingRuntimeCache();
        Carbon::setTestNow();
        Cache::store('array')->flush();

        DB::disconnect('sqlite');

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    private function createSchema(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('member');
            $table->string('level')->nullable();
            $table->timestamps();
        });

        Schema::create('anomaly_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 50);
            $table->string('source_ip', 45)->nullable();
            $table->unsignedBigInteger('source_user_id')->nullable();
            $table->string('source_identifier', 255)->nullable();
            $table->integer('threshold_value');
            $table->integer('actual_value');
            $table->integer('window_minutes');
            $table->timestamp('notified_at')->nullable();
            $table->boolean('auto_blocked')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        // waf_ip_blocklist NAO e criada por padrao; testes que
        // exercitam auto-block a criam explicitamente para validar
        // o caminho de bloqueio.
    }

    private function createWafBlocklistTable(): void
    {
        Schema::create('waf_ip_blocklist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cidr', 45);
            $table->binary('ip_start');
            $table->binary('ip_end');
            $table->text('reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('source', 30)->default('manual');
            $table->boolean('auto_generated')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reseta o cache estatico de Setting via reflection. Necessario
     * porque flushRuntimeCache() volta runtimeCacheLoaded=false, mas
     * algumas chamadas anteriores podem ter populado o array.
     */
    private function resetSettingRuntimeCache(): void
    {
        $reflection = new ReflectionClass(Setting::class);

        $cacheProp = $reflection->getProperty('runtimeCache');
        $cacheProp->setAccessible(true);
        $cacheProp->setValue(null, []);

        $loadedProp = $reflection->getProperty('runtimeCacheLoaded');
        $loadedProp->setAccessible(true);
        $loadedProp->setValue(null, false);
    }

    /**
     * Injeta valores no cache estatico de Setting (mesma tecnica do
     * AnomalyThresholdTest property test). Evita updateOrCreate em
     * cenarios em que multiplas chaves sao definidas em sequencia.
     *
     * @param array<string, mixed> $values
     */
    private function setSettingRuntime(array $values): void
    {
        $reflection = new ReflectionClass(Setting::class);

        $cacheProp = $reflection->getProperty('runtimeCache');
        $cacheProp->setAccessible(true);
        $cacheProp->setValue(null, $values);

        $loadedProp = $reflection->getProperty('runtimeCacheLoaded');
        $loadedProp->setAccessible(true);
        $loadedProp->setValue(null, true);
    }

    // =========================================================
    // 1) recordLoginAttempt
    // =========================================================

    public function test_record_login_attempt_success_does_not_count(): void
    {
        Bus::fake();

        // Threshold baixo para tornar facil de "estourar" se a logica
        // contasse sucessos por engano.
        $this->setSettingRuntime(['anomaly_login_threshold' => '2']);

        for ($i = 0; $i < 10; $i++) {
            $this->service->recordLoginAttempt('203.0.113.1', true);
        }

        $this->assertSame(
            0,
            AnomalyEvent::query()->count(),
            'Logins bem-sucedidos NUNCA devem gerar anomaly_events'
        );
        Bus::assertNotDispatched(SendGenericTemplateEmail::class);
    }

    public function test_record_login_attempt_failures_flag_anomaly_when_threshold_exceeded(): void
    {
        $this->skipIfNotificationLoadIncompatible();
        Bus::fake();

        // Threshold = 3; admin_alert_email fornecido para evitar
        // queries auxiliares no users.
        $this->setSettingRuntime([
            'anomaly_login_threshold' => '3',
            'admin_alert_email' => 'audit@unn.test',
        ]);

        $ip = '203.0.113.10';

        // 3 falhas: ainda dentro do threshold (count <= threshold).
        $this->service->recordLoginAttempt($ip, false);
        $this->service->recordLoginAttempt($ip, false);
        $this->service->recordLoginAttempt($ip, false);

        $this->assertSame(
            0,
            AnomalyEvent::query()->where('source_ip', $ip)->count(),
            'Ate atingir o threshold, nenhum anomaly_event deve ser gerado'
        );

        // 4a falha: count > threshold => 1 anomaly_event.
        $this->service->recordLoginAttempt($ip, false);

        $rows = AnomalyEvent::query()->where('source_ip', $ip)->get();
        $this->assertCount(1, $rows);

        $row = $rows->first();
        $this->assertSame(AnomalyDetectorService::TYPE_FAILED_LOGINS, $row->type);
        $this->assertSame(3, (int) $row->threshold_value);
        $this->assertSame(4, (int) $row->actual_value);
        $this->assertSame(5, (int) $row->window_minutes);
        $this->assertNull($row->source_user_id);
        $this->assertNull($row->source_identifier);

        // 5a falha: gera mais 1 anomaly (count=5 > 3).
        $this->service->recordLoginAttempt($ip, false);
        $this->assertSame(2, AnomalyEvent::query()->where('source_ip', $ip)->count());
    }

    public function test_record_login_attempt_ignores_blank_ip(): void
    {
        Bus::fake();

        $this->setSettingRuntime(['anomaly_login_threshold' => '1']);

        for ($i = 0; $i < 5; $i++) {
            $this->service->recordLoginAttempt('   ', false);
        }

        $this->assertSame(0, AnomalyEvent::query()->count());
        Bus::assertNotDispatched(SendGenericTemplateEmail::class);
    }

    // =========================================================
    // 2) recordUpload
    // =========================================================

    public function test_record_upload_counts_per_user_and_flags_when_threshold_exceeded(): void
    {
        $this->skipIfNotificationLoadIncompatible();
        Bus::fake();

        $this->setSettingRuntime([
            'anomaly_upload_threshold' => '2',
            'admin_alert_email' => 'audit@unn.test',
        ]);

        $userId = 77;

        // 2 uploads: dentro do threshold.
        $this->service->recordUpload($userId);
        $this->service->recordUpload($userId);
        $this->assertSame(0, AnomalyEvent::query()->count());

        // 3o upload: count=3 > 2 => 1 anomaly.
        $this->service->recordUpload($userId);

        $row = AnomalyEvent::query()->where('source_user_id', $userId)->first();
        $this->assertNotNull($row);
        $this->assertSame(AnomalyDetectorService::TYPE_UPLOAD_FLOOD, $row->type);
        $this->assertSame(2, (int) $row->threshold_value);
        $this->assertSame(3, (int) $row->actual_value);
        $this->assertSame(10, (int) $row->window_minutes);
        $this->assertNull($row->source_ip);
    }

    public function test_record_upload_ignores_invalid_user_id(): void
    {
        Bus::fake();

        $this->setSettingRuntime(['anomaly_upload_threshold' => '1']);

        $this->service->recordUpload(0);
        $this->service->recordUpload(-5);

        $this->assertSame(0, AnomalyEvent::query()->count());
    }

    // =========================================================
    // 3) recordWebhook
    // =========================================================

    public function test_record_webhook_valid_does_not_count(): void
    {
        Bus::fake();

        $this->setSettingRuntime(['anomaly_webhook_threshold' => '1']);

        for ($i = 0; $i < 10; $i++) {
            $this->service->recordWebhook('sumup', true);
        }

        $this->assertSame(0, AnomalyEvent::query()->count());
        Bus::assertNotDispatched(SendGenericTemplateEmail::class);
    }

    public function test_record_webhook_invalid_flags_anomaly_when_threshold_exceeded(): void
    {
        $this->skipIfNotificationLoadIncompatible();
        Bus::fake();

        $this->setSettingRuntime([
            'anomaly_webhook_threshold' => '2',
            'admin_alert_email' => 'audit@unn.test',
        ]);

        $source = 'sumup';

        $this->service->recordWebhook($source, false);
        $this->service->recordWebhook($source, false);
        $this->assertSame(0, AnomalyEvent::query()->count());

        // 3a invalidacao: count=3 > 2 => anomaly.
        $this->service->recordWebhook($source, false);

        $row = AnomalyEvent::query()
            ->where('type', AnomalyDetectorService::TYPE_INVALID_WEBHOOKS)
            ->first();
        $this->assertNotNull($row);
        $this->assertSame($source, $row->source_identifier);
        $this->assertSame(2, (int) $row->threshold_value);
        $this->assertSame(3, (int) $row->actual_value);
        $this->assertSame(10, (int) $row->window_minutes);
        $this->assertNull($row->source_ip);
        $this->assertNull($row->source_user_id);
    }

    // =========================================================
    // 4) checkThresholds
    // =========================================================

    public function test_check_thresholds_returns_recent_non_blocked_anomalies(): void
    {
        Bus::fake();

        Carbon::setTestNow('2026-07-01 12:00:00');

        // 1 anomaly recente nao bloqueada.
        AnomalyEvent::create([
            'type' => AnomalyDetectorService::TYPE_FAILED_LOGINS,
            'source_ip' => '203.0.113.5',
            'source_user_id' => null,
            'source_identifier' => null,
            'threshold_value' => 5,
            'actual_value' => 9,
            'window_minutes' => 5,
            'auto_blocked' => false,
            'created_at' => Carbon::now()->subHour(),
        ]);

        // 1 anomaly antiga (>24h) - nao deve aparecer.
        AnomalyEvent::create([
            'type' => AnomalyDetectorService::TYPE_FAILED_LOGINS,
            'source_ip' => '203.0.113.6',
            'threshold_value' => 5,
            'actual_value' => 12,
            'window_minutes' => 5,
            'auto_blocked' => false,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // 1 anomaly recente, mas auto-bloqueada - nao deve aparecer.
        AnomalyEvent::create([
            'type' => AnomalyDetectorService::TYPE_UPLOAD_FLOOD,
            'source_ip' => '203.0.113.7',
            'threshold_value' => 20,
            'actual_value' => 25,
            'window_minutes' => 10,
            'auto_blocked' => true,
            'created_at' => Carbon::now()->subMinutes(30),
        ]);

        $result = $this->service->checkThresholds();

        $this->assertCount(1, $result);
        $this->assertSame(AnomalyDetectorService::TYPE_FAILED_LOGINS, $result[0]['type']);
        $this->assertSame('203.0.113.5', $result[0]['source_ip']);
        $this->assertSame(5, $result[0]['threshold_value']);
        $this->assertSame(9, $result[0]['actual_value']);
        $this->assertFalse($result[0]['auto_blocked']);
    }

    public function test_check_thresholds_returns_empty_array_when_table_unavailable(): void
    {
        Schema::drop('anomaly_events');

        $result = $this->service->checkThresholds();

        $this->assertSame([], $result);
    }

    // =========================================================
    // 5) Thresholds configuraveis via Setting (nao hardcoded)
    // =========================================================

    public function test_get_thresholds_returns_default_values_when_no_settings(): void
    {
        // Sem settings, defaults: 10/20/5 e auto_block=false.
        $thresholds = $this->service->getThresholds();

        $this->assertSame(10, $thresholds['login']);
        $this->assertSame(20, $thresholds['upload']);
        $this->assertSame(5, $thresholds['webhook']);
        $this->assertFalse($thresholds['auto_block']);
    }

    public function test_get_thresholds_reads_configured_values_from_settings(): void
    {
        $this->setSettingRuntime([
            'anomaly_login_threshold' => '7',
            'anomaly_upload_threshold' => '15',
            'anomaly_webhook_threshold' => '3',
            'anomaly_auto_block' => '1',
        ]);

        $thresholds = $this->service->getThresholds();

        $this->assertSame(7, $thresholds['login']);
        $this->assertSame(15, $thresholds['upload']);
        $this->assertSame(3, $thresholds['webhook']);
        $this->assertTrue($thresholds['auto_block']);
    }

    public function test_threshold_setting_overrides_default(): void
    {
        $this->skipIfNotificationLoadIncompatible();
        Bus::fake();

        // Default e 10. Configuramos 1 para garantir que o servico
        // realmente le do Setting (e nao usa o constante hardcoded).
        $this->setSettingRuntime([
            'anomaly_login_threshold' => '1',
            'admin_alert_email' => 'audit@unn.test',
        ]);

        $ip = '203.0.113.20';
        $this->service->recordLoginAttempt($ip, false);
        $this->assertSame(0, AnomalyEvent::query()->count(), 'count=1 == threshold => sem anomaly');

        $this->service->recordLoginAttempt($ip, false);
        $this->assertSame(
            1,
            AnomalyEvent::query()->count(),
            'count=2 > threshold=1 => 1 anomaly (threshold configurado prevaleceu sobre o default)'
        );
    }

    // =========================================================
    // 6) Notificacao de email (Bus::fake)
    // =========================================================

    public function test_dispatches_email_notification_when_anomaly_detected(): void
    {
        $this->skipIfNotificationLoadIncompatible();
        Bus::fake();

        $this->setSettingRuntime([
            'anomaly_login_threshold' => '1',
            'admin_alert_email' => 'audit@unn.test',
        ]);

        $ip = '203.0.113.30';

        // 2 falhas: count=2 > threshold=1 => 1 anomaly + 1 dispatch.
        $this->service->recordLoginAttempt($ip, false);
        $this->service->recordLoginAttempt($ip, false);

        Bus::assertDispatched(SendGenericTemplateEmail::class, function (SendGenericTemplateEmail $job): bool {
            return $job->toEmail === 'audit@unn.test'
                && str_contains($job->subject, 'Anomalia detectada')
                && str_contains($job->htmlContent, 'failed_logins');
        });

        // Apos sucesso de dispatch, notified_at deve ter sido marcado.
        $row = AnomalyEvent::query()->where('source_ip', $ip)->first();
        $this->assertNotNull($row);
        $this->assertNotNull($row->notified_at, 'notified_at deve ser preenchido apos dispatch bem-sucedido');
    }

    public function test_uses_superadmin_user_email_when_setting_not_configured(): void
    {
        $this->skipIfNotificationLoadIncompatible();
        Bus::fake();

        // Cria um Superadmin via tabela users; nenhum admin_alert_email.
        DB::table('users')->insert([
            'name' => 'SuperAdmin Test',
            'email' => 'super@unn.test',
            'role' => 'superadmin',
            'level' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->setSettingRuntime([
            'anomaly_login_threshold' => '1',
            // 'admin_alert_email' explicitamente ausente.
        ]);

        $this->service->recordLoginAttempt('203.0.113.31', false);
        $this->service->recordLoginAttempt('203.0.113.31', false);

        Bus::assertDispatched(SendGenericTemplateEmail::class, function (SendGenericTemplateEmail $job): bool {
            return $job->toEmail === 'super@unn.test';
        });
    }

    // =========================================================
    // 7) Auto-block via WAF (waf_ip_blocklist)
    // =========================================================

    public function test_auto_blocks_ip_when_enabled_in_settings(): void
    {
        $this->skipIfNotificationLoadIncompatible();
        Bus::fake();

        $this->createWafBlocklistTable();

        $this->setSettingRuntime([
            'anomaly_login_threshold' => '1',
            'anomaly_auto_block' => '1',
            'admin_alert_email' => 'audit@unn.test',
        ]);

        $ip = '203.0.113.40';

        $this->service->recordLoginAttempt($ip, false);
        $this->service->recordLoginAttempt($ip, false);

        // Anomaly deve estar marcada como auto_blocked.
        $row = AnomalyEvent::query()->where('source_ip', $ip)->first();
        $this->assertNotNull($row);
        $this->assertTrue(
            (bool) $row->auto_blocked,
            'auto_blocked deve ser true quando anomaly_auto_block=1 e WAF esta disponivel'
        );

        // Insercao em waf_ip_blocklist com reason apropriada.
        $blocked = DB::table('waf_ip_blocklist')
            ->where('cidr', $ip . '/32')
            ->where('reason', 'anomaly:' . AnomalyDetectorService::TYPE_FAILED_LOGINS)
            ->first();
        $this->assertNotNull($blocked, 'IP deveria ter sido inserido em waf_ip_blocklist');
        $this->assertSame('auto_brute_force', $blocked->source);
        $this->assertEquals(1, (int) $blocked->auto_generated);
    }

    public function test_does_not_auto_block_when_setting_disabled(): void
    {
        $this->skipIfNotificationLoadIncompatible();
        Bus::fake();

        $this->createWafBlocklistTable();

        $this->setSettingRuntime([
            'anomaly_login_threshold' => '1',
            'anomaly_auto_block' => '0',
            'admin_alert_email' => 'audit@unn.test',
        ]);

        $ip = '203.0.113.41';

        $this->service->recordLoginAttempt($ip, false);
        $this->service->recordLoginAttempt($ip, false);

        $row = AnomalyEvent::query()->where('source_ip', $ip)->first();
        $this->assertNotNull($row);
        $this->assertFalse(
            (bool) $row->auto_blocked,
            'auto_blocked deve permanecer false quando anomaly_auto_block=0'
        );

        $this->assertSame(
            0,
            DB::table('waf_ip_blocklist')->count(),
            'Nenhum IP deve ser inserido em waf_ip_blocklist com auto_block desabilitado'
        );
    }

    // =========================================================
    // 8) Fallback - falha de notificacao nao propaga (Req 11.7)
    // =========================================================

    public function test_notification_failure_preserves_anomaly_record_and_does_not_throw(): void
    {
        $this->skipIfNotificationLoadIncompatible();
        // Substitui o Bus por dispatcher que lanca em qualquer dispatch:
        // o servico DEVE capturar a excecao, manter a anomaly persistida
        // e NAO propagar para o chamador (Requirement 11.7).
        $this->app->instance(BusDispatcher::class, new ThrowingBusDispatcher());

        $this->setSettingRuntime([
            'anomaly_login_threshold' => '1',
            'admin_alert_email' => 'audit@unn.test',
        ]);

        $ip = '203.0.113.50';

        try {
            $this->service->recordLoginAttempt($ip, false);
            $this->service->recordLoginAttempt($ip, false);
        } catch (\Throwable $e) {
            $this->fail(
                'recordLoginAttempt() nao deve propagar excecao quando o '
                    . 'dispatch da notificacao falha (Requirement 11.7): ' . $e->getMessage()
            );
        }

        // anomaly persistida, mas notified_at = null porque a notificacao falhou.
        $row = AnomalyEvent::query()->where('source_ip', $ip)->first();
        $this->assertNotNull($row, 'Anomaly deve ser preservada mesmo se a notificacao falhar');
        $this->assertNull(
            $row->notified_at,
            'notified_at deve permanecer null quando o dispatch falhou'
        );
    }
}

/**
 * Bus dispatcher de testes que sempre lanca excecao no dispatch.
 * Usado para forcar o caminho de fallback do AnomalyDetectorService
 * (notificacao falha mas anomaly e preservada).
 */
class ThrowingBusDispatcher implements BusDispatcher
{
    public function dispatch($command)
    {
        throw new \RuntimeException('forced dispatch failure (test)');
    }

    public function dispatchSync($command, $handler = null)
    {
        throw new \RuntimeException('forced dispatchSync failure (test)');
    }

    public function dispatchNow($command, $handler = null)
    {
        throw new \RuntimeException('forced dispatchNow failure (test)');
    }

    public function hasCommandHandler($command)
    {
        return false;
    }

    public function getCommandHandler($command)
    {
        return false;
    }

    public function pipeThrough(array $pipes)
    {
        return $this;
    }

    public function map(array $map)
    {
        return $this;
    }
}
