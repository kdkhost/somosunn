<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstructorAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Nao autenticado.'], 401);
            }

            return redirect()->route('login');
        }

        if (session()->has('impersonator_id') && session()->get('impersonator_is_admin')) {
            return $next($request);
        }

        if (method_exists($user, 'canAccessInstructorArea') && $user->canAccessInstructorArea()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Acesso nao autorizado para a area do instrutor.'], 403);
        }

        return redirect()
            ->route('panel.dashboard')
            ->with('warning', 'Seu perfil nao possui permissao para a area do instrutor.');
    }
}
