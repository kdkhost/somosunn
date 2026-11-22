<?php

namespace App\Http\Middleware;

use App\Support\DomainManager;
use Closure;
use Illuminate\Http\Request;

class EnforceSubdomainRouting
{
    /**
     * Garante o isolamento de carga entre dominio principal (public),
     * painel/sistema (app) e endpoints de API/webhook (api), sem quebrar
     * ambientes locais ou chamadas POST durante transicao de DNS.
     */
    public function handle(Request $request, Closure $next, string $zone = 'app')
    {
        if (!DomainManager::isRoutingEnabled($request->getHost())) {
            return $next($request);
        }

        // Nunca redireciona metodos de escrita nem webhooks externos para preservar integracoes legadas
        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        if ($request->is('webhook/*', 'v1/webhooks/*', 'api/*', 'health', 'manifest.webmanifest', 'service-worker.js', 'offline')) {
            return $next($request);
        }

        $currentHost = DomainManager::normalizeHost($request->getHost());
        $mainHost = DomainManager::mainDomain();
        $appHost = DomainManager::appDomain();
        $apiHost = DomainManager::apiDomain();

        if ($zone === 'app' && $currentHost !== $appHost) {
            return redirect()->away(
                DomainManager::scheme($request) . '://' . $appHost . $request->getRequestUri(),
                302
            );
        }

        if ($zone === 'public') {
            if ($currentHost === $apiHost) {
                return redirect()->away(
                    DomainManager::scheme($request) . '://' . $mainHost . $request->getRequestUri(),
                    302
                );
            }
        }

        return $next($request);
    }
}
