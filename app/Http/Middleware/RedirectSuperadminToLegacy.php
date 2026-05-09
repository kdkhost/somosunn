<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectSuperadminToLegacy
{
    /**
     * Superadmin só acessa o painel novo (/painel) via login supervisionado.
     * Sem impersonação ativa, redireciona para o painel legado (/admin).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Se está em impersonação, permite acesso normalmente
        if (session()->has('impersonator_id')) {
            return $next($request);
        }

        // Superadmin sem impersonação → redireciona para painel legado
        if ($user->isSuperAdmin()) {
            if ($request->expectsJson() || $request->ajax()) {
                return $next($request); // APIs continuam funcionando
            }

            return redirect()
                ->route('admin.dashboard')
                ->with('toastr_info', 'Use o acesso supervisionado para visualizar o painel de membros.');
        }

        return $next($request);
    }
}
