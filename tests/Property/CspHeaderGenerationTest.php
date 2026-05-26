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
 * Sistema UNN - Property test (Property 12) para geracao do
 * header Content-Security-Policy.
 *
 * Spec: .kiro/specs/advanced-security-performance (task 12.2)
 *
 * Property 12: CSP Header Generation
 *
 *   Para QUALQUER combinacao (base directives + allowlist
 *   configuravel via settings.csp_extra_allowlist), o header
 *   `Content-Security-Policy` produzido por
 *   SecurityHeadersMiddleware DEVE:
 *
 *     1) conter TODAS as directive keys obrigatorias:
 *          default-src, script-src, style-src, img-src,
 *          font-src, connect-src, frame-src;
 *     2) incluir os CDNs base reais da plataforma:
 *          cdn.tailwindcss.com, cdn.jsdelivr.net,
 *          cdnjs.cloudflare.com, code.jquery.com,
 *          fonts.googleapis.com, fonts.gstatic.com;
 *     3) incluir TODAS as entradas da allowlist extra
 *          configurada via settings.csp_extra_allowlist;
 *     4) conter 'unsafe-inline' dentro da directive style-src.
 *
 * ESTRATEGIA DE TESTE:
 *   - O middleware so' aplica CSP em respostas HTML; o $next do
 *     teste retorna uma Response com Content-Type: text/html.
 *   - A allowlist extra e' injetada diretamente no cache estatico
 *     do Setting (via ReflectionClass), evitando hit em banco e
 *     mantendo a propriedade isolada da camada de persistencia.
 *     SecurityHeadersMiddleware::loadExtraAllowlist() aceita o
 *     valor como array e como string JSON.
 *   - O directive alvo da allowlist extra tambem e' gerado pelo
 *     Eris para cobrir todas as directives mutaveis da CSP.
 *
 * Validates: Requirements 8.1, 8.2, 8.6, 8.8
 */

namespace Tests\Property;

use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Models\Setting;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Http\Request;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CspHeaderGenerationTest extends TestCase
{
    use TestTrait;

    /**
     * Directive keys obrigatorias na CSP gerada (Property 12.1).
     *
     * @var array<int, string>
     */
    private const REQUIRED_DIRECTIVES = [
        'default-src',
        'script-src',
        'style-src',
        'img-src',
        'font-src',
        'connect-src',
        'frame-src',
    ];

    /**
     * CDNs base reais utilizados pela plataforma (Property 12.2).
     *
     * @var array<int, string>
     */
    private const REQUIRED_BASE_CDNS = [
        'cdn.tailwindcss.com',
        'cdn.jsdelivr.net',
        'cdnjs.cloudflare.com',
        'code.jquery.com',
        'fonts.googleapis.com',
        'fonts.gstatic.com',
        'gateway.sumup.com',
    ];

    /**
     * Universo de dominios que podem aparecer na allowlist extra.
     * Conjunto pequeno e finito para que `Generators::seq` produza
     * sequencias variadas (incluindo vazias e com repeticoes).
     *
     * @var array<int, string>
     */
    private const EXTRA_DOMAIN_POOL = [
        'https://example.com',
        'https://other.com',
        'https://stripe.com',
        'https://hooks.stripe.com',
        'https://js.stripe.com',
        'https://maps.googleapis.com',
    ];

    /**
     * Directives que a allowlist extra pode estender. Mantido em
     * sincronia com o whitelist de chaves aceitas em
     * SecurityHeadersMiddleware::buildDirectives().
     *
     * @var array<int, string>
     */
    private const ALLOWLISTABLE_DIRECTIVES = [
        'script-src',
        'style-src',
        'img-src',
        'font-src',
        'connect-src',
        'frame-src',
    ];

    /**
     * Compatibilidade com PHPUnit 10: Eris 0.14.x ainda invoca
     * \PHPUnit\Util\Test::parseTestMethodAnnotations() (removida na
     * PHPUnit 10). Retornar [] faz a trait operar com defaults.
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

        // Garante que cada iteracao parte de um estado limpo do
        // cache de Setting; outras propriedades da suite poderiam
        // ter deixado lixo aqui.
        Setting::flushRuntimeCache();
    }

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();

        parent::tearDown();
    }

    /**
     * Property 12: o header Content-Security-Policy gerado pelo
     * SecurityHeadersMiddleware contem todas as directives
     * obrigatorias, todos os CDNs base, toda a allowlist extra e
     * `'unsafe-inline'` em style-src.
     *
     * Validates: Requirements 8.1, 8.2, 8.6, 8.8
     */
    public function test_csp_contains_all_required_directives_and_allowlist(): void
    {
        /** @var SecurityHeadersMiddleware $middleware */
        $middleware = $this->app->make(SecurityHeadersMiddleware::class);

        $this
            ->forAll(
                Generators::seq(Generators::elements(self::EXTRA_DOMAIN_POOL)),
                Generators::elements(self::ALLOWLISTABLE_DIRECTIVES)
            )
            ->then(function (array $extraDomains, string $directiveKey) use ($middleware): void {
                // Normaliza extras (remove duplicatas mantendo ordem) -
                // a propriedade vale para o conjunto, nao para a sequencia.
                $extraDomains = array_values(array_unique($extraDomains));

                // Configura a allowlist extra como settings.csp_extra_allowlist.
                // O middleware aceita o valor como array PHP ou JSON string;
                // injetamos array diretamente para evitar I/O com o banco.
                $allowlistPayload = $extraDomains === []
                    ? []
                    : [$directiveKey => $extraDomains];

                $this->setSettingRuntime([
                    'csp_extra_allowlist' => $allowlistPayload,
                ]);

                // Executa o middleware com uma Request HTML real. A response
                // de origem precisa ter Content-Type: text/html para que o
                // shouldApplyCsp() retorne true e o header seja emitido.
                $request = Request::create('/pbt-csp', 'GET');
                $next = static function (): Response {
                    $response = new Response('<html></html>', 200);
                    $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

                    return $response;
                };

                $response = $middleware->handle($request, $next);

                $csp = (string) $response->headers->get('Content-Security-Policy', '');

                $this->assertNotSame(
                    '',
                    $csp,
                    'Property 12 violada: header Content-Security-Policy ausente em resposta HTML.'
                );

                // 1) Todas as directives obrigatorias presentes.
                foreach (self::REQUIRED_DIRECTIVES as $directive) {
                    $this->assertMatchesRegularExpression(
                        '/(?:^|;\s*)' . preg_quote($directive, '/') . '\s/',
                        $csp,
                        sprintf(
                            "Property 12.1 violada: directive '%s' ausente. CSP=%s",
                            $directive,
                            $csp
                        )
                    );
                }

                // 2) Todos os CDNs base presentes em alguma directive.
                foreach (self::REQUIRED_BASE_CDNS as $cdn) {
                    $this->assertStringContainsString(
                        $cdn,
                        $csp,
                        sprintf(
                            "Property 12.2 violada: CDN base '%s' ausente. CSP=%s",
                            $cdn,
                            $csp
                        )
                    );
                }

                // 3) Cada entrada da allowlist extra deve constar dentro
                //    da directive configurada (e nao em outra qualquer).
                if ($extraDomains !== []) {
                    $directiveSection = $this->extractDirective($csp, $directiveKey);

                    $this->assertNotNull(
                        $directiveSection,
                        sprintf(
                            "Property 12.3 violada: directive '%s' nao encontrada para validar allowlist. CSP=%s",
                            $directiveKey,
                            $csp
                        )
                    );

                    foreach ($extraDomains as $domain) {
                        $this->assertStringContainsString(
                            $domain,
                            $directiveSection,
                            sprintf(
                                "Property 12.3 violada: dominio '%s' da allowlist extra ausente em '%s'. Section=%s",
                                $domain,
                                $directiveKey,
                                $directiveSection
                            )
                        );
                    }
                }

                // 4) 'unsafe-inline' em style-src.
                $styleSrc = $this->extractDirective($csp, 'style-src');
                $this->assertNotNull(
                    $styleSrc,
                    "Property 12.4 violada: style-src ausente. CSP={$csp}"
                );
                $this->assertStringContainsString(
                    "'unsafe-inline'",
                    (string) $styleSrc,
                    "Property 12.4 violada: style-src nao contem 'unsafe-inline'. style-src={$styleSrc}"
                );
            });
    }

    /* -------------------------------------------------------------- */
    /* Helpers                                                         */
    /* -------------------------------------------------------------- */

    /**
     * Extrai o conteudo (lista de fontes) de uma directive especifica
     * dentro do header CSP serializado. Retorna null se a directive
     * nao estiver presente.
     */
    private function extractDirective(string $csp, string $directive): ?string
    {
        $pattern = '/(?:^|;\s*)' . preg_quote($directive, '/') . '\s+([^;]+)/';

        if (preg_match($pattern, $csp, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }

    /**
     * Injeta valores diretamente no cache estatico do Setting para
     * evitar hits em banco durante propriedades. As chaves
     * sobrevivem a Setting::get() pois loadRuntimeCache() detecta o
     * cache ja carregado.
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
