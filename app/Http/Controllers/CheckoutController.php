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
use App\Traits\SumUpIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    use SumUpIntegration;
    use \App\Traits\PreventsDoubleSubmit;
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
            $availableGateways = [];

            return view('checkout.index', compact('course', 'mpEnabled', 'preferredGateway', 'availableGateways'));
        }

        $gateways = \App\Models\GatewayAccount::resolveForSeller((int) $seller->id);
        $mpEnabled = $gateways['mpEnabled'];
        
        // Verificar gateways disponíveis incluindo SumUp
        $availableGateways = $this->getAvailableGateways($effectiveTotal, 'course');
        $sumupAvailable = $this->shouldShowSumUp($effectiveTotal, 'course', $this->getUserType());
        
        // Determinar gateway preferido
        $preferredGateway = 'mercadopago';
        if ($sumupAvailable && !$mpEnabled) {
            $preferredGateway = 'sumup';
        }

        if (!$mpEnabled && !$sumupAvailable) {
            return redirect()
                ->route('courses.show', $course->slug ?: $course->id)
                ->with('error', 'Este curso nao esta disponivel para compra: nenhum gateway de pagamento esta configurado.');
        }

        // Adicionar dados do SumUp ao contexto
        $context = compact('course', 'mpEnabled', 'preferredGateway', 'availableGateways');
        $context = $this->addSumUpToCheckoutContext($context, $effectiveTotal, 'course');

        return view('checkout.index', $context);
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
            'gateway_provider' => 'nullable|string|in:mercadopago,sumup',
        ]);

        $effectiveTotal = round((float) ($course->effective_price ?? ($course->price ?? 0)), 2);
        $gateways = [
            'mpEnabled' => false,
            'preferredGateway' => null,
            'mpPublicKey' => '',
        ];
        $gatewayProvider = 'free';

        if ($effectiveTotal > 0) {
            // Determinar gateway: respeitar escolha do usuário, com fallback
            $chosenGateway = $request->input('gateway_provider', 'mercadopago');

            if ($chosenGateway === 'sumup') {
                $sumupAvailable = $this->shouldShowSumUp($effectiveTotal, 'course', $this->getUserType());
                if (!$sumupAvailable) {
                    return back()->with('error', 'SumUp não está disponível para esta compra.');
                }
                $gatewayProvider = 'sumup';
            } else {
                $gateways = \App\Models\GatewayAccount::resolveForSeller((int) $seller->id);
                $gatewayProvider = 'mercadopago';

                if (!$gateways['mpEnabled']) {
                    // Se MP não está disponível, tentar fallback para SumUp
                    $sumupAvailable = $this->shouldShowSumUp($effectiveTotal, 'course', $this->getUserType());
                    if ($sumupAvailable) {
                        $gatewayProvider = 'sumup';
                    } else {
                        return back()->with('error', 'Nenhum gateway de pagamento disponível.');
                    }
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

            // Se o usuário escolheu SumUp, processar via SumUp
            if ($gatewayProvider === 'sumup') {
                return $this->processCourseSumUpPayment($order, $course);
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

    /**
     * Processa o checkout de curso via SumUp
     */
    protected function processCourseSumUpPayment(Order $order, Course $course)
    {
        try {
            $sumUpService = app(\App\Services\Payment\SumUpService::class);

            // Validar API Key
            $apiKey = trim((string) (\App\Models\Setting::get('sumup_api_key')
                ?: config('payments.sumup.api_key', '')));

            if (empty($apiKey)) {
                return back()->with('error', 'SumUp não configurado. Falta API Key.');
            }

            // Criar checkout SumUp
            $checkout = $sumUpService->createCheckout($order, [
                'description' => 'Curso: ' . $course->title,
                'return_url'  => route('checkout.success', $order->id),
            ]);

            // Salvar dados do checkout no pedido
            $order->update([
                'metadata' => array_merge($order->metadata ?? [], [
                    'sumup_checkout_id'  => $checkout['id'] ?? null,
                    'sumup_checkout_url' => $checkout['checkout_url'] ?? null,
                ]),
            ]);

            // Recarregar para pegar total_amount atualizado caso a taxa tenha sido repassada
            $order = $order->fresh('items', 'user');

            // Merchant Code (usado como public key no frontend)
            $merchantCode = trim((string) (\App\Models\Setting::get('sumup_merchant_code')
                ?: config('payments.sumup.merchant_code', '')));

            // Métodos habilitados
            $methodCardRaw = \App\Models\Setting::get('sumup_method_card');
            $methodPixRaw  = \App\Models\Setting::get('sumup_method_pix');
            $methodCard = $methodCardRaw !== null ? (bool)(int)$methodCardRaw : true;
            $methodPix  = $methodPixRaw  !== null ? (bool)(int)$methodPixRaw  : true;

            // Parcelamento e taxas
            $maxInstallments = max(1, min(12, (int) (\App\Models\Setting::get('sumup_max_installments', 12))));
            $noInterestUpTo  = max(1, min(12, (int) (\App\Models\Setting::get('sumup_installments_no_interest', 1))));
            $installmentTax  = max(0.0, (float) (\App\Models\Setting::get('sumup_installment_tax', 0)));
            $passFeeToClient = (bool)(int)(\App\Models\Setting::get('sumup_pass_fee', 0));

            return view('checkout.transparent', [
                'order'                    => $order,
                'preferenceId'             => '',
                'publicKey'                => '',
                'gateway'                  => 'sumup',
                'checkoutId'               => $checkout['checkout_id'] ?? $checkout['id'] ?? '',
                'sumupMerchantCode'        => $merchantCode,
                'sumupMethodCard'          => $methodCard,
                'sumupMethodPix'           => $methodPix,
                'sumupMaxInstallments'     => $maxInstallments,
                'sumupInstallmentsNoInterest' => $noInterestUpTo,
                'sumupInstallmentTax'      => $installmentTax,
                'sumupPassFeeToClient'     => $passFeeToClient,
            ]);
        } catch (\Throwable $e) {
            \Log::error('SumUp checkout creation failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'course_id' => $course->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Erro ao processar pagamento via SumUp: ' . $e->getMessage());
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

        abort_unless(Auth::check() && Auth::id() === $order->user_id, 403);

        try {
            $sync = $sumUpService->reconcileOrderTransactions($order);
            $order->refresh();

            if ((string) $order->status === 'paid' || ($sync['paid'] ?? false)) {
                return response()->json([
                    'success'        => true,
                    'status'         => 'PAID',
                    'checkout_id'    => $sync['checkout_id'] ?? null,
                    'transaction_id' => $sync['transaction_id'] ?? $order->transaction_id,
                    'message'        => 'Pedido ja esta pago.',
                ]);
            }

            $result = $sumUpService->processPixCheckout($order);

            Log::debug('SumUp PIX result', [
                'order_id'       => $order->id,
                'checkout_id'    => $result['checkout_id'] ?? null,
                'has_qr_base64'  => !empty($result['qr_code_base64']),
                'has_copy_paste' => !empty($result['copy_paste']),
                'copy_paste_len' => strlen($result['copy_paste'] ?? ''),
            ]);

            $pixCode      = $result['copy_paste'] ?? '';
            $qrCodeBase64 = $result['qr_code_base64'] ?? '';

            // Se a API não retornou base64, gerar via qrserver como fallback
            if (empty($qrCodeBase64) && !empty($pixCode)) {
                $qrResponse = \Illuminate\Support\Facades\Http::timeout(10)
                    ->get('https://api.qrserver.com/v1/create-qr-code/', [
                        'size'   => '200x200',
                        'data'   => $pixCode,
                        'format' => 'png',
                    ]);

                if ($qrResponse->successful()) {
                    $qrCodeBase64 = base64_encode($qrResponse->body());
                }
            }

            // Se não há código PIX, retornar erro claro
            if (empty($pixCode)) {
                Log::error('SumUp PIX: API nao retornou codigo PIX', [
                    'order_id' => $order->id,
                    'raw'      => $result['raw'] ?? [],
                ]);
                return response()->json([
                    'success' => false,
                    'error'   => 'A API SumUp não retornou o código PIX. Verifique se o PIX está habilitado na sua conta SumUp e se a moeda BRL está configurada.',
                ], 422);
            }

            return response()->json([
                'success'        => true,
                'checkout_id'    => $result['checkout_id'] ?? null,
                'qr_code'        => $pixCode,
                'copy_paste'     => $pixCode,
                'qr_code_base64' => $qrCodeBase64,
                'expires_at'     => now()->addMinutes((int) (\App\Models\Setting::get('sumup_pix_expiration_minutes', 10) ?: 10))->toIso8601String(),
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
            $checkoutId = (string) $request->query('checkout_id', '');
            $sync = $sumUpService->reconcileOrderTransactions($order, $checkoutId ?: null);
            $order->refresh();

            if ((string) $order->status === 'paid' || ($sync['paid'] ?? false)) {
                return response()->json([
                    'status'         => 'PAID',
                    'checkout_id'    => $sync['checkout_id'] ?? $checkoutId,
                    'transaction_id' => $sync['transaction_id'] ?? $order->transaction_id,
                    'settled'        => (bool) ($sync['settled'] ?? false),
                ]);
            }

            $query = \App\Models\SumUpTransaction::where('order_id', $order->id);
            if ($checkoutId !== '') {
                $query->where('checkout_id', $checkoutId);
            }

            $transaction = $query
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

    /**
     * Recria o checkout SumUp com um novo numero de parcelas.
     *
     * Quando o cliente muda entre "a vista" e "parcelado" no formulario, o valor
     * cobrado muda (taxa de gateway/juros so se aplicam quando parcelado acima do
     * limite sem juros). Como o SumUp cria o checkout com valor fixo, precisamos
     * gerar um novo checkout toda vez que o total muda.
     */
    public function sumupRecreateCheckout(Request $request, \App\Services\Payment\SumUpService $sumUpService)
    {
        $request->validate([
            'order_id'     => 'required|integer',
            'installments' => 'required|integer|min:1|max:12',
        ]);

        $order = Order::findOrFail($request->order_id);
        abort_unless(Auth::check() && Auth::id() === $order->user_id, 403);

        if ($order->status === 'paid') {
            return response()->json(['success' => false, 'error' => 'Pedido ja esta pago.'], 422);
        }

        try {
            $checkout = $sumUpService->createCheckout($order, [
                'installments' => (int) $request->input('installments'),
            ]);

            $order->refresh();

            return response()->json([
                'success'       => true,
                'checkout_id'   => $checkout['checkout_id'] ?? '',
                'charge_amount' => (float) ($checkout['charge_amount'] ?? $order->total_amount),
                'base_amount'   => (float) ($checkout['base_amount']   ?? $order->total_amount),
                'fee_amount'    => (float) ($checkout['fee_amount']    ?? 0),
                'installments'  => (int)   ($checkout['installments']  ?? 1),
            ]);
        } catch (\Throwable $e) {
            Log::error('SumUp recreate checkout failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'error' => 'Falha ao atualizar checkout: ' . $e->getMessage()], 500);
        }
    }
}
