<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicPartnerController extends Controller
{
    /** Lista pública de parceiros (logos, sem expor cupons) */
    public function index()
    {
        $partners = Partner::active()->withCount('activeCoupons')->get();
        return view('partners.index', compact('partners'));
    }

    /** Página de cupons de um parceiro – exclusiva para membros adimplentes */
    public function show(Partner $partner)
    {
        abort_unless($partner->active, 404);

        $user = Auth::user();

        // Verificar se o usuário está logado e tem plano ativo
        $hasActivePlan = false;
        if ($user) {
            // Admin sempre tem acesso
            if ($user->isAdmin()) {
                $hasActivePlan = true;
            } else {
                // Verifica plano via activePlan() ou subscription ativa
                try {
                    $plan = $user->activePlan();
                    $hasActivePlan = $plan !== null;
                } catch (\Throwable $e) {
                    $hasActivePlan = false;
                }
            }
        }

        $coupons = $hasActivePlan
            ? $partner->activeCoupons()->get()
            : collect();

        return view('partners.show', compact('partner', 'coupons', 'hasActivePlan', 'user'));
    }
}
