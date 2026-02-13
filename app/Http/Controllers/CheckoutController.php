<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use App\Services\CouponService;
use App\Services\Payment\MercadoPagoService;
use App\Support\MarketplaceFee;
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
            return redirect()->guest(route('login'))->with('error', 'Faça login para finalizar a compra do curso.');
        }

        $seller = $course->creator ?: User::find($course->user_id);
        if (!$seller || !$seller->canSellOnMarketplace()) {
            return redirect()
                ->route('courses.show', $course->slug ?: $course->id)
                ->with('error', 'Este criador não está habilitado para vender no marketplace.');
        }

        $mpAccessToken = trim((string) config('payments.mercadopago.access_token'));
        $mpPublicKey = trim((string) config('payments.mercadopago.public_key'));
        $paymentsConfigured = $mpAccessToken !== '' && $mpPublicKey !== '';

        if (!$paymentsConfigured) {
            return redirect()
                ->route('courses.show', $course->slug ?: $course->id)
                ->with('error', 'Pagamento indisponível: o MercadoPago ainda não foi configurado na plataforma.');
        }

        return view('checkout.index', compact('course'));
    }

    public function process(Request $request, Course $course, MercadoPagoService $mpService, CouponService $couponService)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('error', 'Faça login para finalizar a compra do curso.');
        }

        $seller = $course->creator ?: User::find($course->user_id);
        if (!$seller || !$seller->canSellOnMarketplace()) {
            return redirect()
                ->route('courses.show', $course->slug ?: $course->id)
                ->with('error', 'Este criador não está habilitado para vender no marketplace.');
        }

        $mpAccessToken = trim((string) config('payments.mercadopago.access_token'));
        $mpPublicKey = trim((string) config('payments.mercadopago.public_key'));
        $paymentsConfigured = $mpAccessToken !== '' && $mpPublicKey !== '';

        if (!$paymentsConfigured) {
            return redirect()
                ->route('courses.show', $course->slug ?: $course->id)
                ->with('error', 'Pagamento indisponível: o MercadoPago ainda não foi configurado na plataforma.');
        }

        $request->validate([
            'coupon_code' => 'nullable|string|max:40',
        ]);

        $order = null;
        $couponCode = $couponService->normalizeCode($request->input('coupon_code'));

        try {
            DB::transaction(function () use ($course, $couponCode, $couponService, &$order) {
                $regularUnitPrice = round((float) ($course->price ?? 0), 2);
                $effectiveUnitPrice = round((float) ($course->effective_price ?? $regularUnitPrice), 2);
                $originalTotal = $effectiveUnitPrice;

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
                $platformFeePercent = MarketplaceFee::percent();
                $platformFeeAmount = MarketplaceFee::amount($finalTotal);

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'seller_id' => $course->user_id,
                    'status' => 'pending',
                    'total_amount' => $finalTotal,
                    'fee_amount' => 0,
                    'platform_fee_amount' => $platformFeeAmount,
                    'currency' => 'BRL',
                    'gateway' => 'mercadopago',
                    'gateway_account_id' => null,
                    'metadata' => [
                        'context' => 'course',
                        'sale_type' => 'course',
                        'public_token' => Str::random(40),
                        'original_total_amount' => $originalTotal,
                        'regular_total_amount' => $regularUnitPrice,
                        'platform_fee_percent' => $platformFeePercent,
                    ],
                ]);

                $order->items()->create([
                    'item_type' => 'course',
                    'item_id' => $course->id,
                    'title' => $course->title,
                    'price' => $finalTotal,
                    'quantity' => 1,
                    'data' => [
                        'original_unit_price' => $effectiveUnitPrice,
                        'regular_unit_price' => $regularUnitPrice,
                        'flash_sale_price' => $course->flash_sale_price !== null ? (float) $course->flash_sale_price : null,
                        'flash_sale_ends_at' => $course->flash_sale_ends_at ? $course->flash_sale_ends_at->toIso8601String() : null,
                        'discount_amount' => $discountAmount,
                    ],
                ]);
                // Log de comissão
                \App\Models\Commission::create([
                    'order_id' => $order->id,
                    'seller_id' => $course->user_id,
                    'total_amount' => $finalTotal,
                    'platform_fee_amount' => $platformFeeAmount,
                    'seller_amount' => max(0, $finalTotal - $platformFeeAmount),
                    'currency' => 'BRL',
                    'gateway' => 'mercadopago',
                    'metadata' => [
                        'course_id' => $course->id,
                        'platform_fee_percent' => $platformFeePercent,
                        'buyer_id' => Auth::id(),
                        'coupon_id' => $coupon->id ?? null,
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
            $preference = $mpService->createPreference($order, [
                'statement_descriptor' => 'UNN CURSOS',
            ]);

            $order->update([
                'metadata' => array_merge($order->metadata ?? [], [
                    'mercadopago_preference_id' => $preference['id'] ?? null,
                    'mercadopago_init_point' => $preference['init_point'] ?? null,
                    'mercadopago_sandbox_init_point' => $preference['sandbox_init_point'] ?? null,
                ]),
            ]);

            return view('checkout.transparent', [
                'order' => $order,
                'preferenceId' => $preference['id'] ?? '',
                'publicKey' => $mpPublicKey,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }

        return back()->with('error', 'Gateway não suportado.');
    }
}
