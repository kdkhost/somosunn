<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adiciona cabeçalhos de segurança em toda resposta HTTP.
 *
 * NÃO quebra scripts/assets existentes — CSP usa 'unsafe-inline' e
 * 'unsafe-eval' para compatibilidade com jQuery/AdminLTE/Chart.js.
 * Em evolução futura, migrar para nonce-based CSP.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 18.1-18.7, 4.4, 4.5
 * Prompt de segurança item 8: Headers de Segurança
 */
class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // X-Frame-Options (clickjacking)
        if (! $this->isEmbedRoute($request)) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        // X-Content-Type-Options (MIME sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer-Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(self)'
        );

        // HSTS (apenas em HTTPS)
        if ($request->isSecure() || config('app.url_scheme') === 'https') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=15552000; includeSubDomains'
            );
        }

        // Cross-Origin policies
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-site');

        // CSP moderada (compatível com jQuery + AdminLTE + Chart.js + inline scripts)
        if ($this->isHtmlResponse($response)) {
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com",
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
                "img-src 'self' data: blob: https: http:",
                "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
                "connect-src 'self' https:",
                "media-src 'self' blob: https:",
                "frame-src 'self' https://www.google.com https://www.youtube.com",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ]);

            $response->headers->set('Content-Security-Policy', $csp);
        }

        // Remove headers que expõem versão
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }

    /**
     * Rotas que precisam ser embutidas em iframe externo (ex.: AffiliateEmbed).
     */
    private function isEmbedRoute(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        return str_starts_with($path, '/embed/')
            || str_starts_with($path, '/affiliate-embed');
    }

    private function isHtmlResponse(Response $response): bool
    {
        $ct = $response->headers->get('Content-Type', '');

        return str_contains($ct, 'text/html') || str_contains($ct, 'application/xhtml');
    }
}
