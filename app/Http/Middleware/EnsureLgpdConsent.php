<?php

namespace App\Http\Middleware;

use App\Services\LegalConsentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLgpdConsent
{
    public function __construct(
        private readonly LegalConsentService $legalConsent,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $this->shouldBypass($request) || $this->legalConsent->hasAcceptedCurrentVersion($user)) {
            return $next($request);
        }

        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $message = 'Você precisa aceitar os termos de LGPD antes de continuar.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'requires_lgpd_consent' => true,
            ], 423);
        }

        return redirect()
            ->back()
            ->with('error', $message);
    }

    private function shouldBypass(Request $request): bool
    {
        return $request->routeIs('lgpd.accept')
            || $request->routeIs('logout')
            || $request->routeIs('verification.*')
            || $request->routeIs('admin.*')
            || $request->routeIs('panel.admin.*')
            || $request->is('admin')
            || $request->is('admin/*');
    }
}
