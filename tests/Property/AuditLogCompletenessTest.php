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
 * Sistema UNN - Property test (Property 7) para completude de
 * registros de audit log.
 *
 * Spec: .kiro/specs/advanced-security-performance (task 9.3)
 *
 * Property 7: Audit Log Entry Completeness
 *   Para qualquer evento auditavel despachado, o registro persistido
 *   em audit_logs DEVE conter os campos obrigatorios:
 *     - created_at      : timestamp nao nulo
 *     - action          : string nao vazia, igual ao action passado
 *     - ip_address      : string nao nula e nao vazia
 *     - request_id      : UUID v4 valido no formato
 *                         XXXXXXXX-XXXX-4XXX-[89ab]XXX-XXXXXXXXXXXX
 *
 *   Adicionalmente, quando ha contexto de autenticacao:
 *     - user_id == auth()->id() no momento do log
 *   Quando nao ha autenticacao:
 *     - user_id IS NULL
 *
 * Detalhes de implementacao:
 *   - QUEUE_CONNECTION=sync (phpunit.xml) garante que WriteAuditLogJob
 *     executa inline e o DB::table('audit_logs')->insert() acontece
 *     dentro da mesma transacao do teste (compativel com RefreshDatabase).
 *   - A request e injetada via container (app->instance('request', ...))
 *     com REMOTE_ADDR controlado, para que AuditLogService::resolveIpAddress()
 *     retorne o IP gerado pelo Eris.
 *   - NAO e setado o header X-Request-Id, forcando o service a gerar
 *     UUID via Str::uuid() (que e v4 no Laravel) - isso e o que o
 *     property test valida.
 *   - O user_id e simulado via actingAs() com uma instancia de User
 *     nao persistida (audit_logs nao tem FK para users, conforme
 *     migration), suficiente porque o service apenas chama auth()->id()
 *     que retorna o getAuthIdentifier() do user setado no guard.
 *
 * Validates: Requirements 6.1, 6.2, 6.6
 */

namespace Tests\Property;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLogCompletenessTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    /**
     * Regex para UUID v4 (case-insensitive):
     *   8-4-4-4-12 hex digits, com '4' fixo no inicio do 3o grupo
     *   e variant bits 10xx (8, 9, a ou b) no inicio do 4o grupo.
     */
    private const UUID_V4_PATTERN =
        '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /**
     * Verifica conectividade com o banco antes de acionar RefreshDatabase
     * (que migra/limpa o schema). Se o banco estiver indisponivel, marca
     * o teste como skipped sem falhar a suite.
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

    /**
     * Compatibilidade com PHPUnit 10: o Eris 0.14 ainda invoca
     * \PHPUnit\Util\Test::parseTestMethodAnnotations(), removida na PHPUnit 10.
     * Retornar [] faz a trait operar com defaults (100 iteracoes, sem time-limit).
     *
     * @return array<string, mixed>
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    /**
     * Property 7: completude de registro de audit log.
     *
     *   forAll (action, ip, user_id_opt). then:
     *     - log persistido tem created_at, action, ip_address, request_id
     *     - request_id casa com UUID v4
     *     - user_id == auth()->id() (ou null quando sem auth)
     *
     * Validates: Requirements 6.1, 6.2, 6.6
     */
    public function test_log_entry_always_complete(): void
    {
        $service = $this->app->make(AuditLogService::class);

        $this
            ->forAll(
                Generators::elements([
                    AuditLogService::ACTION_LOGIN,
                    AuditLogService::ACTION_LOGOUT,
                    AuditLogService::ACTION_CONFIG_CHANGE,
                    AuditLogService::ACTION_FILE_UPLOAD,
                    AuditLogService::ACTION_PAYMENT,
                ]),
                Generators::elements(['1.2.3.4', '192.168.0.1', '8.8.8.8', '::1']),
                Generators::oneOf(
                    Generators::constant(null),
                    Generators::choose(1, 1000)
                )
            )
            ->then(function (string $action, string $ip, ?int $userId) use ($service): void {
                // Estado limpo a cada iteracao (Eris executa o closure varias
                // vezes dentro do mesmo metodo de teste).
                DB::table('audit_logs')->delete();

                // Injeta uma request controlada para que
                // AuditLogService::resolveIpAddress() retorne o IP gerado e
                // resolveRequestId() caia no fallback de UUID (sem header).
                $request = Request::create('/pbt-audit', 'GET', [], [], [], [
                    'REMOTE_ADDR' => $ip,
                    'HTTP_USER_AGENT' => 'PBT-AuditLogCompleteness',
                ]);
                $this->app->instance('request', $request);

                // Configura contexto de autenticacao. User nao precisa estar
                // persistido: audit_logs nao tem FK para users e o service
                // apenas le auth()->id() via getAuthIdentifier().
                Auth::logout();
                if ($userId !== null) {
                    $user = new User();
                    $user->id = $userId;
                    Auth::setUser($user);
                }

                // Acao sob teste: dispara o log. Com QUEUE_CONNECTION=sync
                // o WriteAuditLogJob executa inline e o registro e persistido
                // antes de retornar.
                $service->log($action);

                $log = AuditLog::query()->orderBy('id', 'desc')->first();

                $this->assertNotNull(
                    $log,
                    "Property 7 violada: nenhum registro persistido apos log({$action})"
                );

                // 1) created_at nao nulo.
                $this->assertNotNull(
                    $log->created_at,
                    'Property 7 violada: created_at e null'
                );

                // 2) action correto e nao vazio.
                $this->assertIsString($log->action);
                $this->assertNotSame(
                    '',
                    $log->action,
                    'Property 7 violada: action vazia'
                );
                $this->assertSame(
                    $action,
                    $log->action,
                    "Property 7 violada: action persistida='{$log->action}', esperado='{$action}'"
                );

                // 3) ip_address nao nulo e nao vazio.
                $this->assertNotNull(
                    $log->ip_address,
                    'Property 7 violada: ip_address e null'
                );
                $this->assertIsString($log->ip_address);
                $this->assertNotSame(
                    '',
                    $log->ip_address,
                    'Property 7 violada: ip_address vazio'
                );

                // 4) request_id no formato UUID v4.
                $this->assertNotNull(
                    $log->request_id,
                    'Property 7 violada: request_id e null'
                );
                $this->assertMatchesRegularExpression(
                    self::UUID_V4_PATTERN,
                    (string) $log->request_id,
                    "Property 7 violada: request_id='{$log->request_id}' nao casa com UUID v4"
                );

                // 5) user_id consistente com auth context.
                if ($userId === null) {
                    $this->assertNull(
                        $log->user_id,
                        'Property 7 violada: user_id deveria ser NULL sem auth, ' .
                        "got={$log->user_id}"
                    );
                } else {
                    $this->assertSame(
                        $userId,
                        (int) $log->user_id,
                        "Property 7 violada: user_id persistido={$log->user_id}, " .
                        "esperado={$userId}"
                    );
                }
            });
    }
}
