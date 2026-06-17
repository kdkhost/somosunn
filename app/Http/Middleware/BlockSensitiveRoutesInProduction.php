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
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BlockSensitiveRoutesInProduction
{
    private const SENSITIVE_PATTERNS = [
        '#^/?debug-test$#i',
        '#^/?limpar-cache$#i',
        '#^/?run-migrations$#i',
        '#^/?demo-somos-unicas$#i',
        '#^/?install(/|$)#i',
        '#^/?backend/install(/|$)#i',
        '#^/?run$#i',
        '#^/?test-connection$#i',
        '#^/?telescope(/|$)#i',
        '#^/?horizon(/|$)#i',
        '#^/?_debugbar(/|$)#i',
        '#^/?phpinfo#i',
        '#^/?adminer#i',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->isSensitiveRoute($request)) {
            return $next($request);
        }

        if ($this->shouldBlock($request)) {
            $this->logBlockedAttempt($request);
            abort(404);
        }

        return $next($request);
    }

    private function isSensitiveRoute(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        foreach (self::SENSITIVE_PATTERNS as $pattern) {
            if (preg_match($pattern, $path) === 1) {
                return true;
            }
        }

        return false;
    }

    private function shouldBlock(Request $request): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        if ($this->isLocalHttpRequest($request) && app()->environment('local', 'testing')) {
            return false;
        }

        if ($this->isInstallerRoute($request) && (bool) config('maintenance.allow_installer', false)) {
            return false;
        }

        return !(bool) config('maintenance.allow_sensitive_routes', false);
    }

    private function isLocalHttpRequest(Request $request): bool
    {
        $ip = (string) $request->ip();

        return in_array($ip, ['127.0.0.1', '::1'], true) || str_starts_with($ip, '192.168.');
    }

    private function isInstallerRoute(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        return preg_match('#^/?install(/|$)#i', $path) === 1
            || preg_match('#^/?backend/install(/|$)#i', $path) === 1;
    }

    private function logBlockedAttempt(Request $request): void
    {
        try {
            Log::warning('Rota de manutencao bloqueada', [
                'path' => '/' . ltrim($request->path(), '/'),
                'method' => $request->method(),
                'ip_hash' => hash('sha256', (string) $request->ip()),
                'user_id' => $request->user()?->id,
            ]);
        } catch (\Throwable) {
            // Nao interrompe a resposta 404 caso o canal de log esteja indisponivel.
        }
    }
}
