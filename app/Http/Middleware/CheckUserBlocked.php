<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class CheckUserBlocked
{
    /**
     * Verifica se o usuario esta bloqueado (punicao por nao-entrega).
     * Se bloqueado, redireciona para uma pagina informativa.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Admin/superadmin nunca e bloqueado
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar bloqueio
        if ($user->blocked_until && Carbon::parse($user->blocked_until)->isFuture()) {
            // Permitir acesso a rotas basicas (perfil, logout, notificacoes)
            $allowedRoutes = [
                'logout',
                'panel.profile.*',
                'panel.dashboard',
                'notifications.*',
            ];

            if ($request->routeIs($allowedRoutes)) {
                return $next($request);
            }

            $blockedUntil = Carbon::parse($user->blocked_until)->format('d/m/Y H:i');
            $reason = $user->block_reason ?: 'Sua conta esta temporariamente bloqueada.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Conta bloqueada ate {$blockedUntil}. Motivo: {$reason}",
                    'blocked_until' => $user->blocked_until,
                ], 403);
            }

            return redirect()->route('panel.dashboard')
                ->with('error', "Sua conta esta bloqueada ate {$blockedUntil}. Motivo: {$reason}");
        }

        // Se o bloqueio expirou, limpar os campos
        if ($user->blocked_until && Carbon::parse($user->blocked_until)->isPast()) {
            $user->update([
                'blocked_until' => null,
                'block_reason' => null,
            ]);
        }

        return $next($request);
    }
}
