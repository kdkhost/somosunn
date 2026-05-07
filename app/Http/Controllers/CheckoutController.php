<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentGatewayException;
use App\Models\Course;
use App\Models\GatewayAccount;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\CouponService;
use App\Services\OrderSettlementService;
use App\Services\Payment\MercadoPagoService;
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
            $preferredGateway = null;

            return view('checkout.index', compact('course', 'mpEnabled', 'preferredGateway'));
        }

        $gateways = \App\Models\GatewayAccount::resolveForSeller((int) $seller->id);
        $mpEnabled = $gateways['mpEnabled'];
        $preferredGateway = 'mercadopago';

        if (!$mpEnabled) {
            return redirect()
                ->route('courses.show', $course->slug ?: $course->id)
                ->with('error', 'Este curso nao esta disponivel para compra: o criador ainda nao configurou o MercadoPago.');
        }

        return view('checkout.index', compact('course', 'mpEnabled', 'preferredGateway'));
    }

    public function process(
        Request $request,
        Course $course,
        MercadoPagoService $mpService,
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
            'gateway_provider' => 'nullable|string|in:mercadopago',
        ]);

        $effectiveTotal = round((float) ($course->effective_price ?? ($course->price ?? 0)), 2);
        $gateways = [
            'mpEnabled' => false,
            'preferredGateway' => null,
            'mpPublicKey' => '',
        ];
        $gatewayProvider = 'free';

        if ($effectiveTotal > 0) {
            $gateways = \App\Models\GatewayAccount::resolveForSeller((int) $seller->id);
            $gatewayProvider = 'mercadopago';

            if (!$gateways['mpEnabled']) {
                return back()->with('error', 'MercadoPago nao configurado pelo vendedor.');
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
                    'gateway' => $finalTotal <= 0 ? 'free' : 'mercadopago',
                    'gateway_account_id' => null,
                    'metadata' => [
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

            // Obter conta do vendedor ou global
            $seller = User::find($order->seller_id);
            $platformOwnerId = \App\Models\Setting::get('platform_owner_id', 2);
            $isPlatformOwner = (int) $order->seller_id === (int) $platformOwnerId;
            
            $sellerMpAccount = null;
            if (!$isPlatformOwner) {
                $sellerMpAccount = GatewayAccount::resolveForSeller($seller->id);
            }

            $gateways = [
                'mp' => Setting::get('mp_active') && ($isPlatformOwner || (is_array($sellerMpAccount) && ($sellerMpAccount['mpEnabled'] ?? false))),
                'mpPublicKey' => (!$isPlatformOwner && is_array($sellerMpAccount)) ? ($sellerMpAccount['mpPublicKey'] ?? null) : null,
            ];

            return view('checkout.transparent', [
                'order' => $order,
                'preferenceId' => $preference['id'] ?? '',
                'publicKey' => $gateways['mpPublicKey'] ?: config('payments.mercadopago.public_key') ?: \App\Models\Setting::get('mp_public_key'),
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
        $gateway = $order->gateway;

        try {
            $paymentResult = [];

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
                    'expires_at' => $paymentResult['expires_at'] ?? null,
                    'redirect' => $paymentMethod === 'pix' ? null : route('checkout.pending', $order)
                ]);
            }

            $detail = $paymentResult['status_detail'] ?? $paymentResult['status'] ?? 'unknown';
            return response()->json(['error' => 'Pagamento nao aprovado: ' . $detail], 400);
        } catch (PaymentGatewayException $e) {

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
                'pix_disabled' => false,
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

            return response()->json([
                'error' => $message !== '' ? $message : 'Nao foi possivel processar o pagamento no momento.',
                'error_code' => null,
                'pix_disabled' => false,
            ], 500);
        }
    }

    public function success(Order $order, Request $request)
    {
        $this->abortIfOrderNotAccessible($order, $request);
        return view('checkout.success', compact('order'));
    }

    public function pending(Order $order, Request $request)
    {
        $this->abortIfOrderNotAccessible($order, $request);
        return view('checkout.pending', compact('order'));
    }

    public function failure(Order $order, Request $request)
    {
        $this->abortIfOrderNotAccessible($order, $request);
        return view('checkout.failure', compact('order'));
    }

    private function abortIfOrderNotAccessible(Order $order, Request $request): void
    {
        $token = (string) $request->query('token');
        $storedToken = (string) data_get($order->metadata, 'public_token');

        $canAccess = Auth::check() && Auth::id() === $order->user_id;
        if (!$canAccess && $token !== '' && $storedToken !== '') {
            $canAccess = hash_equals($storedToken, $token);
        }

        abort_unless($canAccess, 403);
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

    /**
     * Gera PIX via SumUp para o pedido informado.
     */
    public function sumupPix(Request $request, \App\Services\Payment\SumUpService $sumUpService)
    {
        $request->validate(['order_id' => 'required|integer']);

        $order = Order::with('items', 'user')->findOrFail($request->order_id);

        // Verificar acesso
        abort_unless(Auth::check() && Auth::id() === $order->user_id, 403);

        try {
            $result = $sumUpService->processPixCheckout($order);

            Log::debug('SumUp PIX result', [
                'order_id'    => $order->id,
                'checkout_id' => $result['checkout_id'] ?? null,
                'qr_code'     => !empty($result['qr_code']) ? 'presente (' . strlen($result['qr_code']) . ' chars)' : 'VAZIO',
                'copy_paste'  => !empty($result['copy_paste']) ? 'presente (' . strlen($result['copy_paste']) . ' chars)' : 'VAZIO',
                'raw_keys'    => array_keys($result['raw'] ?? []),
            ]);

            // O código para o QR Code é copy_paste (preferido) ou qr_code
            $pixCode   = $result['copy_paste'] ?? $result['qr_code'] ?? '';
            $qrDisplay = $result['qr_code'] ?? $pixCode;

            // Gerar QR Code base64 a partir do código PIX
            $qrCodeBase64 = null;
            if (!empty($pixCode)) {
                $qrResponse = \Illuminate\Support\Facades\Http::timeout(10)
                    ->get('https://api.qrserver.com/v1/create-qr-code/', [
                        'size'   => '200x200',
                        'data'   => $pixCode,
                        'format' => 'png',
                    ]);

                if ($qrResponse->successful()) {
                    $qrCodeBase64 = base64_encode($qrResponse->body());
                } else {
                    Log::warning('SumUp PIX: falha ao gerar QR Code via qrserver', [
                        'status' => $qrResponse->status(),
                    ]);
                }
            }

            // Se a API não retornou código PIX, retornar erro claro
            if (empty($pixCode)) {
                Log::error('SumUp PIX: API nao retornou codigo PIX', [
                    'order_id' => $order->id,
                    'raw'      => $result['raw'] ?? [],
                ]);
                return response()->json([
                    'success' => false,
                    'error'   => 'A API SumUp não retornou o código PIX. Verifique se o PIX está habilitado na sua conta SumUp.',
                ], 422);
            }

            return response()->json([
                'success'        => true,
                'checkout_id'    => $result['checkout_id'] ?? null,
                'qr_code'        => $qrDisplay,
                'copy_paste'     => $pixCode,
                'qr_code_base64' => $qrCodeBase64,
                'expires_at'     => now()->addMinutes(30)->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::error('SumUp PIX error', [
                'order_id' => $request->order_id,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'error' => 'Erro ao gerar PIX: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Consulta status de um checkout SumUp.
     */
    public function sumupStatus(Request $request, \App\Services\Payment\SumUpService $sumUpService)
    {
        $request->validate(['order_id' => 'required|integer']);

        $order = Order::findOrFail($request->order_id);
        abort_unless(Auth::check() && Auth::id() === $order->user_id, 403);

        try {
            $transaction = \App\Models\SumUpTransaction::where('order_id', $order->id)
                ->latest()
                ->first();

            if (!$transaction) {
                return response()->json(['status' => 'PENDING']);
            }

            return response()->json([
                'status'      => $transaction->status,
                'checkout_id' => $transaction->checkout_id,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'PENDING']);
        }
    }
}
