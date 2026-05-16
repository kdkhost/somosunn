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
 * Sistema UNN - Property test (Property 9) para filtros de audit log.
 *
 * Spec: .kiro/specs/advanced-security-performance (task 9.5)
 *
 * Property 9: Audit Log Filter Correctness
 *   Para qualquer combinacao de filtros aplicados simultaneamente em
 *   AuditLogService::query(array $filters):
 *     1) TODOS os registros retornados satisfazem TODOS os filtros
 *        aplicados (combinacao logica AND).
 *     2) Filtros vazios nao restringem - retornam o conjunto completo.
 *     3) Filtros NUNCA sao combinados via OR (interseccao, nao uniao).
 *
 *   Filtros suportados pela API real do servico (validados):
 *     - date_from, date_to (Carbon|string)
 *     - user_id (int)
 *     - action (string)
 *     - target_type (string FQCN)
 *     - target_id (int)
 *
 *   Geradores:
 *     - action: oneOf(null, elements([...acoes do AuditLogService]))
 *     - user_id: oneOf(null, elements([...ids presentes no fixture]))
 *     - target_type: oneOf(null, elements([...classes presentes no fixture]))
 *
 *   Notas de implementacao:
 *     - O fixture e populado em setUp() via DB::table()->insert() (sem
 *       FK de users, conforme migration de audit_logs), cobrindo todas
 *       as combinacoes de (action, user_id, target_type) usadas pelos
 *       generators. Isso garante que a interseccao testada e nao trivial.
 *     - RefreshDatabase isola o estado entre testes; o seed persiste
 *       entre iteracoes do Eris dentro do mesmo teste (a query e idempotente).
 *
 * Validates: Requirements 6.5
 */

namespace Tests\Property;

use App\Contracts\AuditLogInterface;
use App\Models\Order;
use App\Models\SellerProduct;
use App\Models\User;
use App\Services\AuditLogService;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLogFilterTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    /**
     * Acoes usadas no fixture e nos geradores. Subconjunto representativo
     * das constantes ACTION_* de AuditLogService.
     *
     * @var array<int, string>
     */
    private const ACTIONS = [
        AuditLogService::ACTION_LOGIN,
        AuditLogService::ACTION_LOGOUT,
        AuditLogService::ACTION_FILE_UPLOAD,
        AuditLogService::ACTION_PAYMENT,
        AuditLogService::ACTION_WEBHOOK,
    ];

    /**
     * IDs de usuario usados no fixture (sem FK real - audit_logs nao
     * possui constraint de chave estrangeira em user_id).
     *
     * @var array<int, int>
     */
    private const USER_IDS = [1, 2, 3, 4, 5];

    /**
     * Tipos de alvo usados no fixture. Usamos FQCN (formato real produzido
     * por get_class($target) em AuditLogService::buildPayload).
     *
     * @var array<int, class-string>
     */
    private const TARGET_TYPES = [
        User::class,
        Order::class,
        SellerProduct::class,
    ];

    private AuditLogInterface $service;

    /**
     * Compatibilidade com PHPUnit 10: o Eris 0.14 ainda invoca
     * \PHPUnit\Util\Test::parseTestMethodAnnotations(), que foi removida
     * na PHPUnit 10. Retornar [] faz a trait operar com defaults
     * (100 iteracoes, sem time-limit). Determinismo via ERIS_SEED quando
     * necessario para reproduzir contraexemplos.
     *
     * @return array<string,mixed>
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Resolver via container - garante que o binding registrado em
        // AppServiceProvider esta apontando para AuditLogService.
        $this->service = app(AuditLogInterface::class);

        $this->assertInstanceOf(
            AuditLogService::class,
            $this->service,
            'Container deve resolver AuditLogInterface para AuditLogService.'
        );

        $this->seedAuditLogs();
    }

    /**
     * Pre-popula audit_logs com fixture variado cobrindo toda a grade
     * (action x user_id x target_type) usada pelos geradores. Cada celula
     * recebe pelo menos 1 registro, garantindo interseccoes nao triviais
     * para os filtros sob teste.
     *
     * Total de registros: 5 actions * 6 user_ids (5 + null) * 4 target_types
     * (3 + null) = 120 linhas.
     */
    private function seedAuditLogs(): void
    {
        $rows = [];
        $base = Carbon::create(2026, 1, 1, 0, 0, 0);
        $i = 0;

        $userVariants = array_merge([null], self::USER_IDS);
        $targetVariants = array_merge([null], self::TARGET_TYPES);

        foreach (self::ACTIONS as $action) {
            foreach ($userVariants as $userId) {
                foreach ($targetVariants as $targetType) {
                    $rows[] = [
                        'user_id' => $userId,
                        'ip_address' => '127.0.0.' . (($i % 254) + 1),
                        'user_agent' => 'PBT-AuditLogFilter',
                        'action' => $action,
                        'target_type' => $targetType,
                        'target_id' => $targetType === null ? null : (($i % 10) + 1),
                        'old_values' => null,
                        'new_values' => null,
                        'request_id' => str_pad((string) $i, 36, '0', STR_PAD_LEFT),
                        'metadata' => null,
                        'created_at' => $base->copy()->addMinutes($i)->format('Y-m-d H:i:s'),
                    ];
                    $i++;
                }
            }
        }

        // insert em batch (1 query) - mais rapido que loop de Eloquent.
        DB::table('audit_logs')->insert($rows);
    }

    /**
     * Property 9.A (combinacao AND - corretude por elemento):
     *
     *   Para qualquer combinacao gerada de (action, user_id, target_type),
     *   todos os registros retornados por query() satisfazem TODOS os
     *   filtros aplicados (filtros nulos sao omitidos).
     *
     * Validates: Requirements 6.5
     */
    public function test_query_returns_only_records_matching_all_applied_filters(): void
    {
        $this
            ->forAll(
                Generators::oneOf(
                    Generators::constant(null),
                    Generators::elements(self::ACTIONS)
                ),
                Generators::oneOf(
                    Generators::constant(null),
                    Generators::elements(self::USER_IDS)
                ),
                Generators::oneOf(
                    Generators::constant(null),
                    Generators::elements(self::TARGET_TYPES)
                )
            )
            ->then(function (?string $action, ?int $userId, ?string $targetType): void {
                $filters = [];
                if ($action !== null) {
                    $filters['action'] = $action;
                }
                if ($userId !== null) {
                    $filters['user_id'] = $userId;
                }
                if ($targetType !== null) {
                    $filters['target_type'] = $targetType;
                }

                // perPage alto para que a verificacao por elemento examine
                // todos os registros de uma vez (sem precisar paginar).
                $paginator = $this->service->query($filters, 1000);

                foreach ($paginator->items() as $log) {
                    if (isset($filters['action'])) {
                        $this->assertSame(
                            $filters['action'],
                            $log->action,
                            "Property 9 violada: registro retornou action='{$log->action}' " .
                            "mas filtro exige action='{$filters['action']}' " .
                            '(filtros=' . json_encode($filters) . ')'
                        );
                    }

                    if (isset($filters['user_id'])) {
                        $this->assertSame(
                            (int) $filters['user_id'],
                            (int) $log->user_id,
                            "Property 9 violada: registro retornou user_id={$log->user_id} " .
                            "mas filtro exige user_id={$filters['user_id']} " .
                            '(filtros=' . json_encode($filters) . ')'
                        );
                    }

                    if (isset($filters['target_type'])) {
                        $this->assertSame(
                            $filters['target_type'],
                            $log->target_type,
                            "Property 9 violada: registro retornou target_type='{$log->target_type}' " .
                            "mas filtro exige target_type='{$filters['target_type']}' " .
                            '(filtros=' . json_encode($filters) . ')'
                        );
                    }
                }

                // Cardinalidade: total deve coincidir com a contagem
                // calculada via DB::table com os mesmos predicados (AND).
                $expected = $this->expectedAndCount($filters);
                $this->assertSame(
                    $expected,
                    (int) $paginator->total(),
                    'Property 9 violada: query() retornou total=' . $paginator->total() .
                    ", esperado (AND)={$expected} (filtros=" . json_encode($filters) . ')'
                );
            });
    }

    /**
     * Property 9.B (filtros vazios nao restringem):
     *
     *   query([]) retorna o conjunto completo de registros do fixture.
     *
     * Validates: Requirements 6.5
     */
    public function test_empty_filters_do_not_restrict_results(): void
    {
        $expectedTotal = (int) DB::table('audit_logs')->count();

        $paginator = $this->service->query([], 1000);

        $this->assertSame(
            $expectedTotal,
            (int) $paginator->total(),
            "Property 9 violada: query() com filtros vazios deveria retornar total={$expectedTotal}, " .
            "retornou={$paginator->total()}"
        );
    }

    /**
     * Property 9.C (AND, nao OR):
     *
     *   Aplicar dois filtros nao-nulos retorna a INTERSECCAO dos conjuntos
     *   correspondentes, nunca a UNIAO. Validamos comparando contagens
     *   esperadas via DB::table:
     *     - count(AND) <= count(side_a) e count(AND) <= count(side_b)
     *     - count(OR)  >= count(side_a) e count(OR)  >= count(side_b)
     *     - count_query == count(AND), e em caso geral count(AND) < count(OR).
     *
     * Validates: Requirements 6.5
     */
    public function test_filters_are_combined_with_and_not_or(): void
    {
        $action = AuditLogService::ACTION_LOGIN;
        $targetType = User::class;

        $countAction = (int) DB::table('audit_logs')
            ->where('action', $action)
            ->count();

        $countTarget = (int) DB::table('audit_logs')
            ->where('target_type', $targetType)
            ->count();

        $countAnd = (int) DB::table('audit_logs')
            ->where('action', $action)
            ->where('target_type', $targetType)
            ->count();

        $countOr = (int) DB::table('audit_logs')
            ->where(function ($q) use ($action, $targetType): void {
                $q->where('action', $action)
                    ->orWhere('target_type', $targetType);
            })
            ->count();

        // Pre-condicao do fixture: AND deve ser estritamente menor que OR
        // para que o teste consiga discriminar AND vs OR (caso contrario o
        // teste seria trivial / ambiguo).
        $this->assertGreaterThan(
            $countAnd,
            $countOr,
            'Pre-condicao do fixture violada: count(OR) deveria ser > count(AND) ' .
            "para discriminar AND/OR. count(AND)={$countAnd}, count(OR)={$countOr}."
        );

        $paginator = $this->service->query([
            'action' => $action,
            'target_type' => $targetType,
        ], 1000);

        $total = (int) $paginator->total();

        $this->assertSame(
            $countAnd,
            $total,
            "Property 9 violada: query com (action, target_type) deveria retornar AND={$countAnd}, " .
            "retornou={$total}"
        );

        // Sanity checks: AND <= cada lado individual; AND != OR (caso geral).
        $this->assertLessThanOrEqual($countAction, $total);
        $this->assertLessThanOrEqual($countTarget, $total);
        $this->assertNotSame(
            $countOr,
            $total,
            'Property 9 violada: filtros estao sendo combinados via OR (deveria ser AND).'
        );
    }

    /**
     * Calcula a cardinalidade esperada para um conjunto de filtros aplicados
     * via AND, lendo direto do fixture com os mesmos predicados.
     *
     * @param array<string, mixed> $filters
     */
    private function expectedAndCount(array $filters): int
    {
        $q = DB::table('audit_logs');

        if (isset($filters['action'])) {
            $q->where('action', (string) $filters['action']);
        }
        if (isset($filters['user_id'])) {
            $q->where('user_id', (int) $filters['user_id']);
        }
        if (isset($filters['target_type'])) {
            $q->where('target_type', (string) $filters['target_type']);
        }

        return (int) $q->count();
    }
}
