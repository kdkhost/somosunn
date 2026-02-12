<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckMarketplaceSeller
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        // Impersonação (admin/superadmin): liberar acesso ao painel do vendedor
        if (session()->has('impersonator_id') && session()->get('impersonator_is_admin')) {
            return $next($request);
        }

        if (!$user->canSellOnMarketplace()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Você não possui permissão de vendas no marketplace.'], 403);
            }

            return redirect()
                ->route('panel.dashboard')
                ->with('error', 'Você não possui permissão para acessar o painel do Marketplace.');
        }

        return $next($request);
    }
}
