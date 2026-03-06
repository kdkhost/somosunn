<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasActivePlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // 1. If not logged in, let other middlewares handle it (usually redirected to login)
        if (!$user) {
            return $next($request);
        }

        \Log::info('CheckPlan: user ' . $user->id . ' accessing ' . $request->path());

        // 2. Admin Bypass
        if ($user->isAdmin()) {
            return $next($request);
        }

        // 3. Whitelist Routes
        $whitelist = [
            'login',
            'register',
            'logout',
            'premium',
            'panel.profile.edit',
            'panel.profile.update',
            'admin.courses.available',
            'checkout.show',
            'checkout.process',
            'checkout.success',
            'checkout.failure',
            'checkout.pending',
            'verification.notice',
            'verification.verify',
            'verification.resend',
            'admin.impersonate.stop',
            'install.*'
        ];

        if ($request->routeIs($whitelist)) {
            return $next($request);
        }

        // 4. Check for Active Plan
        if (!$user->activePlan()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Selecione um plano para continuar.'], 402);
            }

            return redirect()->route('premium')
                ->with('warning', 'Escolha um plano para liberar o acesso total à comunidade SOMOS UNN.');
        }

        return $next($request);
    }
}
