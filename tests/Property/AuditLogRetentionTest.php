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
 * Sistema UNN - Property test (Property 8) para purge de retenção de audit logs
 *
 * Spec: .kiro/specs/advanced-security-performance (task 9.4)
 *
 * Property 8: Audit Log Retention Purge
 *   Para qualquer N (dias de retencao) e qualquer conjunto de registros
 *   com idades variadas:
 *     - Apos AuditLogService::purgeOld($N), TODOS os registros com
 *       created_at < now()->subDays($N) foram deletados.
 *     - NENHUM registro com created_at >= now()->subDays($N) foi deletado.
 *     - O contador retornado por purgeOld() e exatamente o numero de
 *       registros antigos.
 *
 * Detalhes de implementacao:
 *   - Usa Carbon::setTestNow() para congelar o relogio durante cada
 *     iteracao (evita flakiness na borda exata do cutoff entre o
 *     momento da insercao e o momento do purge).
 *   - Limpa a tabela audit_logs no inicio de cada iteracao (Eris executa
 *     o closure varias vezes dentro do mesmo metodo de teste).
 *   - Ignora override via setting `audit_retention_days`: como usamos
 *     RefreshDatabase, a tabela settings inicia vazia e o parametro
 *     $retentionDays e que prevalece.
 *
 * Validates: Requirements 6.3
 */

namespace Tests\Property;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditLogRetentionTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    /**
     * Verifica conectividade com o banco antes de acionar RefreshDatabase
     * (que migra/limpa o schema). Se o banco estiver indisponivel,
     * marca o teste como skipped sem falhar a suite.
     */
    protected function setUp(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Banco de dados indisponivel: ' . $e->getMessage());
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        // Garante que setTestNow nao vaze entre testes/casos.
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * PHPUnit 10 removeu PHPUnit\Util\Test::parseTestMethodAnnotations()
     * no qual o Eris 0.14.x ainda se apoia. Sobrescrevemos para retornar
     * defaults vazios (rand, 100 iteracoes, sem time-limit).
     *
     * @return array<string, mixed>
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    /**
     * Property 8: purgeOld(N) deleta exatamente os registros antigos
     * (created_at < now - N dias) e preserva todos os recentes.
     *
     * Geradores:
     *   - $days     : 1..365 (intervalo de retencao realistico em dias).
     *   - $oldCount : 1..20  (quantidade de registros antigos a inserir;
     *                         limite mantido baixo porque cada iteracao
     *                         executa inserts reais no banco).
     *
     * Validates: Requirements 6.3
     */
    public function test_purge_deletes_only_old_records(): void
    {
        $service = $this->app->make(AuditLogService::class);
        $recentCount = 5;

        $this
            ->forAll(
                Generators::choose(1, 365),
                Generators::choose(1, 20)
            )
            ->then(function (int $days, int $oldCount) use ($service, $recentCount): void {
                // Congela o relogio para eliminar variancia entre o
                // created_at salvo e o cutoff calculado pelo service.
                $now = Carbon::create(2024, 6, 15, 12, 0, 0);
                Carbon::setTestNow($now);

                // Estado limpo a cada iteracao (Eris reusa o mesmo teste).
                AuditLog::query()->delete();

                // Insere $oldCount registros antigos: created_at < cutoff.
                // cutoff = now - $days; "antigo" = now - ($days + 1).
                $oldCreatedAt = $now->copy()->subDays($days + 1);
                for ($i = 0; $i < $oldCount; $i++) {
                    AuditLog::create([
                        'action' => 'old',
                        'ip_address' => '1.1.1.1',
                        'request_id' => (string) Str::uuid(),
                        'created_at' => $oldCreatedAt,
                    ]);
                }

                // Insere $recentCount registros recentes: created_at > cutoff.
                // Usamos cutoff + 1 hora (em vez de subDays($days - 1)) para
                // cobrir tambem o caso $days == 1 sem cair na borda exata.
                $recentCreatedAt = $now->copy()->subDays($days)->addHour();
                for ($i = 0; $i < $recentCount; $i++) {
                    AuditLog::create([
                        'action' => 'recent',
                        'ip_address' => '2.2.2.2',
                        'request_id' => (string) Str::uuid(),
                        'created_at' => $recentCreatedAt,
                    ]);
                }

                // Acao sob teste.
                $deleted = $service->purgeOld($days);

                // 1) Contador retornado == numero de registros antigos.
                $this->assertSame(
                    $oldCount,
                    $deleted,
                    "Property 8 violada: purgeOld({$days}) retornou {$deleted}, esperado {$oldCount}"
                );

                // 2) Todos os registros antigos foram deletados.
                $this->assertSame(
                    0,
                    AuditLog::where('action', 'old')->count(),
                    "Property 8 violada: registros antigos sobreviveram ao purgeOld({$days})"
                );

                // 3) Nenhum registro recente foi deletado.
                $this->assertSame(
                    $recentCount,
                    AuditLog::where('action', 'recent')->count(),
                    "Property 8 violada: registros recentes foram removidos por purgeOld({$days})"
                );

                // 4) Total final == apenas os recentes.
                $this->assertSame(
                    $recentCount,
                    AuditLog::count(),
                    "Property 8 violada: total de registros apos purge inconsistente"
                );
            });
    }
}
