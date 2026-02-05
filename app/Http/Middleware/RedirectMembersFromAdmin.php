<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectMembersFromAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não está autenticado, deixa passar (middleware auth vai bloquear)
        if (!auth()->check()) {
            return $next($request);
        }

        // Se é admin, deixa passar
        if (auth()->user()->isAdmin()) {
            return $next($request);
        }

        // Se é membro tentando acessar área admin, redireciona para portal
        return redirect()->route('portal')->with('info', 'Você não tem permissão para acessar essa área.');
    }
}
