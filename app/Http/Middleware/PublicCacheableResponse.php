<?php

namespace App\Http\Middleware;

use App\Support\DomainManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicCacheableResponse
{
    /**
     * Ajusta headers de cache de borda para paginas publicas em visitantes anonimos
     * e remove cookies de sessao/CSRF quando a requisicao e stateless.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $isStateless = (bool) $request->attributes->get('unn.stateless_public', false);
        $noEdgeCache = (bool) $request->attributes->get('unn.no_edge_cache', false);
        $status = $response->getStatusCode();

        $canEdgeCache = $isStateless
            && !$noEdgeCache
            && DomainManager::isPublicCacheEnabled()
            && in_array($request->getMethod(), ['GET', 'HEAD'], true)
            && $status >= 200
            && $status < 400
            && $request->user() === null;

        if ($canEdgeCache) {
            $sessionCookie = (string) config('session.cookie', 'unn_session');
            $stripCookies = array_filter([$sessionCookie, 'XSRF-TOKEN']);

            foreach ($response->headers->getCookies() as $cookie) {
                if (in_array($cookie->getName(), $stripCookies, true)) {
                    $response->headers->removeCookie(
                        $cookie->getName(),
                        $cookie->getPath(),
                        $cookie->getDomain()
                    );
                }
            }

            $maxAge = max(60, (int) config('domains.public_cache_max_age', 300));
            $sMaxAge = max($maxAge, (int) config('domains.public_cache_s_maxage', 600));

            $response->headers->remove('Pragma');
            $response->headers->remove('Expires');
            $response->headers->set(
                'Cache-Control',
                "public, max-age={$maxAge}, s-maxage={$sMaxAge}, stale-while-revalidate=60"
            );
            $response->headers->set('Vary', 'Accept-Encoding');
            $response->headers->set('X-UNN-Cache', 'public-edge');

            return $response;
        }

        if ($request->user() !== null || $noEdgeCache || $status >= 500) {
            $response->headers->set('Cache-Control', 'no-store, private, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Vary', 'Cookie');
            $response->headers->set('X-UNN-Cache', 'bypass');
        }

        return $response;
    }
}
