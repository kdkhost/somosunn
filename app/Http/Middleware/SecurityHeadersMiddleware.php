<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adiciona cabeçalhos de segurança em toda resposta HTTP.
 *
 * CSP e Cross-Origin policies desabilitados temporariamente para
 * não quebrar CDNs e formulários de pagamento externos.
 * Headers seguros que NÃO quebram nada estão ativos.
 */
class SecurityHeadersMiddleware
{
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

        // Permissions-Policy (permite camera para scanner de QR code)
        $response->headers->set(
            'Permissions-Policy',
            'microphone=(), geolocation=()'
        );

        // HSTS (apenas em HTTPS)
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=15552000; includeSubDomains'
            );
        }

        // Remove headers que expõem versão do servidor
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
}
