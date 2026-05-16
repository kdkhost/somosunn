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
 * Sistema UNN - Property test (Property 13) para sensibilidade
 * por rota do header Permissions-Policy emitido por
 * SecurityHeadersMiddleware.
 *
 * Spec: .kiro/specs/advanced-security-performance (task 12.3)
 *
 * Property 13: Permissions-Policy Route Sensitivity
 *
 *   Para QUALQUER request path:
 *     1) Se a rota e' de QR scanner (matches `quick-scanner`,
 *        `event-scanner` ou `scanner` no path), o header
 *        `Permissions-Policy` DEVE conter `camera=(self)`.
 *     2) Caso contrario, o header DEVE conter `camera=()`.
 *     3) `microphone`, `geolocation` e `payment` SAO SEMPRE
 *        bloqueados (`=()`), independentemente da rota.
 *
 * ESTRATEGIA DE TESTE:
 *   - Geramos paths a partir de um pool finito que cobre rotas
 *     reais da plataforma (admin, painel, instrutor, eventos)
 *     tanto em modo "normal" quanto em modo "QR scanner".
 *   - Para cada path geramos uma Request real e executamos o
 *     middleware com um $next minimo (sem dependencia de CSP).
 *   - O oraculo (`$isQrRoute`) usa o MESMO criterio do middleware
 *     (substring match, case-insensitive) — a propriedade afirma
 *     que o middleware respeita esse contrato.
 *
 * Validates: Requirements 8.3
 */

namespace Tests\Property;

use App\Http\Middleware\SecurityHeadersMiddleware;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PermissionsPolicyRouteTest extends TestCase
{
    use TestTrait;

    /**
     * Pool de paths cobrindo cenarios normais e de QR scanner.
     * Os paths de scanner (quick-scanner / event-scanner /
     * .../scanner) seguem nomes reais usados em routes/web.php.
     *
     * @var array<int, string>
     */
    private const PATH_POOL = [
        // Rotas normais (sem camera)
        '/admin/dashboard',
        '/painel/configuracoes',
        '/api/users',
        '/painel/perfil',
        '/admin/relatorios',
        '/painel/loja/produtos',

        // Rotas de QR scanner (camera=(self) liberada)
        '/admin/quick-scanner',
        '/painel/admin/quick-scanner',
        '/painel/instrutor/scanner',
        '/painel/events/123/scanner',
        '/admin/event-scanner/42',
        '/admin/events/7/scanner',
    ];

    /**
     * Diretivas que SEMPRE devem estar bloqueadas (=()),
     * independentemente do tipo de rota.
     *
     * @var array<int, string>
     */
    private const ALWAYS_BLOCKED_DIRECTIVES = [
        'microphone',
        'geolocation',
        'payment',
    ];

    /**
     * Compatibilidade com PHPUnit 10: Eris 0.14.x ainda invoca
     * \PHPUnit\Util\Test::parseTestMethodAnnotations() (removida
     * na PHPUnit 10). Retornar [] faz a trait operar com defaults.
     *
     * @return array<string, mixed>
     */
    public function getTestCaseAnnotations(): array
    {
        return [];
    }

    /**
     * Property 13: o header Permissions-Policy reflete o tipo de
     * rota — `camera=(self)` apenas em rotas QR scanner; demais
     * rotas recebem `camera=()`. microphone/geolocation/payment
     * permanecem bloqueados em qualquer cenario.
     *
     * Validates: Requirements 8.3
     */
    public function test_camera_self_only_on_qr_scanner_routes(): void
    {
        /** @var SecurityHeadersMiddleware $middleware */
        $middleware = $this->app->make(SecurityHeadersMiddleware::class);

        $this
            ->forAll(Generators::elements(self::PATH_POOL))
            ->then(function (string $path) use ($middleware): void {
                // Mesmo criterio do middleware: substring match
                // case-insensitive contra os patterns de scanner.
                // Como `quick-scanner` e `event-scanner` ja contem
                // `scanner`, basta verificar a palavra `scanner`.
                $isQrRoute = stripos($path, 'scanner') !== false;

                $request = Request::create($path, 'GET');
                $next = static function (): Response {
                    // Resposta nao-HTML para isolar a propriedade de
                    // CSP (que so' e' aplicada em text/html). O header
                    // Permissions-Policy e' sempre setado.
                    return new Response('', 200, ['Content-Type' => 'application/json']);
                };

                $response = $middleware->handle($request, $next);

                $policy = (string) $response->headers->get('Permissions-Policy', '');

                $this->assertNotSame(
                    '',
                    $policy,
                    sprintf('Property 13 violada: header Permissions-Policy ausente para path=%s', $path)
                );

                // 1) camera correto conforme o tipo de rota.
                $expectedCamera = $isQrRoute ? 'camera=(self)' : 'camera=()';
                $this->assertStringContainsString(
                    $expectedCamera,
                    $policy,
                    sprintf(
                        "Property 13.1 violada: rota %s='%s' deveria emitir '%s'. Policy=%s",
                        $isQrRoute ? 'QR' : 'normal',
                        $path,
                        $expectedCamera,
                        $policy
                    )
                );

                // 2) Mutua exclusao: nunca os dois ao mesmo tempo.
                $forbiddenCamera = $isQrRoute ? 'camera=()' : 'camera=(self)';
                $this->assertStringNotContainsString(
                    $forbiddenCamera,
                    $policy,
                    sprintf(
                        "Property 13.2 violada: rota %s='%s' nao deveria emitir '%s'. Policy=%s",
                        $isQrRoute ? 'QR' : 'normal',
                        $path,
                        $forbiddenCamera,
                        $policy
                    )
                );

                // 3) microphone/geolocation/payment SEMPRE bloqueados.
                foreach (self::ALWAYS_BLOCKED_DIRECTIVES as $directive) {
                    $this->assertStringContainsString(
                        $directive . '=()',
                        $policy,
                        sprintf(
                            "Property 13.3 violada: '%s=()' ausente para path='%s'. Policy=%s",
                            $directive,
                            $path,
                            $policy
                        )
                    );
                }
            });
    }
}
