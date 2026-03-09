<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\User;
use App\Services\AffiliateTrackingService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function store(Request $request, AffiliateTrackingService $tracking)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'doc' => 'nullable|string',
            'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
            'phone' => 'nullable|string',
            'cep' => 'nullable|string',
            'address' => 'nullable|string',
            'ref' => 'nullable|string|max:20',
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

        Auth::login($user);
        return redirect()->route('planos')
            ->with('success', 'Conta criada com sucesso! Escolha um plano para aproveitar ao máximo a plataforma.');
    }
}
