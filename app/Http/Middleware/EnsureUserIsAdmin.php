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
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acesso não autorizado.'], 403);
            }

            return redirect()
                ->route('panel.dashboard')
                ->with('warning', 'Você não tem acesso ao painel administrativo. Acesse seu Painel do Membro.');
        }

        return $next($request);
    }
}

