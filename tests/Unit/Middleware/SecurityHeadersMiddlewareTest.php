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
 * Sistema UNN - Unit tests para SecurityHeadersMiddleware
 *
 * Spec: .kiro/specs/advanced-security-performance (task 12.4)
 *
 * Cobertura:
 *   1. CSP completa com TODAS as directives obrigatorias.
 *   2. Permissions-Policy:
 *        - camera=()       em rotas normais (ex.: /admin/dashboard)
 *        - camera=(self)   em rotas de QR scanner
 *      (microphone, geolocation, payment sempre bloqueados)
 *   3. Cross-Origin-Opener-Policy:  same-origin-allow-popups
 *   4. Cross-Origin-Resource-Policy: cross-origin
 *   5. Strict-Transport-Security so' aparece em HTTPS.
 *   6. X-Frame-Options presente em rotas comuns (SAMEORIGIN, valor
 *      definido pelo middleware) e ausente em rotas /embed/.
 *   7. Referrer-Policy: strict-origin-when-cross-origin.
 *   8. report-uri desativado quando nao ha endpoint implementado.
 *   9. Fallback CSP minima segura quando o pipeline de geracao
 *      falha (testado diretamente via Reflection no metodo
 *      privado minimalSafeCsp()).
 *  10. CSP NAO aplicada em respostas nao-HTML (ex.: JSON) — protege
 *      clientes/integracoes contra quebra.
 *
 * ESTRATEGIA DE TESTE:
 *   - Sem banco de dados: o cache estatico do Setting e' alimentado
 *     via Reflection, evitando hit em DB e isolando o teste das
 *     migrations / schema.
 *   - $next sempre retorna uma Response com Content-Type explicito,
 *     pois Symfony so' define text/html dentro de prepare(), que
 *     ainda nao foi chamado quando o middleware roda.
 *
 * Requirements: 8.1, 8.3, 8.4, 8.5, 8.7
 */

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Models\Setting;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SecurityHeadersMiddlewareTest extends TestCase
{
    /**
     * Directive keys obrigatorias na CSP gerada (Requirement 8.1).
     *
     * @var array<int, string>
     */
    private const REQUIRED_CSP_DIRECTIVES = [
        'default-src',
        'script-src',
        'style-src',
        'img-src',
        'font-src',
        'connect-src',
        'frame-src',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Cada teste comeca com cache de Setting limpo, sem hits em DB.
        $this->setSettingRuntime([]);
    }

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();
        parent::tearDown();
    }

    /* ---------------------------------------------------------------- */
    /* 1. CSP completa                                                   */
    /* ---------------------------------------------------------------- */

    public function test_csp_contains_all_required_directives_on_html_response(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $request    = Request::create('/admin/dashboard', 'GET');

        $response = $middleware->handle($request, $this->htmlNext());

        $csp = (string) $response->headers->get('Content-Security-Policy', '');
        $this->assertNotSame('', $csp, 'CSP deve estar presente em respostas HTML.');

        // 1.a) Todas as directives obrigatorias estao no header.
        foreach (self::REQUIRED_CSP_DIRECTIVES as $directive) {
            $this->assertMatchesRegularExpression(
                '/(?:^|;\s*)' . preg_quote($directive, '/') . '\s/',
                $csp,
                "Directive obrigatoria '{$directive}' ausente. CSP={$csp}"
            );
        }

        // 1.b) default-src 'self' (Requirement 8.1).
        $this->assertMatchesRegularExpression(
            "/(?:^|;\s*)default-src\s+'self'/",
            $csp,
            "default-src deve declarar 'self' como fonte base."
        );

        // 1.c) style-src contem 'unsafe-inline' (Requirement 8.8).
        $styleSrc = $this->extractDirective($csp, 'style-src');
        $this->assertNotNull($styleSrc, 'style-src ausente.');
        $this->assertStringContainsString(
            "'unsafe-inline'",
            $styleSrc,
            "style-src deve conter 'unsafe-inline' para compatibilidade com Summernote/jQuery plugins."
        );

        // 1.d) report-uri permanece desativado enquanto a rota /csp-report
        // nao existe, evitando 404 em producao.
        $this->assertStringNotContainsString(
            'report-uri',
            $csp,
            "report-uri nao deve ser emitido sem endpoint implementado. CSP={$csp}"
        );
    }

    public function test_csp_includes_real_cdn_allowlist(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $request    = Request::create('/admin/dashboard', 'GET');

        $response = $middleware->handle($request, $this->htmlNext());
        $csp      = (string) $response->headers->get('Content-Security-Policy', '');

        // CDNs reais usados na plataforma (subset das BASE_ALLOWLIST do middleware).
        $expectedCdns = [
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
            'https://cdn.tailwindcss.com',
            'https://code.jquery.com',
            'https://fonts.googleapis.com',
            'https://fonts.gstatic.com',
            'https://www.youtube.com',
        ];
        foreach ($expectedCdns as $cdn) {
            $this->assertStringContainsString(
                $cdn,
                $csp,
                "CDN '{$cdn}' deveria estar na allowlist base da CSP."
            );
        }
    }

    public function test_csp_allows_sumup_card_widget_sources(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $request    = Request::create('/eventos/30/reservar', 'POST');

        $response = $middleware->handle($request, $this->htmlNext());
        $csp      = (string) $response->headers->get('Content-Security-Policy', '');

        $scriptSrc = $this->extractDirective($csp, 'script-src');
        $frameSrc  = $this->extractDirective($csp, 'frame-src');

        $this->assertNotNull($scriptSrc, 'script-src ausente.');
        $this->assertStringContainsString(
            'https://gateway.sumup.com',
            (string) $scriptSrc,
            'script-src deve liberar o SDK oficial do SumUp Card Widget.'
        );
        $this->assertStringContainsString(
            'https://api.sumup.com',
            (string) $scriptSrc,
            'script-src deve liberar scripts auxiliares da integracao SumUp.'
        );

        $this->assertNotNull($frameSrc, 'frame-src ausente.');
        $this->assertStringContainsString(
            'https://gateway.sumup.com',
            (string) $frameSrc,
            'frame-src deve liberar o iframe seguro do SumUp Card Widget.'
        );
        $this->assertStringContainsString(
            'https://api.sumup.com',
            (string) $frameSrc,
            'frame-src deve liberar iframes auxiliares da integracao SumUp.'
        );
    }

    public function test_extra_allowlist_from_settings_is_appended_to_csp(): void
    {
        $this->setSettingRuntime([
            'csp_extra_allowlist' => [
                'connect-src' => ['https://api.exemplo.com', 'https://ws.exemplo.com'],
            ],
        ]);

        $middleware = new SecurityHeadersMiddleware();
        $response   = $middleware->handle(
            Request::create('/admin/dashboard', 'GET'),
            $this->htmlNext()
        );

        $csp        = (string) $response->headers->get('Content-Security-Policy', '');
        $connectSrc = $this->extractDirective($csp, 'connect-src');

        $this->assertNotNull($connectSrc, 'connect-src ausente.');
        $this->assertStringContainsString('https://api.exemplo.com', (string) $connectSrc);
        $this->assertStringContainsString('https://ws.exemplo.com', (string) $connectSrc);
    }

    public function test_csp_is_not_emitted_for_non_html_responses(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $request    = Request::create('/api/anything', 'GET');

        // Resposta JSON: middleware NAO deve emitir CSP, mas demais headers
        // de seguranca permanecem aplicados.
        $next = static function (): Response {
            $response = new Response('{"ok":true}', 200);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        };

        $response = $middleware->handle($request, $next);

        $this->assertFalse(
            $response->headers->has('Content-Security-Policy'),
            'CSP nao deve ser aplicada em respostas JSON (evita quebrar clientes).'
        );
        // Demais headers continuam vindo:
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
    }

    /* ---------------------------------------------------------------- */
    /* 2. Permissions-Policy                                             */
    /* ---------------------------------------------------------------- */

    public function test_permissions_policy_blocks_camera_on_normal_routes(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $response   = $middleware->handle(
            Request::create('/admin/dashboard', 'GET'),
            $this->htmlNext()
        );

        $policy = (string) $response->headers->get('Permissions-Policy', '');

        $this->assertStringContainsString('camera=()', $policy);
        $this->assertStringNotContainsString('camera=(self)', $policy);
        $this->assertStringContainsString('microphone=()', $policy);
        $this->assertStringContainsString('geolocation=()', $policy);
        $this->assertStringContainsString('payment=()', $policy);
    }

    /**
     * @dataProvider qrScannerRouteProvider
     */
    public function test_permissions_policy_allows_camera_on_qr_scanner_routes(string $path): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $response   = $middleware->handle(
            Request::create($path, 'GET'),
            $this->htmlNext()
        );

        $policy = (string) $response->headers->get('Permissions-Policy', '');

        $this->assertStringContainsString(
            'camera=(self)',
            $policy,
            "Rota QR scanner '{$path}' deveria liberar camera=(self)."
        );
        // Demais permissoes seguem bloqueadas mesmo em rotas de scanner.
        $this->assertStringContainsString('microphone=()', $policy);
        $this->assertStringContainsString('geolocation=()', $policy);
        $this->assertStringContainsString('payment=()', $policy);
    }

    /**
     * Rotas reais do projeto que casam com as patterns
     * QR_SCANNER_PATTERNS = ['scanner', 'quick-scanner', 'event-scanner'].
     *
     * @return array<string, array<int, string>>
     */
    public static function qrScannerRouteProvider(): array
    {
        return [
            'admin quick-scanner'      => ['/admin/quick-scanner'],
            'panel quick-scanner'      => ['/painel/admin/quick-scanner'],
            'panel instructor scanner' => ['/painel/instrutor/scanner'],
            'panel event scanner'      => ['/painel/events/123/scanner'],
            'admin event-scanner'      => ['/admin/event-scanner/42'],
        ];
    }

    /* ---------------------------------------------------------------- */
    /* 3. COOP / 4. CORP                                                 */
    /* ---------------------------------------------------------------- */

    public function test_coop_and_corp_headers_are_set_on_every_response(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $response   = $middleware->handle(
            Request::create('/admin/dashboard', 'GET'),
            $this->htmlNext()
        );

        $this->assertSame(
            'same-origin-allow-popups',
            $response->headers->get('Cross-Origin-Opener-Policy'),
            'COOP deve permitir popups (necessario para gateways de pagamento).'
        );

        $this->assertSame(
            'cross-origin',
            $response->headers->get('Cross-Origin-Resource-Policy'),
            'CORP cross-origin permite carregar recursos de CDN externos.'
        );
    }

    /* ---------------------------------------------------------------- */
    /* 5. HSTS                                                           */
    /* ---------------------------------------------------------------- */

    public function test_hsts_is_set_only_on_https_requests(): void
    {
        $middleware = new SecurityHeadersMiddleware();

        // Requisicao HTTP simples: HSTS NAO deve ser definido.
        $httpRequest  = Request::create('http://somosunn.test/admin/dashboard', 'GET');
        $httpResponse = $middleware->handle($httpRequest, $this->htmlNext());

        $this->assertFalse(
            $httpResponse->headers->has('Strict-Transport-Security'),
            'HSTS nao deve aparecer em respostas HTTP (evita confusao com proxies).'
        );

        // Requisicao HTTPS: HSTS presente com max-age longo e includeSubDomains.
        $httpsRequest  = Request::create('https://somosunn.test/admin/dashboard', 'GET');
        $httpsResponse = $middleware->handle($httpsRequest, $this->htmlNext());

        $hsts = (string) $httpsResponse->headers->get('Strict-Transport-Security', '');
        $this->assertNotSame('', $hsts, 'HSTS deve estar presente em HTTPS.');
        $this->assertMatchesRegularExpression('/max-age=\d+/', $hsts);
        $this->assertStringContainsString('includeSubDomains', $hsts);
    }

    /* ---------------------------------------------------------------- */
    /* 6. X-Frame-Options + 7. Referrer-Policy + headers basicos         */
    /* ---------------------------------------------------------------- */

    public function test_basic_security_headers_are_applied_on_normal_routes(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $response   = $middleware->handle(
            Request::create('/admin/dashboard', 'GET'),
            $this->htmlNext()
        );

        // X-Frame-Options: middleware usa SAMEORIGIN (protege clickjacking
        // sem quebrar iframes internos da plataforma).
        $this->assertSame(
            'SAMEORIGIN',
            $response->headers->get('X-Frame-Options'),
            'X-Frame-Options deve estar definido em rotas comuns.'
        );

        $this->assertSame(
            'nosniff',
            $response->headers->get('X-Content-Type-Options'),
            'X-Content-Type-Options deve evitar MIME sniffing.'
        );

        $this->assertSame(
            'strict-origin-when-cross-origin',
            $response->headers->get('Referrer-Policy'),
            'Referrer-Policy deve seguir o padrao strict-origin-when-cross-origin.'
        );
    }

    public function test_x_frame_options_is_omitted_for_embed_routes(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $response   = $middleware->handle(
            Request::create('/embed/widget/123', 'GET'),
            $this->htmlNext()
        );

        $this->assertFalse(
            $response->headers->has('X-Frame-Options'),
            'Rotas de /embed/ nao devem receber X-Frame-Options para permitir iframe externo.'
        );
        // Demais headers continuam aplicados.
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function test_server_disclosure_headers_are_removed(): void
    {
        $middleware = new SecurityHeadersMiddleware();

        $next = static function (): Response {
            $response = new Response('<html></html>', 200);
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
            $response->headers->set('X-Powered-By', 'PHP/8.4.0');
            $response->headers->set('Server', 'LiteSpeed');

            return $response;
        };

        $response = $middleware->handle(
            Request::create('/admin/dashboard', 'GET'),
            $next
        );

        $this->assertFalse(
            $response->headers->has('X-Powered-By'),
            'X-Powered-By deve ser removido para nao expor versao do PHP.'
        );
        $this->assertFalse(
            $response->headers->has('Server'),
            'Header Server deve ser removido para nao expor o servidor web.'
        );
    }

    public function test_private_routes_disable_browser_and_proxy_cache(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $response = $middleware->handle(
            Request::create('/painel/perfil', 'GET'),
            $this->htmlNext()
        );

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
        $this->assertSame('no-cache', $response->headers->get('Pragma'));
    }

    /* ---------------------------------------------------------------- */
    /* 9. Fallback CSP minima segura                                     */
    /* ---------------------------------------------------------------- */

    public function test_minimal_safe_csp_is_used_when_csp_pipeline_fails(): void
    {
        $middleware = new SecurityHeadersMiddleware();

        $reflection = new ReflectionClass(SecurityHeadersMiddleware::class);
        $method     = $reflection->getMethod('minimalSafeCsp');
        $method->setAccessible(true);

        $fallback = (string) $method->invoke($middleware);

        // O fallback deve ser uma CSP curta, sem allowlist externa, mas
        // com directives criticas suficientes para nao quebrar HTML basico.
        $this->assertNotSame('', $fallback, 'Fallback CSP nunca deve ser vazio.');
        $this->assertStringContainsString("default-src 'self'", $fallback);
        $this->assertStringContainsString("img-src 'self'", $fallback);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline'", $fallback);

        // Fallback NAO deve incluir CDNs externos (justamente porque a
        // configuracao falhou — e' uma CSP minima e segura).
        $this->assertStringNotContainsString('cdn.jsdelivr.net', $fallback);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $fallback);
    }

    public function test_invalid_extra_allowlist_does_not_break_base_csp(): void
    {
        // String que nao e' JSON valido em csp_extra_allowlist: o middleware
        // deve ignorar o extra silenciosamente e ainda emitir a CSP base
        // completa (Requirement 8.6).
        $this->setSettingRuntime([
            'csp_extra_allowlist' => '<<not json at all>>',
        ]);

        $middleware = new SecurityHeadersMiddleware();
        $response   = $middleware->handle(
            Request::create('/admin/dashboard', 'GET'),
            $this->htmlNext()
        );

        $csp = (string) $response->headers->get('Content-Security-Policy', '');

        $this->assertNotSame('', $csp, 'CSP base deve ser emitida mesmo com allowlist invalida.');
        foreach (self::REQUIRED_CSP_DIRECTIVES as $directive) {
            $this->assertMatchesRegularExpression(
                '/(?:^|;\s*)' . preg_quote($directive, '/') . '\s/',
                $csp,
                "Directive '{$directive}' deveria continuar presente apesar do allowlist invalido."
            );
        }
        $this->assertStringNotContainsString('report-uri', $csp);
    }

    /* ---------------------------------------------------------------- */
    /* Helpers                                                           */
    /* ---------------------------------------------------------------- */

    /**
     * Builder padrao do `$next` para respostas HTML — Symfony so' define
     * Content-Type dentro de prepare(), entao precisamos setar manualmente
     * para que a CSP seja emitida pelo middleware.
     */
    private function htmlNext(): \Closure
    {
        return static function (): Response {
            $response = new Response('<html><body>ok</body></html>', 200);
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

            return $response;
        };
    }

    /**
     * Extrai o conteudo (lista de fontes) de uma directive especifica
     * dentro do header CSP serializado. Retorna null se ausente.
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
     * evitar hits em banco. As chaves sobrevivem a Setting::get() pois
     * loadRuntimeCache() detecta o cache ja carregado.
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
