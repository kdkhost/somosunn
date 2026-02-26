<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
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

        if ($this->canAccessInstructorScopedAdminRoute($request, $user)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Acesso nao autorizado.'], 403);
        }

        return redirect()
            ->route('panel.dashboard')
            ->with('warning', 'Voce nao tem acesso ao painel administrativo. Acesse seu Painel do Membro.');
    }

    private function canAccessInstructorScopedAdminRoute(Request $request, $user): bool
    {
        if (!method_exists($user, 'canAccessInstructorArea') || !$user->canAccessInstructorArea()) {
            return false;
        }

        if (session()->has('impersonator_id') && session()->get('impersonator_is_admin')) {
            return true;
        }

        $routeName = (string) optional($request->route())->getName();
        if ($routeName === '') {
            return false;
        }

        $allowedPrefixes = [
            'panel.admin.courses.',
            'panel.admin.mentorships.',
            'panel.admin.events.',
            'panel.admin.certificates.',
        ];

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
