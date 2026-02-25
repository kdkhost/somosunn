<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Área do membro que é vinculado a um Parceiro (user_id na tabela partners).
 * Permite que o próprio parceiro gerencie seus cupons exclusivos.
 */
class MemberPartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Painel do parceiro — lista seus cupons */
    public function index()
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (!$partner) {
            abort(403, 'Você não tem um perfil de parceiro vinculado à sua conta.');
        }

        $coupons = $partner->coupons()->latest()->get();

        return view('member.partner.index', compact('partner', 'coupons'));
    }

    /** Salva novo cupom */
    public function store(Request $request)
    {
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

    /** Atualiza cupom */
    public function update(Request $request, PartnerCoupon $coupon)
    {
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

    /** Remove cupom */
    public function destroy(PartnerCoupon $coupon)
    {
        $partner = Partner::where('user_id', Auth::id())->firstOrFail();
        abort_unless($coupon->partner_id === $partner->id, 403);

        $coupon->delete();

        return response()->json(['success' => true, 'message' => 'Cupom removido.']);
    }
}
