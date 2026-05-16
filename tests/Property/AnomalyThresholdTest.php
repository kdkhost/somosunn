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
 * Sistema UNN - Property test (Property 16) para detecao de threshold de anomalias
 *
 * Spec: .kiro/specs/advanced-security-performance (task 15.3)
 *
 * Property 16: Anomaly Threshold Detection
 *
 *   Para qualquer N (count de eventos), T (threshold configurado) e
 *   W (janela em minutos), o AnomalyDetectorService SHALL satisfazer:
 *
 *     1) Equivalencia logica:
 *          anomalia flagged ⟺ count > threshold
 *        Isto e, AnomalyEvent::count() para a fonte == max(0, N - T).
 *        Cada chamada a recordLoginAttempt() onde a contagem corrente
 *        ultrapassa o threshold gera UMA linha em anomaly_events.
 *
 *     2) Threshold e configurado (nao hardcoded):
 *        Quando 'anomaly_login_threshold' e injetado via Setting (cache
 *        runtime), o servico DEVE usar esse valor em vez do default
 *        DEFAULT_LOGIN_THRESHOLD (=10). O gerador cobre valores
 *        diferentes do default propositadamente.
 *
 *     3) Janela W e respeitada:
 *        Eventos cujo timestamp armazenado em cache esta fora da janela
 *        (mais antigos que WINDOW_LOGIN_MINUTES = 5min) NAO contam para
 *        a contagem corrente — sao podados antes da comparacao com o
 *        threshold.
 *
 * ESTRATEGIA:
 *   - RefreshDatabase para isolar a tabela anomaly_events.
 *   - Bus::fake() para que o job de notificacao por email
 *     (SendGenericTemplateEmail) nao seja efetivamente despachado
 *     contra mailers reais durante as iteracoes.
 *   - Setting cache estatico injetado via reflection — evita I/O em DB
 *     a cada iteracao para resolver o threshold configurado.
 *   - Cache::flush() + delete em anomaly_events no inicio de cada
 *     iteracao para garantir contagem do zero.
 *
 * Validates: Requirements 11.1, 11.2, 11.3, 11.5
 */

namespace Tests\Property;

use App\Models\AnomalyEvent;
use App\Models\Setting;
use App\Services\AnomalyDetectorService;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Tests\TestCase;

class AnomalyThresholdTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    /** Janela hardcoded para failed_logins, em minutos (referencia do servico). */
    private const WINDOW_LOGIN_MINUTES = 5;

    /** IP fixo dentro do range RFC 5737 TEST-NET-3 (uso documentacao/teste). */
    private const TEST_IP = '203.0.113.42';

    private AnomalyDetectorService $service;

    /**
     * Compatibilidade com PHPUnit 10: Eris 0.14.x ainda chama
     * \PHPUnit\Util\Test::parseTestMethodAnnotations() que foi removido.
     * Retornar [] faz a trait operar com defaults (100 iteracoes).
     *
     * @return array<string, mixed>
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    /**
     * Verifica conectividade com o banco antes de acionar RefreshDatabase
     * (Property 16 depende de inserts reais em anomaly_events). Se o
     * banco estiver indisponivel, marca o teste como skipped sem falhar
     * a suite.
     */
    protected function setUp(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Banco de dados indisponivel: ' . $e->getMessage());
        }

        parent::setUp();

        // Evita que SendGenericTemplateEmail::dispatch() execute mailers
        // reais durante as iteracoes.
        Bus::fake();

        // Estado limpo do cache (timestamps de janela deslizante) e da
        // tabela settings (runtime cache estatico do model).
        Cache::flush();
        Setting::flushRuntimeCache();

        $this->service = $this->app->make(AnomalyDetectorService::class);
    }

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();

        parent::tearDown();
    }

    /**
     * Property 16 (parte 1+2): Equivalencia threshold-flagged usando
     * threshold configurado.
     *
     * Para qualquer N (count) e T (threshold) gerados, apos N chamadas
     * a recordLoginAttempt($ip, false), a quantidade de linhas em
     * anomaly_events para o IP DEVE ser exatamente max(0, N - T).
     * Equivalentemente: ha pelo menos uma anomalia ⟺ N > T.
     *
     * Validates: Requirements 11.1, 11.5
     */
    public function test_anomaly_flagged_when_count_exceeds_configured_threshold(): void
    {
        $this
            ->forAll(
                Generators::choose(1, 20), // N: count de tentativas falhas
                Generators::choose(1, 20)  // T: threshold configurado via Setting
            )
            ->then(function (int $count, int $threshold): void {
                // Estado limpo a cada iteracao (Eris reusa o mesmo metodo).
                Cache::flush();
                AnomalyEvent::query()->delete();

                // Injeta o threshold via cache do Setting (nao hardcoded).
                // Tambem fixa um destinatario de notificacao para evitar
                // queries auxiliares ao tentar resolver o Superadmin.
                $this->setSettingRuntime([
                    'anomaly_login_threshold' => (string) $threshold,
                    'admin_alert_email' => 'audit@unn.test',
                ]);

                $ip = self::TEST_IP;

                // N tentativas falhas em sequencia. Cada chamada onde a
                // contagem corrente ultrapassa o threshold gera UMA
                // linha em anomaly_events.
                for ($i = 0; $i < $count; $i++) {
                    $this->service->recordLoginAttempt($ip, false);
                }

                $anomalyCount = AnomalyEvent::query()
                    ->where('source_ip', $ip)
                    ->where('type', AnomalyDetectorService::TYPE_FAILED_LOGINS)
                    ->count();

                $expected = max(0, $count - $threshold);

                $this->assertSame(
                    $expected,
                    $anomalyCount,
                    sprintf(
                        'Property 16 violada (contagem): count=%d threshold=%d esperado=%d obtido=%d',
                        $count,
                        $threshold,
                        $expected,
                        $anomalyCount
                    )
                );

                // Equivalencia logica explicita: flagged > 0 ⟺ count > threshold.
                $this->assertSame(
                    $count > $threshold,
                    $anomalyCount > 0,
                    sprintf(
                        'Property 16 violada (equivalencia): count=%d threshold=%d flagged=%s esperado=%s',
                        $count,
                        $threshold,
                        $anomalyCount > 0 ? 'true' : 'false',
                        $count > $threshold ? 'true' : 'false'
                    )
                );
            });
    }

    /**
     * Property 16 (parte 3): Janela W e respeitada.
     *
     * Eventos com timestamp anterior a (now - WINDOW_LOGIN_MINUTES * 60)
     * sao podados pelo pushTimestamp() do servico antes da comparacao
     * com o threshold. Para validar isso, injetamos no cache uma lista
     * com K timestamps "antigos" (10 minutos atras) e em seguida
     * recordamos R tentativas frescas. A contagem efetiva considerada
     * pelo servico DEVE ser apenas R, e o numero de anomalias geradas
     * DEVE ser max(0, R - T) — independente de K.
     *
     * Validates: Requirements 11.1, 11.2, 11.3
     */
    public function test_window_excludes_events_older_than_window_minutes(): void
    {
        $this
            ->forAll(
                Generators::choose(1, 8),  // R: tentativas frescas (dentro da janela)
                Generators::choose(1, 50)  // K: timestamps antigos injetados no cache
            )
            ->then(function (int $recent, int $oldInjected): void {
                Cache::flush();
                AnomalyEvent::query()->delete();

                // Threshold pequeno e fixo para garantir que algumas
                // iteracoes flagueiem (recent > 3) e outras nao.
                $threshold = 3;

                $this->setSettingRuntime([
                    'anomaly_login_threshold' => (string) $threshold,
                    'admin_alert_email' => 'audit@unn.test',
                ]);

                $ip = self::TEST_IP;
                $cacheKey = 'unn:anomaly:login_failures:' . $ip;

                // Injeta K timestamps fora da janela (10min atras; janela = 5min).
                $oldTs = time() - (self::WINDOW_LOGIN_MINUTES * 60 * 2);
                Cache::put(
                    $cacheKey,
                    array_fill(0, $oldInjected, $oldTs),
                    self::WINDOW_LOGIN_MINUTES * 60 * 2
                );

                // Recorda R tentativas frescas — os timestamps antigos
                // devem ser podados antes da comparacao com o threshold.
                for ($i = 0; $i < $recent; $i++) {
                    $this->service->recordLoginAttempt($ip, false);
                }

                $expected = max(0, $recent - $threshold);
                $actual = AnomalyEvent::query()
                    ->where('source_ip', $ip)
                    ->where('type', AnomalyDetectorService::TYPE_FAILED_LOGINS)
                    ->count();

                $this->assertSame(
                    $expected,
                    $actual,
                    sprintf(
                        'Property 16 violada (janela): old=%d recent=%d threshold=%d esperado=%d obtido=%d '
                            . '— eventos fora da janela contaminaram a contagem',
                        $oldInjected,
                        $recent,
                        $threshold,
                        $expected,
                        $actual
                    )
                );
            });
    }

    /* -------------------------------------------------------------- */
    /* Helpers                                                         */
    /* -------------------------------------------------------------- */

    /**
     * Injeta valores diretamente no cache estatico do Setting para
     * evitar dependencia de I/O em DB durante as iteracoes Eris.
     * Setting::loadRuntimeCache() detecta runtimeCacheLoaded=true e
     * nao tenta sobrescrever; chaves nao injetadas caem no fallback
     * de Setting::get() (default).
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
