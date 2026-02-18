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

            if ($course->is_recurring && !empty($course->mp_plan_id)) {
                $subscription = $mpService->subscribeUser($course->mp_plan_id, [
                    'email' => Auth::user()->email,
                    'reason' => 'Assinatura: ' . $course->title,
                    'external_reference' => (string) $order->id,
                    'back_url' => route('panel.dashboard'),
                ]);

                $order->update([
                    'metadata' => array_merge($order->metadata ?? [], [
                        'mercadopago_preapproval_id' => $subscription['id'] ?? null,
                        'mercadopago_init_point' => $subscription['init_point'] ?? null,
                    ]),
                ]);

                if (!empty($subscription['init_point'])) {
                    return redirect($subscription['init_point']);
                }
            }

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
    }

    public function processPayment(Request $request, MercadoPagoService $mpService)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'formData' => 'required|array',
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        if ($order->status === 'paid') {
            return response()->json(['success' => true, 'redirect' => route('checkout.success', $order)]);
        }

        $formData = $request->formData;
        $paymentMethod = $formData['payment_method_id'] ?? '';

        try {
            if ($paymentMethod === 'pix') {
                $paymentResult = $mpService->createPixPayment($order, [
                    'email' => $formData['payer']['email'] ?? Auth::user()->email,
                    'name' => Auth::user()->name,
                    'cpf' => $formData['payer']['identification']['number'] ?? Auth::user()->doc,
                ]);
            } else {
                // Cartão de crédito
                $paymentResult = $mpService->createCreditCardPayment($order, [
                    'token' => $formData['token'],
                    'installments' => $formData['installments'],
                    'payment_method_id' => $formData['payment_method_id'],
                    'issuer_id' => $formData['issuer_id'],
                    'email' => $formData['payer']['email'] ?? Auth::user()->email,
                    'cpf' => $formData['payer']['identification']['number'] ?? Auth::user()->doc,
                ]);
            }

            if (($paymentResult['status'] ?? '') === 'approved') {
                $order->update([
                    'status' => 'paid',
                    'transaction_id' => (string) ($paymentResult['id'] ?? null),
                ]);

                return response()->json([
                    'success' => true,
                    'redirect' => route('checkout.success', $order)
                ]);
            } elseif (in_array(($paymentResult['status'] ?? ''), ['pending', 'in_process'], true)) {
                $order->update(['transaction_id' => (string) ($paymentResult['id'] ?? null)]);

                return response()->json([
                    'success' => true,
                    'status' => $paymentResult['status'],
                    'qr_code' => $paymentResult['qr_code'] ?? null,
                    'qr_code_base64' => $paymentResult['qr_code_base64'] ?? null,
                    'redirect' => $paymentMethod === 'pix' ? null : route('checkout.pending', $order)
                ]);
            } else {
                $detail = $paymentResult['status_detail'] ?? $paymentResult['status'] ?? 'unknown';
                return response()->json(['error' => 'Pagamento não aprovado: ' . $detail], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Erro Transparente MP: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao processar: ' . $e->getMessage()], 500);
        }
    }
}
