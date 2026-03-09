<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Support\Facades\Auth;

class PublicPartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::active()->withCount('activeCoupons')->get();

        return view('partners.index', compact('partners'));
    }

    public function show(Partner $partner)
    {
        abort_unless($partner->active, 404);

        $user = Auth::user();
        $hasBenefitAccess = false;

        if ($user) {
            if ($user->isAdmin()) {
                $hasBenefitAccess = true;
            } else {
                try {
                    $hasBenefitAccess = (bool) $user->canAccessFeature('benefits.club.access');
                } catch (\Throwable $e) {
                    $hasBenefitAccess = false;
                }
            }
        }

        $coupons = $hasBenefitAccess
            ? $partner->activeCoupons()->get()
            : collect();

        return view('partners.show', [
            'partner' => $partner,
            'coupons' => $coupons,
            'hasActivePlan' => $hasBenefitAccess,
            'user' => $user,
        ]);
    }
}
