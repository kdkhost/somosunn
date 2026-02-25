<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerCouponController extends Controller
{
    public function store(Request $request, Partner $partner)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'code' => 'required|string|max:60',
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
            'active' => 'nullable|boolean',
        ]);
        $data['partner_id'] = $partner->id;
        $data['active'] = $request->boolean('active', true);
        PartnerCoupon::create($data);
        return back()->with('success', 'Cupom adicionado!');
    }

    public function update(Request $request, Partner $partner, PartnerCoupon $coupon)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'code' => 'required|string|max:60',
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
            'active' => 'nullable|boolean',
        ]);
        $data['active'] = $request->boolean('active');
        $coupon->update($data);
        return back()->with('success', 'Cupom atualizado!');
    }

    public function destroy(Partner $partner, PartnerCoupon $coupon)
    {
        $this->ensureAdmin();
        $coupon->delete();
        return back()->with('success', 'Cupom removido.');
    }

    protected function ensureAdmin(): void
    {
        if (!Auth::user()?->isAdmin()) {
            abort(403);
        }
    }
}
