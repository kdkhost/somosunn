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
 * Sistema UNN - SecurityHeadersMiddleware
 *
 * Adiciona cabecalhos de seguranca em toda resposta HTTP:
 *   - X-Frame-Options (exceto rotas de embed)
 *   - X-Content-Type-Options
 *   - Referrer-Policy
 *   - Permissions-Policy (camera liberada apenas em rotas de QR scanner)
 *   - HSTS (apenas em HTTPS)
 *   - Content-Security-Policy (apenas em respostas HTML; com allowlist
 *     configuravel via settings.csp_extra_allowlist)
 *   - Cross-Origin-Opener-Policy: same-origin-allow-popups
 *   - Cross-Origin-Resource-Policy: cross-origin
 *
 * Em caso de configuracao invalida da CSP, aplica fallback minimo seguro.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 8.8
 */

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Padroes de path que liberam `camera=(self)` na Permissions-Policy.
     */
    private const QR_SCANNER_PATTERNS = [
        'scanner',
        'quick-scanner',
        'event-scanner',
    ];

    /**
     * Endpoint de report-uri da CSP (desativado — rota nao implementada).
     * Mantido como referencia para futura implementacao.
     */
    // private const CSP_REPORT_URI = '/csp-report';

    /**
     * Fontes base (CDNs reais utilizados pela plataforma) por directive.
     * NUNCA inclui tokens especiais aqui — eles sao adicionados em
     * buildCspHeader() para garantir ordem e quoting corretos.
     *
     * @var array<string, array<int, string>>
     */
    private const BASE_ALLOWLIST = [
        'script-src' => [
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
            'https://cdn.tailwindcss.com',
            'https://cdn.datatables.net',
            'https://js.pusher.com',
            'https://code.jquery.com',
            'https://www.googletagmanager.com',
            'https://www.google-analytics.com',
            'https://unpkg.com',
        ],
        'style-src' => [
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
            'https://cdn.datatables.net',
            'https://fonts.googleapis.com',
            'https://unpkg.com',
        ],
        'img-src' => [
            'data:',
            'blob:',
            'https:',
            'http:',
        ],
        'font-src' => [
            'data:',
            'https://fonts.gstatic.com',
            'https://cdnjs.cloudflare.com',
            'https://cdn.jsdelivr.net',
            'https://use.fontawesome.com',
        ],
        'connect-src' => [
            'https:',
            'wss:',
        ],
        'frame-src' => [
            'https://www.youtube.com',
            'https://player.vimeo.com',
            'https://www.mercadopago.com.br',
            'https://api.mercadopago.com',
            'https://www.sumup.com',
            'https://gateway.sumup.com',
            'https://www.openstreetmap.org',
            'https://maps.google.com',
        ],
        // media-src cobre <video src="blob:...">/<audio src="blob:...">
        // criados via URL.createObjectURL() em previews de upload, alem de
        // CDNs/HTTPS para arquivos servidos via S3 publico.
        'media-src' => [
            'blob:',
            'data:',
            'https:',
        ],
        // worker-src nao tem fallback para default-src em alguns browsers;
        // mantemos blob: liberado para Web Workers gerados dinamicamente
        // (TinyMCE, processamento de imagem em browser, etc.).
        'worker-src' => [
            'blob:',
        ],
        // child-src cobre Web Workers e nested browsing contexts em
        // browsers mais antigos que ainda usam o nome legado.
        'child-src' => [
            'blob:',
        ],
        // object-src bloqueado por padrao — protege contra Flash e
        // plugins legados; exceto data: para PDF embeds.
        'object-src' => [
            'data:',
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // X-Frame-Options (SAMEORIGIN — protege contra clickjacking sem quebrar iframes internos)
        if (! $this->isEmbedRoute($request)) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        // X-Content-Type-Options (previne MIME sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer-Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy (camera liberada apenas em rotas de QR scanner)
        $response->headers->set(
            'Permissions-Policy',
            $this->buildPermissionsPolicy($request)
        );

        // Cross-Origin policies (sempre presentes; necessarios para popups
        // de pagamento e recursos servidos por CDNs)
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin');

        // HSTS (apenas em HTTPS)
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=15552000; includeSubDomains'
            );
        }

        // Content-Security-Policy: aplicar somente em respostas HTML.
        // Endpoints JSON, arquivos, imagens e streams nao recebem CSP
        // para evitar quebra de clients/integracao.
        if ($this->shouldApplyCsp($response)) {
            $response->headers->set('Content-Security-Policy', $this->buildCspHeader());
        }

        // Remove headers que expoem versao do servidor
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }

    private function isEmbedRoute(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        return str_starts_with($path, '/embed/')
            || str_starts_with($path, '/affiliate-embed');
    }

    /**
     * Decide se o response atual e' um documento HTML (e portanto deve
     * receber a CSP). Usa o header Content-Type real definido pelo
     * framework. Quando o tipo nao e' resolvivel (response vazia), nao
     * aplica CSP por seguranca operacional (evita quebrar binarios).
     */
    private function shouldApplyCsp(Response $response): bool
    {
        $contentType = (string) $response->headers->get('Content-Type', '');

        if ($contentType === '') {
            return false;
        }

        return str_starts_with(strtolower($contentType), 'text/html');
    }

    /**
     * Constroi o header Permissions-Policy. Em rotas de QR scanner a
     * camera e' permitida apenas para o proprio site (self); demais
     * rotas tem camera totalmente bloqueada. microphone, geolocation
     * e payment ficam bloqueados em qualquer rota.
     */
    private function buildPermissionsPolicy(Request $request): string
    {
        $cameraDirective = $this->isQrScannerRoute($request) ? 'camera=(self)' : 'camera=()';

        return implode(', ', [
            $cameraDirective,
            'microphone=()',
            'geolocation=()',
            'payment=()',
        ]);
    }

    private function isQrScannerRoute(Request $request): bool
    {
        $path = strtolower('/' . ltrim($request->path(), '/'));

        foreach (self::QR_SCANNER_PATTERNS as $needle) {
            if (str_contains($path, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Constroi o valor final do header Content-Security-Policy a partir
     * de:
     *   1. directives base (default-src, script-src, ...);
     *   2. allowlist base (CDNs reais);
     *   3. allowlist extra configurada em settings.csp_extra_allowlist.
     *
     * Em caso de erro (settings indisponivel, JSON invalido, etc.) faz
     * fallback para uma CSP minima segura e loga no canal `security`.
     */
    private function buildCspHeader(): string
    {
        try {
            $directives = $this->buildDirectives();

            return $this->serializeDirectives($directives);
        } catch (\Throwable $e) {
            Log::channel('security')->warning('CSP build failed, applying safe fallback.', [
                'exception' => $e->getMessage(),
            ]);

            return $this->minimalSafeCsp();
        }
    }

    /**
     * Monta o array final de directives mesclando base + extras de settings.
     *
     * @return array<string, array<int, string>>
     */
    private function buildDirectives(): array
    {
        $directives = [
            'default-src' => ["'self'"],
            'script-src'  => array_merge(
                ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
                self::BASE_ALLOWLIST['script-src']
            ),
            'style-src'   => array_merge(
                ["'self'", "'unsafe-inline'"],
                self::BASE_ALLOWLIST['style-src']
            ),
            'img-src'     => array_merge(
                ["'self'"],
                self::BASE_ALLOWLIST['img-src']
            ),
            'font-src'    => array_merge(
                ["'self'"],
                self::BASE_ALLOWLIST['font-src']
            ),
            'connect-src' => array_merge(
                ["'self'"],
                self::BASE_ALLOWLIST['connect-src']
            ),
            'frame-src'   => array_merge(
                ["'self'"],
                self::BASE_ALLOWLIST['frame-src']
            ),
            'media-src'   => array_merge(
                ["'self'"],
                self::BASE_ALLOWLIST['media-src']
            ),
            'worker-src'  => array_merge(
                ["'self'"],
                self::BASE_ALLOWLIST['worker-src']
            ),
            'child-src'   => array_merge(
                ["'self'"],
                self::BASE_ALLOWLIST['child-src']
            ),
            // object-src restrito a 'self' + data: — sem 'self' apenas
            // se nao usarmos <object>/<embed> em nenhum fluxo. Mantemos
            // 'self' para nao quebrar PDFs que sao servidos pelo proprio
            // dominio (ex.: /admin/invoices/{id}/pdf).
            'object-src'  => array_merge(
                ["'self'"],
                self::BASE_ALLOWLIST['object-src']
            ),
        ];

        $extra = $this->loadExtraAllowlist();
        foreach ($extra as $directive => $sources) {
            if (! isset($directives[$directive])) {
                continue;
            }

            foreach ($sources as $source) {
                $source = trim((string) $source);
                if ($source === '') {
                    continue;
                }

                if (! in_array($source, $directives[$directive], true)) {
                    $directives[$directive][] = $source;
                }
            }
        }

        return $directives;
    }

    /**
     * Le a chave `csp_extra_allowlist` (JSON object com directive=>sources)
     * de settings. Em caso de erro/JSON invalido retorna array vazio
     * (sem extras) — a CSP base continua valida.
     *
     * @return array<string, array<int, string>>
     */
    private function loadExtraAllowlist(): array
    {
        try {
            $raw = Setting::get('csp_extra_allowlist', null);
        } catch (\Throwable $e) {
            Log::channel('security')->warning('CSP allowlist setting unreadable.', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }

        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_array($raw)) {
            $decoded = $raw;
        } else {
            $decoded = json_decode((string) $raw, true);
        }

        if (! is_array($decoded)) {
            Log::channel('security')->warning('CSP allowlist setting is not valid JSON object.', [
                'raw_type' => gettype($raw),
            ]);

            return [];
        }

        $normalized = [];
        foreach ($decoded as $directive => $sources) {
            if (! is_string($directive) || $directive === '') {
                continue;
            }

            if (is_string($sources)) {
                $sources = [$sources];
            }

            if (! is_array($sources)) {
                continue;
            }

            $clean = [];
            foreach ($sources as $source) {
                if (! is_string($source)) {
                    continue;
                }
                $source = trim($source);
                if ($source === '') {
                    continue;
                }
                $clean[] = $source;
            }

            if ($clean !== []) {
                $normalized[$directive] = $clean;
            }
        }

        return $normalized;
    }

    /**
     * Serializa o array de directives no formato textual do header.
     *
     * @param array<string, array<int, string>> $directives
     */
    private function serializeDirectives(array $directives): string
    {
        $parts = [];
        foreach ($directives as $directive => $sources) {
            // remove duplicatas mantendo ordem
            $sources = array_values(array_unique($sources));
            $parts[] = $directive . ' ' . implode(' ', $sources);
        }

        return implode('; ', $parts);
    }

    /**
     * CSP minima segura usada como fallback quando a configuracao falha.
     */
    private function minimalSafeCsp(): string
    {
        return "default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline'";
    }
}
