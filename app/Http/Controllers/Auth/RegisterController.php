<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\User;
use App\Rules\ValidEmailAddress;
use App\Services\AffiliateTrackingService;
use App\Services\LegalConsentService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function store(Request $request, AffiliateTrackingService $tracking, LegalConsentService $legalConsent)
    {
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'name' => 'required|string|max:80',
            'email' => ['required', new ValidEmailAddress(), 'unique:users,email'],
            'password' => 'required|min:8|confirmed',
            'doc' => 'nullable|string',
            'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
            'phone' => 'nullable|string',
            'cep' => 'nullable|string',
            'address' => 'nullable|string',
            'ref' => 'nullable|string|max:20',
            'terms' => ['required', 'accepted'],
        ], [
            'terms.accepted' => 'Você precisa aceitar os Termos de Uso, a Política de Privacidade e o Consentimento LGPD.',
        ]);

        // Resolve referidor pelo código de indicação
        $refCode = trim((string) ($data['ref'] ?? $request->query('ref', $tracking->currentReferralCode($request))));
        $referrer = $tracking->resolveReferrerByCode($refCode);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'doc' => $data['doc'] ?? null,
            'gender' => $data['gender'] ?? null,
            'phone' => $data['phone'] ?? null,
            'cep' => $data['cep'] ?? null,
            'address' => $data['address'] ?? null,
            'referred_by' => $referrer?->id,
        ]);

        $tracking->attachRegisteredUser($request, $user, $refCode);
        $legalConsent->recordAcceptance($user, $request);

        // Vincula plano gratuito para liberar o Painel do Membro imediatamente
        try {
            $defaultPlan = Plan::getFreePlan();
            if ($defaultPlan) {
                $user->plan_id = (int) $defaultPlan->id;
                $user->plan_expires_at = null;
                $user->save();
            }
        } catch (\Throwable $e) {
            // ignore (fallback: usuário escolhe plano no /premium)
        }

        // Award signup points (if rules exist)
        // Nota: pontos de referral para o indicador são dados SOMENTE após pagamento de plano
        //       (ver PaymentWebhookController::activatePlanForOrder)
        try {
            $ps = new \App\Services\PointsService();
            $ps->award($user, 'signup');
        } catch (\Throwable $e) {
            \Log::error('Points award error: ' . $e->getMessage());
        }

        event(new Registered($user));

        Auth::login($user);
        return redirect()->route('verification.notice')
            ->with('success', 'Conta criada com sucesso. Valide seu e-mail para liberar compras na plataforma.');
    }
}
