<?php

namespace App\Http\Middleware;

use App\Services\LegalConsentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePurchaseEligibility
{
    public function __construct(
        private readonly LegalConsentService $legalConsent,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Entre na sua conta para continuar a compra.',
                    'requires_authentication' => true,
                    'login_url' => route('login'),
                ], 401);
            }

            return redirect()->guest(route('login'))
                ->with('error', 'Entre na sua conta para continuar a compra.');
        }

        if (!$user->hasVerifiedEmail()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Valide seu e-mail antes de realizar uma compra.',
                    'requires_email_verification' => true,
                    'verification_url' => route('verification.notice'),
                ], 403);
            }

            return redirect()->route('verification.notice')
                ->with('error', 'Valide seu e-mail antes de realizar uma compra.');
        }

        if (!$this->legalConsent->hasAcceptedCurrentVersion($user)) {
            $message = 'Aceite os Termos de Uso, a Política de Privacidade e o Consentimento LGPD antes de realizar uma compra.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                    'requires_legal_consent' => true,
                ], 423);
            }

            return redirect()->back()->with('error', $message);
        }

        return $next($request);
    }
}
