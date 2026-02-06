<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Order;
use App\Models\GatewayAccount;
use App\Services\Payment\MercadoPagoService;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function show(Course $course)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Faça login para finalizar a compra do curso.');
        }

        // Check if seller has gateway configured
        $gateway = GatewayAccount::where('user_id', $course->user_id)
            ->where('enabled', true)
            ->first();

        if (!$gateway) {
            return back()->with('error', 'Este criador ainda não configurou o recebimento de pagamentos.');
        }

        return view('checkout.index', compact('course', 'gateway'));
    }

    public function process(Request $request, Course $course, MercadoPagoService $mpService, CouponService $couponService)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Faça login para finalizar a compra do curso.');
        }

        $gateway = GatewayAccount::where('user_id', $course->user_id)
            ->where('enabled', true)
            ->firstOrFail();

        $request->validate([
            'coupon_code' => 'nullable|string|max:40',
        ]);

        $order = null;
        $couponCode = $couponService->normalizeCode($request->input('coupon_code'));

        try {
            DB::transaction(function () use ($course, $gateway, $couponCode, $couponService, &$order) {
                $originalTotal = round((float) $course->price, 2);

                $discountAmount = 0.0;
                $coupon = null;

                if ($couponCode !== '') {
                    $result = $couponService->validateAndCalculateLocked(
                        $couponCode,
                        CouponService::CONTEXT_COURSE,
                        (int) $course->id,
                        (int) Auth::id(),
                        (float) $originalTotal
                    );
                    $coupon = $result['coupon'];
                    $discountAmount = (float) $result['discount_amount'];
                }

                $finalTotal = max(0, round($originalTotal - $discountAmount, 2));

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'seller_id' => $course->user_id,
                    'status' => 'pending',
                    'total_amount' => $finalTotal,
                    'fee_amount' => 0,
                    'platform_fee_amount' => 0,
                    'currency' => 'BRL',
                    'gateway' => $gateway->provider,
                    'gateway_account_id' => $gateway->id,
                    'metadata' => [
                        'context' => 'course',
                        'public_token' => Str::random(40),
                        'original_total_amount' => $originalTotal,
                    ],
                ]);

                $order->items()->create([
                    'item_type' => 'course',
                    'item_id' => $course->id,
                    'title' => $course->title,
                    'price' => $finalTotal,
                    'quantity' => 1,
                    'data' => [
                        'original_unit_price' => (float) $course->price,
                        'discount_amount' => $discountAmount,
                    ],
                ]);

                if ($coupon && $discountAmount > 0) {
                    $order->update([
                        'metadata' => array_merge($order->metadata ?? [], [
                            'coupon' => [
                                'id' => $coupon->id,
                                'code' => $coupon->code,
                                'discount_type' => $coupon->discount_type,
                                'discount_value' => (float) $coupon->discount_value,
                                'discount_amount' => $discountAmount,
                            ],
                        ]),
                    ]);

                    $couponService->reserveRedemption($coupon, (int) Auth::id(), (int) $order->id, $discountAmount);
                }
            });
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?? 'Não foi possível aplicar o cupom.';
            return back()->with('error', $msg)->withInput();
        }

        try {
            $order->load('items', 'user');
            if ($gateway->provider === 'mercadopago') {
                $preference = $mpService->createPreference($order, $gateway);
                
                // For transparent checkout, we return the view with the preference ID
                return view('checkout.transparent', [
                    'order' => $order,
                    'preferenceId' => $preference['id'],
                    'publicKey' => $gateway->public_key
                ]);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }

        return back()->with('error', 'Gateway não suportado.');
    }
}
