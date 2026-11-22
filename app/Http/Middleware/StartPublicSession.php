<?php

namespace App\Http\Middleware;

use App\Support\DomainManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\NullSessionHandler;
use Illuminate\Session\Store;

class StartPublicSession extends StartSession
{
    /**
     * Para visitantes anonimos em GET/HEAD das rotas publicas, utiliza sessao
     * efemera em memoria (NullSessionHandler) sem gravar arquivo em disco nem
     * emitir cookie `unn_session`, permitindo cache total no Cloudflare/LiteSpeed.
     */
    public function handle($request, Closure $next)
    {
        if ($this->shouldUseStatelessSession($request)) {
            $cookieName = (string) config('session.cookie', 'unn_session');
            $store = new Store($cookieName, new NullSessionHandler());
            $store->setRequestOnHandler($request);
            $store->start();

            $request->setLaravelSession($store);
            $request->attributes->set('unn.stateless_public', true);

            return $next($request);
        }

        return parent::handle($request, $next);
    }

    private function shouldUseStatelessSession(Request $request): bool
    {
        if (!DomainManager::isPublicCacheEnabled()) {
            return false;
        }

        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return false;
        }

        if ($request->headers->has('Authorization')) {
            return false;
        }

        // Se existir parametro de liberacao manual de manutencao, preservar emissao de cookie
        if ($request->query->has('maintenance_bypass_token')) {
            return false;
        }

        $sessionCookie = (string) config('session.cookie', 'unn_session');
        if ($sessionCookie !== '' && $request->cookies->has($sessionCookie)) {
            return false;
        }

        foreach ($request->cookies->keys() as $cookieKey) {
            if (str_starts_with((string) $cookieKey, 'remember_web_')) {
                return false;
            }
        }

        return true;
    }
}
