<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            Log::warning('Tentativa de acesso administrativo sem autenticação', [
                'route' => optional($request->route())->getName(),
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acesso nao autorizado.'], 403);
            }

            return redirect()
                ->route('panel.dashboard')
                ->with('warning', 'Voce nao tem acesso ao painel administrativo. Acesse seu Painel do Membro.');
        }

        $user = auth()->user();
        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($this->canAccessScopedAdminRoute($request, $user)) {
            return $next($request);
        }

        Log::warning('Acesso administrativo negado', [
            'user_id' => $user->id,
            'route' => optional($request->route())->getName(),
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Acesso nao autorizado.'], 403);
        }

        return redirect()
            ->route('panel.dashboard')
            ->with('warning', 'Voce nao tem acesso ao painel administrativo. Acesse seu Painel do Membro.');
    }

    private function canAccessScopedAdminRoute(Request $request, $user): bool
    {
        $routeName = (string) optional($request->route())->getName();
        if ($routeName === '') {
            return false;
        }

        $allowedPrefixes = [];

        if (method_exists($user, 'canAccessInstructorArea') && $user->canAccessInstructorArea()) {
            $allowedPrefixes = array_merge($allowedPrefixes, [
                'panel.admin.courses.',
                'panel.admin.mentorships.',
                'panel.admin.events.',
                'panel.admin.certificates.',
            ]);
        }

        if (method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace()) {
            $allowedPrefixes[] = 'panel.admin.redemptions.';
        }

        if ($allowedPrefixes === []) {
            return false;
        }

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
