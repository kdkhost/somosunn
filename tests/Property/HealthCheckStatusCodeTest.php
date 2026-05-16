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
 * Sistema UNN - Property test (Property 14) para status code do
 * health check.
 *
 * Spec: .kiro/specs/advanced-security-performance (task 13.2)
 *
 * Property 14: Health Check Status Code Correctness
 *
 *   Para qualquer combinacao de:
 *     - token (valido, invalido, vazio)
 *     - estado de cada componente (healthy / unhealthy), zero ou mais
 *
 *   A resposta HTTP do endpoint /health (HealthController::index) DEVE
 *   satisfazer:
 *     - token invalido           -> 401
 *     - token valido + tudo OK   -> 200
 *     - token valido + algum erro-> 503
 *
 *   ESTRATEGIA DE TESTE:
 *   O HealthController real depende de DB, S3, filesystem e fila para
 *   computar o status real dos componentes. Para isolar a Property 14
 *   (que e puramente sobre o mapeamento status agregado -> codigo HTTP)
 *   instanciamos o controller diretamente atraves de uma subclasse
 *   anonima que sobrescreve check() devolvendo um HealthResult fabricado
 *   a partir do vetor de estados gerado pelo Eris. Assim:
 *     - nao tocamos em DB nem em rede;
 *     - exercitamos o codigo real de validacao de token e de mapeamento
 *       status -> HTTP em index();
 *     - cobrimos o caso degenerado (zero componentes, que recomputa para
 *       healthy) e qualquer numero de componentes unhealthy.
 *
 *   AUTENTICACAO:
 *   HealthController le o token via env('HEALTH_TOKEN', ''). Setamos a
 *   variavel em putenv + $_ENV + $_SERVER (as tres fontes consultadas
 *   por Laravel\Env). config(['security.health_token' => ...]) nao seria
 *   suficiente porque o controller real nao consulta o config.
 *
 * Validates: Requirements 9.2, 9.5, 9.6
 */

namespace Tests\Property;

use App\Http\Controllers\HealthController;
use App\Support\ComponentStatus;
use App\Support\HealthResult;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Http\Request;
use Tests\TestCase;

class HealthCheckStatusCodeTest extends TestCase
{
    use TestTrait;

    /** Token "valido" injetado em HEALTH_TOKEN durante o teste. */
    private const VALID_TOKEN = 'secret-test-token';

    /** Estados possiveis por componente. */
    private const STATE_HEALTHY = 'healthy';
    private const STATE_UNHEALTHY = 'unhealthy';

    /**
     * Backup de HEALTH_TOKEN antes do teste, para restaurar em tearDown
     * e nao vazar estado para outros testes da suite.
     */
    private ?string $originalEnvToken = null;
    private bool $hadEnvToken = false;
    private bool $hadServerToken = false;

    /**
     * Compatibilidade com PHPUnit 10: Eris 0.14.x ainda invoca
     * \PHPUnit\Util\Test::parseTestMethodAnnotations(), removida no PHPUnit 10.
     * Retornar [] faz a trait operar com defaults (100 iteracoes, sem time-limit).
     *
     * @return array<string, mixed>
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Snapshot do estado anterior das tres fontes consultadas pelo
        // helper env() do Laravel (putenv + $_ENV + $_SERVER).
        $previousPutenv = getenv('HEALTH_TOKEN');
        $this->originalEnvToken = $previousPutenv === false ? null : (string) $previousPutenv;
        $this->hadEnvToken = array_key_exists('HEALTH_TOKEN', $_ENV);
        $this->hadServerToken = array_key_exists('HEALTH_TOKEN', $_SERVER);

        // Injeta o token "valido" do teste em todas as fontes para que o
        // controller real (que usa env('HEALTH_TOKEN', '')) o enxergue.
        putenv('HEALTH_TOKEN=' . self::VALID_TOKEN);
        $_ENV['HEALTH_TOKEN'] = self::VALID_TOKEN;
        $_SERVER['HEALTH_TOKEN'] = self::VALID_TOKEN;
    }

    protected function tearDown(): void
    {
        // Restaura HEALTH_TOKEN ao estado original para isolamento entre
        // testes. Importante porque outros testes podem assumir que a
        // variavel nao esta setada.
        if ($this->originalEnvToken === null) {
            putenv('HEALTH_TOKEN'); // remove
        } else {
            putenv('HEALTH_TOKEN=' . $this->originalEnvToken);
        }

        if ($this->hadEnvToken) {
            $_ENV['HEALTH_TOKEN'] = $this->originalEnvToken;
        } else {
            unset($_ENV['HEALTH_TOKEN']);
        }

        if ($this->hadServerToken) {
            $_SERVER['HEALTH_TOKEN'] = $this->originalEnvToken;
        } else {
            unset($_SERVER['HEALTH_TOKEN']);
        }

        parent::tearDown();
    }

    /**
     * Property 14: o status HTTP retornado por HealthController::index e
     * funcao deterministica de (token, estado dos componentes):
     *
     *   - token != HEALTH_TOKEN                     -> 401
     *   - token == HEALTH_TOKEN, todos healthy       -> 200
     *   - token == HEALTH_TOKEN, algum unhealthy     -> 503
     *
     * Validates: Requirements 9.2, 9.5, 9.6
     */
    public function test_status_code_matches_health_state(): void
    {
        $this
            ->forAll(
                // Token enviado: valido, invalido conhecido, ou vazio.
                Generators::elements([self::VALID_TOKEN, 'wrong-token', '']),
                // Estados de N componentes (N pode ser 0, simulando o
                // caso degenerado em que nenhum componente foi verificado).
                Generators::seq(
                    Generators::elements([self::STATE_HEALTHY, self::STATE_UNHEALTHY])
                )
            )
            ->then(function (string $token, array $componentStates): void {
                // Constroi um HealthController de teste cujo check() retorna
                // um HealthResult derivado dos estados gerados pelo Eris.
                // Mantem o restante da implementacao real (incluindo a
                // verificacao de bearer token e o mapeamento status -> HTTP).
                $controller = $this->makeFakeController($componentStates);

                // Monta a request com Authorization: Bearer {token}. Para o
                // caso de token vazio enviamos "Bearer " (sem conteudo) que
                // o helper bearerToken() devolve como string vazia, caindo
                // no fallback de query string ?token= (tambem vazio).
                $request = Request::create('/health', 'GET');
                $request->headers->set('Authorization', 'Bearer ' . $token);

                $response = $controller->index($request);
                $actualStatus = $response->getStatusCode();

                $expectedStatus = $this->expectedStatus($token, $componentStates);

                $this->assertSame(
                    $expectedStatus,
                    $actualStatus,
                    sprintf(
                        'Property 14 violada: token=%s components=[%s] esperado=%d obtido=%d',
                        var_export($token, true),
                        implode(',', $componentStates),
                        $expectedStatus,
                        $actualStatus
                    )
                );

                // Sub-property reforco: invariantes de cada caso.
                if ($token !== self::VALID_TOKEN) {
                    $this->assertSame(
                        401,
                        $actualStatus,
                        'Property 14: token invalido SEMPRE deve produzir 401, '
                            . 'independentemente do estado dos componentes.'
                    );
                } else {
                    $hasUnhealthy = in_array(
                        self::STATE_UNHEALTHY,
                        $componentStates,
                        true
                    );

                    if ($hasUnhealthy) {
                        $this->assertSame(
                            503,
                            $actualStatus,
                            'Property 14: token valido + ALGUM componente '
                                . 'unhealthy SEMPRE deve produzir 503.'
                        );
                    } else {
                        $this->assertSame(
                            200,
                            $actualStatus,
                            'Property 14: token valido + TODOS componentes '
                                . 'healthy (incluindo lista vazia) deve produzir 200.'
                        );
                    }
                }
            });
    }

    /* -------------------------------------------------------------- */
    /* Sanity checks (cenarios fixos exemplificando os tres casos)    */
    /* -------------------------------------------------------------- */

    public function test_invalid_token_returns_401_even_when_all_healthy(): void
    {
        $controller = $this->makeFakeController([self::STATE_HEALTHY, self::STATE_HEALTHY]);

        $request = Request::create('/health', 'GET');
        $request->headers->set('Authorization', 'Bearer wrong-token');

        $response = $controller->index($request);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_valid_token_with_all_healthy_returns_200(): void
    {
        $controller = $this->makeFakeController([
            self::STATE_HEALTHY,
            self::STATE_HEALTHY,
            self::STATE_HEALTHY,
        ]);

        $request = Request::create('/health', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . self::VALID_TOKEN);

        $response = $controller->index($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_valid_token_with_any_unhealthy_returns_503(): void
    {
        $controller = $this->makeFakeController([
            self::STATE_HEALTHY,
            self::STATE_UNHEALTHY,
            self::STATE_HEALTHY,
        ]);

        $request = Request::create('/health', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . self::VALID_TOKEN);

        $response = $controller->index($request);

        $this->assertSame(503, $response->getStatusCode());
    }

    /* -------------------------------------------------------------- */
    /* Helpers                                                         */
    /* -------------------------------------------------------------- */

    /**
     * Modelo de referencia: dado o par (token, componentStates), devolve
     * o HTTP status que a Property 14 exige.
     *
     * @param array<int, string> $componentStates
     */
    private function expectedStatus(string $token, array $componentStates): int
    {
        if ($token !== self::VALID_TOKEN) {
            return 401;
        }

        foreach ($componentStates as $state) {
            if ($state === self::STATE_UNHEALTHY) {
                return 503;
            }
        }

        return 200;
    }

    /**
     * Instancia uma subclasse anonima de HealthController que substitui
     * apenas o metodo check() para retornar um HealthResult fabricado a
     * partir dos estados gerados. Nenhuma outra parte do controller e
     * alterada: a validacao de bearer token e o mapeamento status->HTTP
     * sao exatamente os do codigo de producao.
     *
     * @param array<int, string> $componentStates
     */
    private function makeFakeController(array $componentStates): HealthController
    {
        return new class($componentStates) extends HealthController
        {
            /** @var array<int, string> */
            private array $forcedStates;

            /**
             * @param array<int, string> $forcedStates
             */
            public function __construct(array $forcedStates)
            {
                $this->forcedStates = $forcedStates;
            }

            public function check(): HealthResult
            {
                $result = new HealthResult();

                foreach ($this->forcedStates as $index => $state) {
                    $componentStatus = $state === 'healthy'
                        ? ComponentStatus::STATUS_OK
                        : ComponentStatus::STATUS_ERROR;

                    $result->components[] = new ComponentStatus(
                        'component_' . $index,
                        $componentStatus,
                        null,
                        0.0
                    );
                }

                $result->recomputeStatus();

                return $result;
            }
        };
    }
}
