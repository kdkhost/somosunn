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

        if (!$user->canSellOnMarketplace()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'VocÃª nÃ£o possui permissÃ£o de vendas no marketplace.'], 403);
            }

            return redirect()
                ->route('panel.dashboard')
                ->with('error', 'VocÃª nÃ£o possui permissÃ£o para acessar o painel do Marketplace.');
        }

        return $next($request);
    }
}
