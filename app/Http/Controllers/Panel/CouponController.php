<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $coupons = Coupon::where('user_id', $user->id)->latest()->get();
        return view('panel.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $coupon = new Coupon();
        return view('panel.coupons.form', compact('coupon'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'discount_percent' => 'required|integer|min:1|max:100',
            'expires_at' => 'nullable|date',
        ], [], [
            'code' => 'Código',
            'discount_percent' => 'Desconto',
            'expires_at' => 'Validade',
        ]);
        $data['user_id'] = Auth::id();
        Coupon::create($data);
        return redirect()->route('panel.coupons.index')->with('success', 'Cupom criado com sucesso!');
    }

    public function edit(Coupon $coupon)
    {
        $this->authorize('update', $coupon);
        return view('panel.coupons.form', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $this->authorize('update', $coupon);
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'discount_percent' => 'required|integer|min:1|max:100',
            'expires_at' => 'nullable|date',
        ], [], [
            'code' => 'Código',
            'discount_percent' => 'Desconto',
            'expires_at' => 'Validade',
        ]);
        $coupon->update($data);
        return redirect()->route('panel.coupons.index')->with('success', 'Cupom atualizado com sucesso!');
    }

    public function destroy(Coupon $coupon)
    {
        $this->authorize('delete', $coupon);
        $coupon->delete();
        return redirect()->route('panel.coupons.index')->with('success', 'Cupom excluído com sucesso!');
    }
}
