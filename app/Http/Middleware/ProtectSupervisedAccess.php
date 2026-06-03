<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProtectSupervisedAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('impersonator_id')) {
            return $next($request);
        }

        $expectedUserId = (int) $request->session()->get('impersonated_user_id');
        $authenticatedUserId = (int) Auth::id();

        if ($expectedUserId <= 0 || $authenticatedUserId !== $expectedUserId) {
            Log::warning('Sessao de acesso supervisionado inconsistente encerrada', [
                'impersonator_id' => $request->session()->get('impersonator_id'),
                'expected_user_id' => $expectedUserId,
                'authenticated_user_id' => $authenticatedUserId,
                'ip' => $request->ip(),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'A sessao supervisionada estava inconsistente e foi encerrada por seguranca.');
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
