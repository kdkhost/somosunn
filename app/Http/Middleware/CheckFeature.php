<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $feature
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $feature)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->canAccessFeature($feature)) {
            // Se for AJAX, retorna JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Funcionalidade não incluída no seu plano.'], 403);
            }

            // Se for normal, redireciona com mensagem ou aborta
            // Redirecionar para 'upgrade' ou 'planos' seria ideal
            return redirect()->route('portal')->with('error', 'Esta funcionalidade não está disponível no seu plano atual. Faça um upgrade!');
        }

        return $next($request);
    }
}
