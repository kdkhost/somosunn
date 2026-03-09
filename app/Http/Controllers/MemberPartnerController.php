<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberPartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->abortUnlessPartnerFeatureEnabled();

        $partner = Partner::where('user_id', Auth::id())->first();
        if (!$partner) {
            abort(403, 'Voce nao tem um perfil de parceiro vinculado a sua conta.');
        }

        $coupons = $partner->coupons()->latest()->get();

        return view('member.partner.index', compact('partner', 'coupons'));
    }

    public function store(Request $request)
    {
        $this->abortUnlessPartnerFeatureEnabled();

        $partner = Partner::where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'code' => 'required|string|max:60',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'min_purchase' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date|after:today',
            'active' => 'boolean',
        ]);

        $validated['partner_id'] = $partner->id;
        $validated['active'] = $request->boolean('active', true);

        PartnerCoupon::create($validated);

        return response()->json(['success' => true, 'message' => 'Cupom criado com sucesso!']);
    }

    public function update(Request $request, PartnerCoupon $coupon)
    {
        $this->abortUnlessPartnerFeatureEnabled();

        $partner = Partner::where('user_id', Auth::id())->firstOrFail();
        abort_unless($coupon->partner_id === $partner->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'code' => 'required|string|max:60',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'min_purchase' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active', true);
        $coupon->update($validated);

        return response()->json(['success' => true, 'message' => 'Cupom atualizado!']);
    }

    public function destroy(PartnerCoupon $coupon)
    {
        $this->abortUnlessPartnerFeatureEnabled();

        $partner = Partner::where('user_id', Auth::id())->firstOrFail();
        abort_unless($coupon->partner_id === $partner->id, 403);

        $coupon->delete();

        return response()->json(['success' => true, 'message' => 'Cupom removido.']);
    }

    private function abortUnlessPartnerFeatureEnabled(): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->isAdmin()) {
            return;
        }

        abort_unless(
            method_exists($user, 'canAccessFeature') && $user->canAccessFeature('benefits.club.partner'),
            403,
            'Seu plano atual nao libera perfil parceiro e gestao de cupons.'
        );
    }
}
