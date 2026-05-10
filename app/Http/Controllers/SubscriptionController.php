<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Services\AffiliateTrackingService;
use App\Services\Payment\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    use \App\Traits\PreventsDoubleSubmit;

    protected MercadoPagoService $mpService;

    public function __construct(MercadoPagoService $mpService)
    {
        $this->mpService = $mpService;
    }

    public function checkout(Plan $plan)
    {
        $availablePeriods = $plan->getAvailablePeriods();
        $period = $this->resolvePlanPeriod($plan, request()->query('period'));
        $effectivePrice = $plan->getPriceForPeriod($period);

        $prorataAmount = null;
        $isUpgrade = false;
        $isDowngrade = false;
        $currentPlan = null;

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->plan_id == $plan->id) {
                return redirect()->route('portal')->with('info', 'Voce ja possui este plano ativo.');
            }

            if ($user->plan_id) {
                $currentPlan = Plan::find($user->plan_id);
                if ($currentPlan) {
                    $currentPeriod = $this->resolvePlanPeriod($currentPlan, $period);
                    $currentPrice = $currentPlan->getPriceForPeriod($currentPeriod);

                    if ($effectivePrice > $currentPrice) {
                        $isUpgrade = true;
                        $prorataAmount = Plan::calculateProrata($currentPlan, $plan, $period);
                    } elseif ($effectivePrice < $currentPrice) {
                        $isDowngrade = true;
                    }
                }
            }
        }

        ['public_key' => $publicKey, 'access_token' => $accessToken] = $this->mercadoPagoCredentials();
        $mpHasCredentials = trim((string) $accessToken) !== '' && trim((string) $publicKey) !== '';
        $mpEnabled = (int) (\App\Models\Setting::get('mercadopago_enabled', 1)) === 1;
        $mpAllowSubscriptions = (int) (\App\Models\Setting::get('mercadopago_allow_subscriptions', 1)) === 1;
        $paymentConfigured = $mpHasCredentials && $mpEnabled && $mpAllowSubscriptions;

        // SumUp disponibilidade
        $sumupEnabled = (int) (\App\Models\Setting::get('sumup_enabled', 0)) === 1;
        $sumupApiKey = (string) (\App\Models\Setting::get('sumup_api_key', '') ?? '');
        $sumupConfigured = $sumupEnabled && $sumupApiKey !== '';
        $sumupAllowSubscriptions = (int) (\App\Models\Setting::get('sumup_allow_subscriptions', 1)) === 1;
        $sumupAvailable = $sumupConfigured && $sumupAllowSubscriptions;

        // Métodos ativos
        $mpMethods = [];
        if ((int) \App\Models\Setting::get('mercadopago_method_credit_card', 1) === 1) $mpMethods[] = 'Cartão';
        if ((int) \App\Models\Setting::get('mercadopago_method_pix', 1) === 1) $mpMethods[] = 'Pix';

        $sumupMethods = [];
        if ($sumupAvailable) {
            if ((int) \App\Models\Setting::get('sumup_method_card', 1) === 1) $sumupMethods[] = 'Cartão';
            if ((int) \App\Models\Setting::get('sumup_method_pix', 1) === 1) $sumupMethods[] = 'Pix';
        }

        return view('site.subscription.checkout', compact(
            'plan',
            'publicKey',
            'paymentConfigured',
            'period',
            'effectivePrice',
            'availablePeriods',
            'prorataAmount',
            'isUpgrade',
            'isDowngrade',
            'currentPlan',
            'sumupAvailable',
            'mpMethods',
            'sumupMethods'
        ));
    }

    public function process(Request $request, Plan $plan, AffiliateTrackingService $tracking)
    {
        // Proteção contra double-submit
        if ($this->isDoubleSubmit("subscription:{$plan->id}", 15)) {
            return back()->with('error', 'Processando seu pagamento. Aguarde...');
        }

        $period = $this->resolvePlanPeriod($plan, $request->input('period'));
        $effectivePrice = $plan->getPriceForPeriod($period);
        $isPaidPlan = $effectivePrice > 0;

        $request->validate([
            'payment_method' => $isPaidPlan ? 'required|in:credit_card,pix' : 'nullable',
            'gateway_provider' => 'nullable|string|in:mercadopago,sumup',
            'period' => 'nullable|string|in:mensal,trimestral,semestral,anual',
            'name' => Auth::check() ? 'nullable' : 'required|string|max:255',
            'email' => Auth::check() ? 'nullable' : 'required|email|unique:users,email',
            'cpf' => (Auth::check() && Auth::user()?->doc) ? 'nullable|string' : 'required|string',
            'password' => Auth::check() ? 'nullable' : 'required|min:8|confirmed',
            'token' => $isPaidPlan ? 'required_if:payment_method,credit_card' : 'nullable',
            'installments' => $isPaidPlan ? 'required_if:payment_method,credit_card' : 'nullable',
            'payment_method_id' => $isPaidPlan ? 'required_if:payment_method,credit_card' : 'nullable',
            'issuer_id' => $isPaidPlan ? 'required_if:payment_method,credit_card' : 'nullable',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            if (!$user) {
                $referrer = $tracking->resolveReferrerByCode($tracking->currentReferralCode($request));
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'doc' => $request->cpf,
                    'phone' => $request->phone,
                    'level' => 'iniciante',
                    'referred_by' => $referrer?->id,
                ]);
                Auth::login($user);
                $tracking->attachRegisteredUser($request, $user, $referrer?->referral_code ?? null);

                try {
                    Mail::to($user)->send(new WelcomeMail($user));
                } catch (\Throwable $e) {
                    \Log::error('Erro ao enviar email de boas-vindas: ' . $e->getMessage());
                }
            }

            if ($request->filled('cpf') && !$user->doc) {
                $user->update(['doc' => $request->cpf]);
            }

            if (!$isPaidPlan) {
                $user->update([
                    'plan_id' => $plan->id,
                    'plan_expires_at' => $this->planExpiresAt($plan, $period),
                ]);

                DB::commit();

                return redirect()->route('portal')->with('success', 'Plano ativado com sucesso!');
            }

            ['public_key' => $mpPublicKey, 'access_token' => $mpAccessToken] = $this->mercadoPagoCredentials();
            $paymentsConfigured = trim((string) $mpAccessToken) !== '' && trim((string) $mpPublicKey) !== '';
            $isSimulation = !$paymentsConfigured && config('app.debug');

            $chosenGateway = $request->input('gateway_provider', 'mercadopago');

            // Se escolheu SumUp, verificar disponibilidade
            if ($chosenGateway === 'sumup') {
                $sumupEnabled = (int) (\App\Models\Setting::get('sumup_enabled', 0)) === 1;
                $sumupApiKey = trim((string) (\App\Models\Setting::get('sumup_api_key', '') ?? ''));
                if (!$sumupEnabled || $sumupApiKey === '') {
                    throw new \RuntimeException('SumUp não está configurado para assinaturas.');
                }
            } elseif (!$paymentsConfigured && !$isSimulation) {
                throw new \RuntimeException('MercadoPago nao configurado para assinaturas.');
            }

            $prorataAmount = 0.0;
            if ($user->plan_id && $user->plan_id != $plan->id) {
                $currentPlan = Plan::find($user->plan_id);
                $currentPeriod = $currentPlan ? $this->resolvePlanPeriod($currentPlan, $period) : $period;

                if ($currentPlan && $effectivePrice > $currentPlan->getPriceForPeriod($currentPeriod)) {
                    $prorataAmount = Plan::calculateProrata($currentPlan, $plan, $period);
                    $effectivePrice = min($effectivePrice, $prorataAmount > 0 ? $prorataAmount : $effectivePrice);
                }
            }

            $order = Order::create([
                'user_id' => $user->id,
                'seller_id' => null,
                'status' => 'pending',
                'total_amount' => $effectivePrice,
                'fee_amount' => 0,
                'platform_fee_amount' => 0,
                'currency' => 'BRL',
                'gateway' => $chosenGateway,
                'gateway_account_id' => null,
                'metadata' => [
                    'context' => 'subscription',
                    'sale_type' => 'subscription',
                    'period' => $period,
                    'prorata' => $prorataAmount > 0 ? $prorataAmount : null,
                    'public_token' => Str::random(40),
                ],
            ]);

            $order->items()->create([
                'item_type' => 'plan',
                'item_id' => $plan->id,
                'title' => $plan->name . ' (' . ucfirst($period) . ')',
                'price' => $effectivePrice,
                'quantity' => 1,
                'data' => [
                    'plan_slug' => $plan->slug,
                    'period' => $period,
                    'prorata' => $prorataAmount > 0 ? $prorataAmount : null,
                ],
            ]);

            $tracking->recordCheckoutStarted($request, $order, $plan);

            // Processar via SumUp se escolhido
            if ($chosenGateway === 'sumup') {
                DB::commit();
                return $this->processSubscriptionSumUp($order, $plan, $period);
            }

            if ($isSimulation) {
                if ($request->payment_method === 'pix') {
                    $fakePixCode = 'SIM.' . strtoupper(Str::random(20)) . '.' . number_format((float) $effectivePrice, 2, '', '');
                    $fakeQrBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

                    DB::commit();

                    return view('site.subscription.pix', [
                        'order' => $order,
                        'qr_code' => $fakePixCode,
                        'qr_code_base64' => $fakeQrBase64,
                    ]);
                }

                $paymentResult = [
                    'status' => 'approved',
                    'id' => 'sim_' . Str::random(10),
                    'status_detail' => 'accreditation_simulation',
                ];
            } elseif ($plan->is_recurring && !empty($plan->mp_plan_id)) {
                $paymentResult = $this->mpService->subscribeUser($plan->mp_plan_id, [
                    'email' => $user->email,
                    'card_token' => $request->token,
                    'reason' => 'Assinatura: ' . $plan->name,
                    'external_reference' => (string) $order->id,
                    'back_url' => route('subscription.success', $order),
                ]);

                if (!empty($paymentResult['init_point']) && empty($request->token)) {
                    $order->update([
                        'metadata' => array_merge($order->metadata ?? [], [
                            'mercadopago_preapproval_id' => $paymentResult['id'] ?? null,
                            'mercadopago_init_point' => $paymentResult['init_point'] ?? null,
                        ]),
                    ]);

                    DB::commit();

                    return redirect($paymentResult['init_point']);
                }

                if (in_array($paymentResult['status'] ?? '', ['authorized', 'active'], true)) {
                    $paymentResult['status'] = 'approved';
                }
            } elseif ($request->payment_method === 'pix') {
                $paymentResult = $this->mpService->createPixPayment($order, [
                    'email' => $user->email,
                    'name' => $user->name,
                    'cpf' => $request->cpf ?? $user->doc,
                ]);
            } else {
                $paymentResult = $this->mpService->createCreditCardPayment($order, [
                    'token' => $request->token,
                    'installments' => $request->installments,
                    'payment_method_id' => $request->payment_method_id,
                    'issuer_id' => $request->issuer_id,
                    'email' => $user->email,
                    'cpf' => $request->cpf ?? $user->doc,
                ]);
            }

            if (($paymentResult['status'] ?? '') === 'approved') {
                app(PaymentWebhookController::class)->processPaidOrder($order, $paymentResult['id'] ?? null, $paymentResult);
            } elseif (in_array(($paymentResult['status'] ?? ''), ['pending', 'in_process'], true)) {
                $order->update(['transaction_id' => (string) ($paymentResult['id'] ?? null)]);
            } else {
                $detail = $paymentResult['status_detail'] ?? $paymentResult['status'] ?? 'unknown';
                throw new \RuntimeException('Pagamento nao aprovado: ' . $detail);
            }

            DB::commit();

            if ($request->payment_method === 'pix') {
                return view('site.subscription.pix', [
                    'order' => $order,
                    'qr_code' => $paymentResult['qr_code'],
                    'qr_code_base64' => $paymentResult['qr_code_base64'],
                ]);
            }

            return redirect()->route('subscription.success', $order);
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Erro ao processar assinatura: ' . $e->getMessage())->withInput();
        }
    }

    public function success(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items');
        $planName = optional($order->items->firstWhere('item_type', 'plan'))->title;

        return view('site.subscription.success', compact('order', 'planName'));
    }

    private function planExpiresAt(Plan $plan, string $period = 'mensal'): ?\Carbon\Carbon
    {
        $period = $this->resolvePlanPeriod($plan, $period);
        if ($period !== 'mensal') {
            return match ($period) {
                'trimestral' => now()->addMonths(3),
                'semestral' => now()->addMonths(6),
                'anual' => now()->addYear(),
                default => now()->addMonth(),
            };
        }

        $planPeriod = trim((string) ($plan->period ?? ''));
        $periodLower = strtolower($planPeriod);

        if (in_array($periodLower, ['vitalicio', 'vitalício'], true)) {
            return null;
        }

        if (ctype_digit($planPeriod)) {
            return now()->addDays((int) $planPeriod);
        }

        return match ($periodLower) {
            'mensal' => now()->addMonth(),
            'trimestral' => now()->addMonths(3),
            'semestral' => now()->addMonths(6),
            'anual' => now()->addYear(),
            default => now()->addMonth(),
        };
    }

    private function resolvePlanPeriod(Plan $plan, ?string $period): string
    {
        $normalized = Plan::sanitizePeriod($period);
        $availablePeriods = $plan->getAvailablePeriods();

        if (array_key_exists($normalized, $availablePeriods)) {
            return $normalized;
        }

        return $plan->firstAvailablePeriod();
    }

    private function mercadoPagoCredentials(): array
    {
        $env = \App\Models\Setting::get('mercadopago_env', config('payments.mercadopago.env', 'sandbox'));
        $prefix = $env === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';

        return [
            'public_key' => \App\Models\Setting::get($prefix . 'public_key')
                ?: \App\Models\Setting::get('mercadopago_public_key')
                ?: config('payments.mercadopago.public_key', ''),
            'access_token' => \App\Models\Setting::get($prefix . 'access_token')
                ?: \App\Models\Setting::get('mercadopago_access_token')
                ?: config('payments.mercadopago.access_token', ''),
        ];
    }

    /**
     * AJAX: Cria pedido + checkout SumUp e retorna checkoutId para renderizar widget inline.
     * Protege contra duplicidade: reutiliza pedido pending existente se houver.
     */
    public function prepareSumUp(Request $request, Plan $plan)
    {
        $period = $this->resolvePlanPeriod($plan, $request->input('period'));
        $effectivePrice = $plan->getPriceForPeriod($period);

        if ($effectivePrice <= 0) {
            return response()->json(['error' => 'Plano gratuito não precisa de pagamento.'], 422);
        }

        $user = auth()->user();

        // PROTEÇÃO CONTRA DUPLICIDADE: verificar se já existe pedido pending para este plano
        $existingOrder = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('gateway', 'sumup')
            ->whereHas('items', function ($q) use ($plan) {
                $q->where('item_type', 'plan')->where('item_id', $plan->id);
            })
            ->where('created_at', '>', now()->subHours(24))
            ->first();

        if ($existingOrder) {
            // Reutilizar pedido existente se já tem checkout_id
            $checkoutId = data_get($existingOrder->metadata, 'sumup_checkout_id');
            if ($checkoutId) {
                return response()->json([
                    'success' => true,
                    'checkout_id' => $checkoutId,
                    'order_id' => $existingOrder->id,
                    'amount' => (float) $existingOrder->total_amount,
                    'success_url' => route('subscription.success', $existingOrder->id),
                    'reused' => true,
                ]);
            }
        }

        // Verificar prorrata
        $prorataAmount = 0.0;
        if ($user->plan_id && $user->plan_id != $plan->id) {
            $currentPlan = Plan::find($user->plan_id);
            if ($currentPlan && $effectivePrice > $currentPlan->getPriceForPeriod($period)) {
                $prorataAmount = Plan::calculateProrata($currentPlan, $plan, $period);
                $effectivePrice = min($effectivePrice, $prorataAmount > 0 ? $prorataAmount : $effectivePrice);
            }
        }

        // Criar pedido
        $order = Order::create([
            'user_id' => $user->id,
            'seller_id' => null,
            'status' => 'pending',
            'total_amount' => $effectivePrice,
            'fee_amount' => 0,
            'platform_fee_amount' => 0,
            'currency' => 'BRL',
            'gateway' => 'sumup',
            'gateway_account_id' => null,
            'metadata' => [
                'context' => 'subscription',
                'sale_type' => 'subscription',
                'period' => $period,
                'prorata' => $prorataAmount > 0 ? $prorataAmount : null,
                'public_token' => Str::random(40),
            ],
        ]);

        $order->items()->create([
            'item_type' => 'plan',
            'item_id' => $plan->id,
            'title' => $plan->name . ' (' . ucfirst($period) . ')',
            'price' => $effectivePrice,
            'quantity' => 1,
            'data' => [
                'plan_slug' => $plan->slug,
                'period' => $period,
            ],
        ]);

        // Criar checkout SumUp
        try {
            $sumUpService = app(\App\Services\Payment\SumUpService::class);
            $checkout = $sumUpService->createCheckout($order, [
                'description' => 'Assinatura: ' . $plan->name . ' (' . ucfirst($period) . ')',
                'return_url'  => route('subscription.success', $order->id),
            ]);

            $checkoutId = $checkout['checkout_id'] ?? $checkout['id'] ?? null;

            $order->update([
                'metadata' => array_merge($order->metadata ?? [], [
                    'sumup_checkout_id' => $checkoutId,
                ]),
            ]);

            $order->refresh();

            return response()->json([
                'success' => true,
                'checkout_id' => $checkoutId,
                'order_id' => $order->id,
                'amount' => (float) $order->total_amount,
                'base_amount' => (float) (data_get($order->metadata, 'sumup_base_amount') ?? $effectivePrice),
                'fee_amount'  => (float) (data_get($order->metadata, 'sumup_fee_amount') ?? 0),
                'success_url' => route('subscription.success', $order->id),
            ]);
        } catch (\Throwable $e) {
            $order->update(['status' => 'failed']);
            return response()->json(['error' => 'Erro ao criar checkout SumUp: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Processa assinatura via SumUp - renderiza checkout.transparent com formulário SumUp inline.
     */
    private function processSubscriptionSumUp(Order $order, Plan $plan, string $period)
    {
        try {
            $sumUpService = app(\App\Services\Payment\SumUpService::class);

            $checkout = $sumUpService->createCheckout($order, [
                'description' => 'Assinatura: ' . $plan->name . ' (' . ucfirst($period) . ')',
                'return_url'  => route('subscription.success', $order->id),
            ]);

            $checkoutId = $checkout['checkout_id'] ?? $checkout['id'] ?? null;

            $order->update([
                'metadata' => array_merge($order->metadata ?? [], [
                    'sumup_checkout_id'  => $checkoutId,
                    'sumup_checkout_url' => $checkout['checkout_url'] ?? data_get($checkout, 'raw.checkout_url'),
                ]),
            ]);

            $merchantCode = trim((string) (\App\Models\Setting::get('sumup_merchant_code') ?? ''));
            $methodCard = (bool)(int)(\App\Models\Setting::get('sumup_method_card', 1));
            $methodPix  = (bool)(int)(\App\Models\Setting::get('sumup_method_pix', 1));
            $maxInstallments = max(1, min(12, (int) (\App\Models\Setting::get('sumup_max_installments', 12))));
            $noInterestUpTo  = max(1, min(12, (int) (\App\Models\Setting::get('sumup_installments_no_interest', 1))));
            $installmentTax  = max(0.0, (float) (\App\Models\Setting::get('sumup_installment_tax', 0)));
            $passFeeToClient = (bool)(int)(\App\Models\Setting::get('sumup_pass_fee', 0));

            return view('checkout.transparent', [
                'order'                       => $order->fresh('items', 'user'),
                'preferenceId'                => '',
                'publicKey'                   => '',
                'gateway'                     => 'sumup',
                'checkoutId'                  => $checkoutId ?? '',
                'sumupMerchantCode'           => $merchantCode,
                'sumupMethodCard'             => $methodCard,
                'sumupMethodPix'              => $methodPix,
                'sumupMaxInstallments'        => $maxInstallments,
                'sumupInstallmentsNoInterest' => $noInterestUpTo,
                'sumupInstallmentTax'         => $installmentTax,
                'sumupPassFeeToClient'        => $passFeeToClient,
            ]);
        } catch (\Throwable $e) {
            \Log::error('SumUp subscription checkout failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'plan_id' => $plan->id,
            ]);
            return back()->with('error', 'Erro ao processar pagamento via SumUp: ' . $e->getMessage());
        }
    }
}
