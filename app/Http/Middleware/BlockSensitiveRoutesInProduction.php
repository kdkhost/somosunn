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

/**
 * Bloqueia rotas sensíveis (install, migrations, debug) em produção.
 *
 * Regras:
 *   - Em APP_ENV=production, bloqueia rotas listadas abaixo
 *   - Permite acesso se:
 *     a) Usuário autenticado com role super-admin, OU
 *     b) Header/query `maintenance_token` == env('MAINTENANCE_SECRET')
 *   - Registra tentativa bloqueada no canal `security`
 *
 * NÃO remove rotas — apenas protege.
 *
 * Prompt de segurança item 1: Rotas Sensíveis
 */
class BlockSensitiveRoutesInProduction
{
    /**
     * Padrões de rotas sensíveis (regex PCRE).
     */
    private const SENSITIVE_PATTERNS = [
        '#^/?run-migrations#i',
        '#^/?install(/|$)#i',
        '#^/?backend/install(/|$)#i',
        '#^/?run$#i',
        '#^/?test-connection$#i',
        '#^/?demo-somos-unicas#i',
        '#^/?telescope(/|$)#i',
        '#^/?horizon(/|$)#i',
        '#^/?_debugbar(/|$)#i',
        '#^/?phpinfo#i',
        '#^/?adminer#i',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Bloqueia em qualquer ambiente que não seja localhost real
        // (APP_ENV=local em servidor remoto ainda é produção de fato)
        $isRealLocal = in_array($request->ip(), ['127.0.0.1', '::1'])
            || str_starts_with($request->ip(), '192.168.')
            || app()->runningInConsole()
            || app()->runningUnitTests();

        if ($isRealLocal && app()->environment('local', 'testing')) {
            return $next($request);
        }

        $path = '/' . ltrim($request->path(), '/');

        // Verifica se a rota é sensível
        $isSensitive = false;
        foreach (self::SENSITIVE_PATTERNS as $pattern) {
            if (@preg_match($pattern, $path) === 1) {
                $isSensitive = true;
                break;
            }
        }

        if (! $isSensitive) {
            return $next($request);
        }

        // Permite se tem token de manutenção válido
        $secret = env('MAINTENANCE_SECRET');
        if ($secret && strlen($secret) >= 8) {
            $token = $request->header('X-Maintenance-Token')
                ?? $request->query('maintenance_token');

            if ($token && hash_equals($secret, $token)) {
                return $next($request);
            }
        }

        // Permite se é superadmin autenticado
        $user = $request->user();
        if ($user) {
            $isSuperAdmin = method_exists($user, 'hasRole')
                ? $user->hasRole('super-admin')
                : (($user->role ?? '') === 'super-admin' || ($user->is_superadmin ?? false));

            if ($isSuperAdmin) {
                return $next($request);
            }
        }

        // Bloqueia e registra
        try {
            Log::channel('security')->warning('Tentativa de acesso a rota sensível bloqueada', [
                'path'       => $path,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id'    => $user?->id,
            ]);
        } catch (\Throwable $e) {
            // Canal security pode não existir ainda
            Log::warning('Rota sensível bloqueada: ' . $path, ['ip' => $request->ip()]);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        abort(403, 'Acesso negado.');
    }
}
