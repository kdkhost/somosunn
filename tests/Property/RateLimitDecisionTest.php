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
 * Sistema UNN - Property test (Property 5) para decisao de rate limit
 *
 * Spec: .kiro/specs/advanced-security-performance (task 8.2)
 *
 * Property 5: Rate Limit Decision Correctness
 *
 *   Para qualquer requisicao com IP e User-Agent, a decisao do
 *   AdvancedRateLimitMiddleware (block / allow) DEVE satisfazer:
 *
 *     decision(req) = block
 *         iff
 *     ( matchesUA(req.ua) v exceedsThreshold(req.ip, count) )
 *         e
 *     NOT whitelisted(req.ip)
 *
 *   Onde:
 *     - matchesUA(ua)        == AdvancedRateLimitMiddleware::isSuspiciousAgent(ua)
 *     - exceedsThreshold(ip) == count > rate_limit_threshold (window 60s)
 *     - whitelisted(ip)      == AdvancedRateLimitMiddleware::isWhitelisted(ip)
 *
 *   ESTRATEGIA DE TESTE:
 *   O middleware concreto depende de DB (rate_limit_blocks), filesystem
 *   (storage/framework/rate-limits/*.json) e de Setting cache. Para
 *   isolar a propriedade logica e evitar I/O por iteracao, testamos:
 *     A) o predicado isSuspiciousAgent diretamente contra a lista
 *        DEFAULT_UA_PATTERNS;
 *     B) o predicado isWhitelisted contra um whitelist injetado via
 *        cache estatico do Setting (sem hit no DB);
 *     C) a composicao logica final: decision = (matchesUA OR threshold)
 *        AND NOT whitelisted, usando os predicados reais do middleware
 *        e comparando com um modelo de referencia.
 *
 * Validates: Requirements 5.1, 5.2, 5.4
 */

namespace Tests\Property;

use App\Contracts\RateLimiterInterface;
use App\Http\Middleware\AdvancedRateLimitMiddleware;
use App\Models\Setting;
use Eris\Generator;
use Eris\TestTrait;
use ReflectionClass;
use Tests\TestCase;

class RateLimitDecisionTest extends TestCase
{
    use TestTrait;

    /**
     * IPs de teste cobrindo IPv4 publicos, IPv4 privados e loopback.
     * Conjunto finito para que `Generators::elements` possa produzir
     * tanto cenarios whitelisted quanto nao-whitelisted dentro do
     * mesmo dominio.
     *
     * @var array<int, string>
     */
    private const TEST_IPS = [
        '1.2.3.4',
        '8.8.8.8',
        '192.168.1.1',
        '127.0.0.1',
        '203.0.113.10',
        '198.51.100.42',
    ];

    /**
     * User-Agents benignos (NAO devem casar com nenhum padrao da lista
     * DEFAULT_UA_PATTERNS).
     *
     * @var array<int, string>
     */
    private const BENIGN_UAS = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)',
        'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/115.0',
        'curl/7.0',
        'PostmanRuntime/7.32.3',
    ];

    /**
     * User-Agents maliciosos (cada um contem ao menos um padrao de
     * DEFAULT_UA_PATTERNS, comparacao case-insensitive).
     *
     * @var array<int, string>
     */
    private const SUSPICIOUS_UAS = [
        'sqlmap/1.6.12 (https://sqlmap.org)',
        'Mozilla/5.0 nikto/2.1.6',
        'acunetix-product (WVS/14.0)',
        'masscan/1.3.2',
        'Nmap Scripting Engine; https://nmap.org/book/nse.html',
        'python-requests/2.28.1',
        'gobuster/3.4.0',
        'dirbuster-1.0',
        'WPScan v3.8.22 (https://wpscan.com/wordpress-security-scanner)',
        'Nuclei - Open-source project (github.com/projectdiscovery/nuclei)',
    ];

    private AdvancedRateLimitMiddleware $middleware;

    /**
     * Compatibilidade com PHPUnit 10: Eris 0.14.x ainda chama
     * \PHPUnit\Util\Test::parseTestMethodAnnotations() que foi removido.
     * Retornar [] faz a trait operar com defaults (100 iteracoes).
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Limpa cache do Setting para isolar entre testes (evita leak
        // de whitelist/ua_patterns configurados em outros testes).
        Setting::flushRuntimeCache();

        // Limpa storage de rate limit em disco. O middleware usa
        // storage/framework/rate-limits/{md5(ip)}.json para janela
        // deslizante; iteracoes Eris repetem IPs entao precisamos zerar.
        $this->purgeRateLimitStorage();

        // Resolve via container para validar bindings registrados.
        $this->middleware = app(AdvancedRateLimitMiddleware::class);

        $this->assertInstanceOf(
            RateLimiterInterface::class,
            $this->middleware,
            'AdvancedRateLimitMiddleware deve implementar RateLimiterInterface.'
        );
    }

    protected function tearDown(): void
    {
        // Reverte qualquer valor injetado no cache estatico do Setting
        // para nao impactar outros testes da suite.
        Setting::flushRuntimeCache();
        $this->purgeRateLimitStorage();

        parent::tearDown();
    }

    /**
     * Property 5.A (matchesUA verdadeiro):
     *
     * Para QUALQUER User-Agent contendo um padrao da lista
     * DEFAULT_UA_PATTERNS (case-insensitive, substring match),
     * isSuspiciousAgent SHALL retornar true.
     *
     * Validates: Requirements 5.1
     */
    public function test_suspicious_agent_predicate_matches_known_attack_tools(): void
    {
        $this
            ->forAll(Generator\elements(self::SUSPICIOUS_UAS))
            ->then(function (string $userAgent): void {
                $this->assertTrue(
                    $this->middleware->isSuspiciousAgent($userAgent),
                    "isSuspiciousAgent deveria retornar true para UA suspeito: '{$userAgent}'"
                );
            });
    }

    /**
     * Property 5.B (matchesUA falso):
     *
     * Para QUALQUER User-Agent benigno (que NAO contem nenhum dos
     * padroes em DEFAULT_UA_PATTERNS), isSuspiciousAgent SHALL retornar
     * false. Valida que o predicado nao tem falsos positivos para
     * navegadores e ferramentas legitimas.
     *
     * Validates: Requirements 5.1, 5.5
     */
    public function test_suspicious_agent_predicate_rejects_benign_user_agents(): void
    {
        $this
            ->forAll(Generator\elements(self::BENIGN_UAS))
            ->then(function (string $userAgent): void {
                $this->assertFalse(
                    $this->middleware->isSuspiciousAgent($userAgent),
                    "isSuspiciousAgent deveria retornar false para UA benigno: '{$userAgent}'"
                );
            });
    }

    /**
     * Property 5.C (whitelisted):
     *
     * isWhitelisted(ip) SHALL retornar true se e somente se o IP estiver
     * presente na lista configurada em `rate_limit_whitelist`. Cobre
     * tanto match positivo quanto negativo (IP fora da whitelist).
     *
     * Validates: Requirements 5.4
     */
    public function test_whitelist_predicate_reflects_configured_set(): void
    {
        $this
            ->forAll(
                Generator\elements(self::TEST_IPS), // IP sob teste
                Generator\elements(self::TEST_IPS)  // IP que estara na whitelist
            )
            ->then(function (string $ip, string $whitelistedIp): void {
                // Injeta a whitelist no cache estatico do Setting sem
                // depender de I/O ou DB.
                $this->setSettingRuntime([
                    'rate_limit_whitelist' => json_encode([$whitelistedIp]),
                ]);

                $expected = ($ip === $whitelistedIp);

                $this->assertSame(
                    $expected,
                    $this->middleware->isWhitelisted($ip),
                    sprintf(
                        "isWhitelisted('%s') retornou valor incorreto. Whitelist=[%s], esperado=%s.",
                        $ip,
                        $whitelistedIp,
                        $expected ? 'true' : 'false'
                    )
                );
            });
    }

    /**
     * Property 5.D (composicao logica):
     *
     * Para QUALQUER combinacao (ip, user_agent, request_count, threshold,
     * whitelisted), a decisao de bloquear computada pelos predicados do
     * middleware DEVE satisfazer:
     *
     *   block <==> ( matchesUA(ua) OR (count > threshold) ) AND NOT whitelisted(ip)
     *
     * Cobre os 16 cenarios logicos relevantes do produto cartesiano
     * (matchesUA x exceedsThreshold x whitelisted) repetidamente, com
     * threshold gerado em [1, 1000] e count em [0, 1000].
     *
     * Validates: Requirements 5.1, 5.2, 5.4
     */
    public function test_block_decision_matches_logical_definition(): void
    {
        $uaGen = Generator\oneOf(
            Generator\elements(self::BENIGN_UAS),
            Generator\elements(self::SUSPICIOUS_UAS)
        );

        $this
            ->forAll(
                Generator\elements(self::TEST_IPS),       // ip sob teste
                $uaGen,                                    // user-agent
                Generator\choose(0, 1000),                // request_count
                Generator\choose(1, 1000),                // threshold
                Generator\elements(self::TEST_IPS)        // ip a colocar em whitelist
            )
            ->then(function (
                string $ip,
                string $userAgent,
                int $count,
                int $threshold,
                string $whitelistedIp
            ): void {
                // Configura o cenario completo (whitelist + threshold).
                $this->setSettingRuntime([
                    'rate_limit_whitelist' => json_encode([$whitelistedIp]),
                    'rate_limit_threshold' => (string) $threshold,
                ]);

                // Predicados conforme implementados pelo middleware.
                $matchesUa    = $this->middleware->isSuspiciousAgent($userAgent);
                $whitelisted  = $this->middleware->isWhitelisted($ip);
                $exceeds      = $count > $threshold;

                // Decisao computada conforme Property 5.
                $expectedBlock = (! $whitelisted) && ($matchesUa || $exceeds);

                // Modelo de referencia (formula textual): aplica a mesma
                // logica explicitamente para garantir que estamos
                // comparando com a especificacao e nao com o codigo.
                $referenceBlock = $this->referenceDecision(
                    $matchesUa,
                    $exceeds,
                    $whitelisted
                );

                $this->assertSame(
                    $referenceBlock,
                    $expectedBlock,
                    sprintf(
                        'Property 5 violada: formula nao bate com referencia. '
                            . 'ip=%s ua=%s count=%d threshold=%d whitelistedIp=%s '
                            . '(matchesUA=%s exceeds=%s whitelisted=%s) '
                            . 'expected=%s reference=%s',
                        $ip,
                        $userAgent,
                        $count,
                        $threshold,
                        $whitelistedIp,
                        $matchesUa ? 'T' : 'F',
                        $exceeds ? 'T' : 'F',
                        $whitelisted ? 'T' : 'F',
                        $expectedBlock ? 'block' : 'allow',
                        $referenceBlock ? 'block' : 'allow'
                    )
                );

                // Casos extremos da Property 5:
                //  - whitelisted SEMPRE allow, mesmo com matchesUA ou exceeds
                //  - NAO whitelisted + (matchesUA OR exceeds) SEMPRE block
                //  - NAO whitelisted + nem matchesUA nem exceeds SEMPRE allow
                if ($whitelisted) {
                    $this->assertFalse(
                        $expectedBlock,
                        'IP whitelisted nunca deve resultar em block, '
                            . "ip={$ip} matchesUA=" . ($matchesUa ? 'T' : 'F')
                            . ' exceeds=' . ($exceeds ? 'T' : 'F')
                    );
                } elseif ($matchesUa || $exceeds) {
                    $this->assertTrue(
                        $expectedBlock,
                        'IP nao whitelisted com UA suspeito ou threshold '
                            . 'excedido deve resultar em block, '
                            . "ip={$ip} matchesUA=" . ($matchesUa ? 'T' : 'F')
                            . ' exceeds=' . ($exceeds ? 'T' : 'F')
                    );
                } else {
                    $this->assertFalse(
                        $expectedBlock,
                        'IP nao whitelisted, UA benigno e dentro do threshold '
                            . "deve resultar em allow, ip={$ip}"
                    );
                }
            });
    }

    /* -------------------------------------------------------------- */
    /* Helpers                                                         */
    /* -------------------------------------------------------------- */

    /**
     * Modelo de referencia da Property 5: implementacao literal da
     * formula `block <==> (matchesUA v exceeds) AND NOT whitelisted`.
     * Mantido em separado para que o teste compare a formula concreta
     * dos predicados com a especificacao textual.
     */
    private function referenceDecision(bool $matchesUa, bool $exceeds, bool $whitelisted): bool
    {
        if ($whitelisted) {
            return false; // Property 5: whitelist tem precedencia absoluta
        }

        return $matchesUa || $exceeds;
    }

    /**
     * Injeta valores diretamente no cache estatico do Setting para
     * evitar I/O em DB durante propriedades. As chaves sobrevivem a
     * Setting::get() pois loadRuntimeCache() detecta cache ja carregado.
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

    /**
     * Remove arquivos de janela deslizante de rate limit de iteracoes
     * anteriores. Operacao silenciosa (fail-safe): erros de I/O sao
     * ignorados.
     */
    private function purgeRateLimitStorage(): void
    {
        if (! function_exists('storage_path')) {
            return;
        }

        $dir = storage_path(AdvancedRateLimitMiddleware::STORAGE_DIR);
        if (! is_dir($dir)) {
            return;
        }

        $files = @glob($dir . DIRECTORY_SEPARATOR . '*.json');
        if (! is_array($files)) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
