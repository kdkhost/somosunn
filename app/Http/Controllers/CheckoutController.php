<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentGatewayException;
use App\Models\Course;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\CouponService;
use App\Services\OrderSettlementService;
use App\Services\Payment\MercadoPagoService;
use App\Services\SumUpService;
use App\Support\MarketplaceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function show(Course $course)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('error', 'Faca login para finalizar a compra do curso.');
        }

        $seller = $course->creator ?: User::find($course->user_id);
        if (!$seller || !$seller->canSellOnMarketplace()) {
            return redirect()
                ->route('courses.show', $course->slug ?: $course->id)
                ->with('error', 'Este criador nao esta habilitado para vender no marketplace.');
        }

        $effectiveTotal = round((float) ($course->effective_price ?? ($course->price ?? 0)), 2);
        if ($effectiveTotal <= 0) {
            $mpEnabled = false;
            $psEnabled = false;
            $preferredGateway = null;

            return view('checkout.index', compact('course', 'mpEnabled', 'psEnabled', 'preferredGateway'));
        }

        $gateways = \App\Models\GatewayAccount::resolveForSeller((int) $seller->id);
        $mpEnabled = $gateways['mpEnabled'];
        $psEnabled = $gateways['psEnabled'];
        $preferredGateway = $gateways['preferredGateway'];

        if (!$mpEnabled && !$psEnabled) {
            return redirect()
                ->route('courses.show', $course->slug ?: $course->id)
                ->with('error', 'Este curso nao esta disponivel para compra: o criador ainda nao configurou um metodo de pagamento.');
        }

        return view('checkout.index', compact('course', 'mpEnabled', 'psEnabled', 'preferredGateway'));
    }

    public function process(
        Request $request,
        Course $course,
        MercadoPagoService $mpService,
        SumUpService $suService,
        \App\Services\Payment\PagSeguroService $psService,
        CouponService $couponService,
        OrderSettlementService $orderSettlementService
    ) {
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('error', 'Faca login para finalizar a compra do curso.');
        }

        $seller = $course->creator ?: User::find($course->user_id);
        if (!$seller || !$seller->canSellOnMarketplace()) {
            return redirect()
                ->route('courses.show', $course->slug ?: $course->id)
                ->with('error', 'Este criador nao esta habilitado para vender no marketplace.');
        }

        $request->validate([
            'coupon_code' => 'nullable|string|max:40',
            'gateway_provider' => 'nullable|string|in:mercadopago,pagseguro,sumup',
        ]);

        $effectiveTotal = round((float) ($course->effective_price ?? ($course->price ?? 0)), 2);
        $gateways = [
            'mpEnabled' => false,
            'psEnabled' => false,
            'preferredGateway' => null,
            'mpPublicKey' => '',
            'psPublicKey' => '',
        ];
        $gatewayProvider = 'free';

        if ($effectiveTotal > 0) {
            $gateways = \App\Models\GatewayAccount::resolveForSeller((int) $seller->id);
            $gatewayProvider = $request->input('gateway_provider', $gateways['preferredGateway'] ?? 'mercadopago');

            if (
                ($gatewayProvider === 'mercadopago' && !$gateways['mpEnabled'])
                || ($gatewayProvider === 'pagseguro' && !$gateways['psEnabled'])
                || ($gatewayProvider === 'sumup' && !($gateways['suEnabled'] ?? false))
            ) {
                if ($gateways['mpEnabled']) {
                    $gatewayProvider = 'mercadopago';
                } elseif ($gateways['psEnabled']) {
                    $gatewayProvider = 'pagseguro';
                } elseif ($gateways['suEnabled'] ?? false) {
                    $gatewayProvider = 'sumup';
                } else {
                    return back()->with('error', 'Metodo de pagamento nao disponivel para este produto. O vendedor ainda nao configurou um gateway.');
                }
            }
        } else {
            $existingFreeOrder = $this->findExistingFreeOrder((int) Auth::id(), 'course', (int) $course->id);
            if ($existingFreeOrder) {
                return redirect()
                    ->route('courses.show', $course->slug ?: $course->id)
                    ->with('success', 'Curso liberado com sucesso.');
            }
        }

        $order = null;
        $couponCode = $couponService->normalizeCode($request->input('coupon_code'));

        try {
            DB::transaction(function () use ($course, $couponCode, $couponService, &$order, $gatewayProvider) {
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
                $suEnabled = $gateways['suEnabled'] ?? false;
                $suToken = $gateways['suToken'] ?? null;
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
                    'gateway' => $finalTotal <= 0 ? 'free' : $gatewayProvider,
                    'gateway_account_id' => null,
                    'metadata' => [
                        'sumup_token' => ($gatewayProvider === 'sumup') ? $suToken : null,
                        'context' => 'course',
                        'sale_type' => 'course',
                        'public_token' => Str::random(40),
                        'original_total_amount' => $originalTotal,
                        'regular_total_amount' => $regularUnitPrice,
                        'platform_fee_percent' => $platformFeePercent,
                        'is_free_checkout' => $finalTotal <= 0,
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
            $msg = collect($e->errors())->flatten()->first() ?? 'Nao foi possivel aplicar o cupom.';
            return back()->with('error', $msg)->withInput();
        }

        if ((float) ($order->total_amount ?? 0) <= 0) {
            try {
                $orderSettlementService->settleAsPaid($order, [
                    'transaction_id' => 'FREE-COURSE-' . $order->id . '-' . now()->format('YmdHis'),
                    'payment_method' => 'free_checkout',
                    'queue_invoice_email' => false,
                    'send_notifications' => false,
                    'gateway_data' => [
                        'source' => 'free_course_checkout',
                        'automatic' => true,
                    ],
                ]);
            } catch (\Throwable $e) {
                return back()->with('error', 'Erro ao liberar curso gratuito: ' . $e->getMessage());
            }

            return redirect()
                ->route('courses.show', $course->slug ?: $course->id)
                ->with('success', 'Curso liberado com sucesso.');
        }

        try {
            $order->load('items', 'user');

            if ($gatewayProvider === 'sumup') {
                $suToken = $order->metadata['sumup_token'] ?? Setting::get('sumup_access_token');
                $checkout = $suService->createCheckout($order, (string) $suToken);

                if (!$checkout || empty($checkout['id'])) {
                    return back()->with('error', 'Falha ao criar checkout na SumUp. Tente outro metodo.');
                }

                $order->update([
                    'transaction_id' => $checkout['id'],
                    'metadata' => array_merge($order->metadata ?? [], [
                        'sumup_checkout_id' => $checkout['id'],
                        'sumup_checkout_url' => "https://checkout.sumup.com/checkouts/{$checkout['id']}",
                    ]),
                ]);

                return redirect("https://checkout.sumup.com/checkouts/{$checkout['id']}");
            }

            if ($gatewayProvider === 'pagseguro') {
                return view('checkout.pagseguro_transparent', [
                    'order' => $order,
                    'publicKey' => $gateways['psPublicKey'] ?? config('payments.pagseguro.public_key'),
                    'pixAvailable' => $psService->isPixAvailable($order),
                ]);
            }

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
                'publicKey' => $gateways['mpPublicKey'] ?: config('payments.mercadopago.public_key'),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }
    }

    public function processPayment(Request $request, MercadoPagoService $mpService, \App\Services\Payment\PagSeguroService $psService)
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
        $gateway = $order->gateway;

        try {
            $paymentResult = [];

            if ($gateway === 'pagseguro') {
                if ($paymentMethod === 'pix') {
                    $paymentResult = $psService->createPixPayment($order);
                } else {
                    $paymentResult = $psService->createCreditCardPayment($order, [
                        'encrypted_card' => $formData['encrypted_card'] ?? null,
                        'installments' => $formData['installments'] ?? 1,
                    ]);
                }

                $status = $paymentResult['charges'][0]['status'] ?? $paymentResult['status'] ?? 'UNKNOWN';

                if (isset($paymentResult['qr_codes'])) {
                    $qrCode = $paymentResult['qr_codes'][0]['text'] ?? null;

                    return response()->json([
                        'success' => true,
                        'status' => 'pending',
                        'qr_code' => $qrCode,
                        'qr_code_base64' => null,
                        'redirect' => null
                    ]);
                }

                if ($status === 'PAID') {
                    app(PaymentWebhookController::class)->processPaidOrder($order, $paymentResult['id'] ?? null, $paymentResult);
                    return response()->json(['success' => true, 'redirect' => route('checkout.success', $order)]);
                }

                return response()->json(['error' => 'Pagamento nao aprovado: ' . $status], 400);
            }

            if ($paymentMethod === 'pix') {
                $paymentResult = $mpService->createPixPayment($order, [
                    'email' => $formData['payer']['email'] ?? Auth::user()->email,
                    'name' => Auth::user()->name,
                    'cpf' => $formData['payer']['identification']['number'] ?? Auth::user()->doc,
                ]);
            } else {
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
                app(PaymentWebhookController::class)->processPaidOrder($order, $paymentResult['id'] ?? null, $paymentResult);

                return response()->json([
                    'success' => true,
                    'redirect' => route('checkout.success', $order)
                ]);
            }

            if (in_array(($paymentResult['status'] ?? ''), ['pending', 'in_process'], true)) {
                $order->update(['transaction_id' => (string) ($paymentResult['id'] ?? null)]);

                return response()->json([
                    'success' => true,
                    'status' => $paymentResult['status'],
                    'qr_code' => $paymentResult['qr_code'] ?? null,
                    'qr_code_base64' => $paymentResult['qr_code_base64'] ?? null,
                    'redirect' => $paymentMethod === 'pix' ? null : route('checkout.pending', $order)
                ]);
            }

            $detail = $paymentResult['status_detail'] ?? $paymentResult['status'] ?? 'unknown';
            return response()->json(['error' => 'Pagamento nao aprovado: ' . $detail], 400);
        } catch (PaymentGatewayException $e) {
            if (
                $gateway === 'pagseguro'
                && $paymentMethod === 'pix'
                && $e->errorCode() === 'pagseguro_pix_whitelist_required'
            ) {
                $psService->markPixAsUnavailable($order);
            }

            Log::warning('Falha controlada no checkout', [
                'message' => $e->getMessage(),
                'error_code' => $e->errorCode(),
                'order_id' => $order->id ?? null,
                'user_id' => Auth::id(),
                'gateway' => $gateway ?? null,
                'context' => $e->context(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'error_code' => $e->errorCode(),
                'pix_disabled' => $e->errorCode() === 'pagseguro_pix_whitelist_required',
            ], $e->httpStatus());
        } catch (\Throwable $e) {
            Log::error('Erro Checkout', [
                'message' => $e->getMessage(),
                'order_id' => $order->id ?? null,
                'user_id' => Auth::id(),
                'gateway' => $gateway ?? null,
            ]);

            $message = trim((string) $e->getMessage());
            if ($message !== '' && function_exists('iconv')) {
                $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $message);
                if (is_string($converted) && $converted !== '') {
                    $message = $converted;
                }
            }

            $errorCode = null;
            if (Str::contains(Str::lower($message), 'access_denied') && Str::contains(Str::lower($message), 'whitelist')) {
                if ($gateway === 'pagseguro' && $paymentMethod === 'pix') {
                    $psService->markPixAsUnavailable($order);
                }

                $message = 'O Pix do PagSeguro nao esta liberado para esta conta no momento. Solicite a liberacao de whitelist no PagSeguro ou use outro metodo de pagamento disponivel.';
                $errorCode = 'pagseguro_pix_whitelist_required';
            }

            return response()->json([
                'error' => $message !== '' ? $message : 'Nao foi possivel processar o pagamento no momento.',
                'error_code' => $errorCode,
                'pix_disabled' => $errorCode === 'pagseguro_pix_whitelist_required',
            ], $errorCode ? 422 : 500);
        }
    }

    private function findExistingFreeOrder(int $userId, string $itemType, int $itemId): ?Order
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->where('total_amount', '<=', 0)
            ->whereHas('items', function ($query) use ($itemType, $itemId) {
                $query->where('item_type', $itemType)
                    ->where('item_id', $itemId);
            })
            ->latest('id')
            ->first();
    }
}
