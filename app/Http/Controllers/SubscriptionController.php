<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Services\Payment\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use App\Mail\PaymentConfirmedMail;
use App\Services\InvoiceService;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    protected $mpService;

    public function __construct(MercadoPagoService $mpService)
    {
        $this->mpService = $mpService;
    }

    public function checkout(Plan $plan)
    {
        // Período selecionado pelo usuário (ou padrão mensal)
        $period = $this->normalizePeriod(request()->query('period', 'mensal'));

        // Preço efetivo para o período escolhido
        $effectivePrice = $plan->getPriceForPeriod($period);

        // Períodos disponíveis para o plano
        $availablePeriods = $plan->getAvailablePeriods();

        // Prorrata (se o usuário já tem plano ativo e está fazendo upgrade)
        $prorataAmount = null;
        $isUpgrade = false;
        $isDowngrade = false;
        $currentPlan = null;

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->plan_id == $plan->id) {
                return redirect()->route('portal')->with('info', 'Você já possui este plano ativo.');
            }

            if ($user->plan_id) {
                $currentPlan = Plan::find($user->plan_id);
                if ($currentPlan) {
                    $currentPrice = $currentPlan->getPriceForPeriod($period);
                    if ($effectivePrice > $currentPrice) {
                        $isUpgrade    = true;
                        $prorataAmount = Plan::calculateProrata($currentPlan, $plan, $period);
                    } elseif ($effectivePrice < $currentPrice) {
                        $isDowngrade = true;
                    }
                }
            }
        }

        // Resolver credenciais MercadoPago
        $env    = \App\Models\Setting::get('mercadopago_env', config('payments.mercadopago.env', 'sandbox'));
        $prefix = $env === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';

        $publicKey = \App\Models\Setting::get($prefix . 'public_key')
            ?: \App\Models\Setting::get('mercadopago_public_key')
            ?: config('payments.mercadopago.public_key', '');

        $accessToken = \App\Models\Setting::get($prefix . 'access_token')
            ?: \App\Models\Setting::get('mercadopago_access_token')
            ?: config('payments.mercadopago.access_token', '');

        $paymentConfigured = trim((string) $accessToken) !== '' && trim((string) $publicKey) !== '';

        return view('site.subscription.checkout', compact(
            'plan', 'publicKey', 'paymentConfigured',
            'period', 'effectivePrice', 'availablePeriods',
            'prorataAmount', 'isUpgrade', 'isDowngrade', 'currentPlan'
        ));
    }

    public function process(Request $request, Plan $plan)
    {
        $period       = $this->normalizePeriod($request->input('period', 'mensal'));
        $effectivePrice = $plan->getPriceForPeriod($period);
        $isPaidPlan   = $effectivePrice > 0;

        $request->validate([
            'payment_method' => $isPaidPlan ? 'required|in:credit_card,pix' : 'nullable',
            'period'         => 'nullable|string|in:mensal,trimestral,semestral,anual',
            // Dados pessoais (se não logado)
            'name'     => Auth::check() ? 'nullable' : 'required|string|max:255',
            'email'    => Auth::check() ? 'nullable' : 'required|email|unique:users,email',
            'cpf'      => (Auth::check() && Auth::user()?->doc) ? 'nullable|string' : 'required|string',
            'password' => Auth::check() ? 'nullable' : 'required|min:8|confirmed',
            // Cartão de crédito
            'token'             => $isPaidPlan ? 'required_if:payment_method,credit_card' : 'nullable',
            'installments'      => $isPaidPlan ? 'required_if:payment_method,credit_card' : 'nullable',
            'payment_method_id' => $isPaidPlan ? 'required_if:payment_method,credit_card' : 'nullable',
            'issuer_id' => $isPaidPlan ? 'required_if:payment_method,credit_card' : 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // 1. Criar usuário se necessário
            if (!$user) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'doc' => $request->cpf,
                    'phone' => $request->phone,
                    'level' => 'iniciante',
                ]);
                Auth::login($user);

                // Enviar email de boas-vindas
                try {
                    Mail::to($user)->send(new WelcomeMail($user));
                } catch (\Exception $e) {
                    \Log::error('Erro ao enviar email de boas-vindas: ' . $e->getMessage());
                }
            }

            // Guarantee document if provided during checkout
            if ($request->filled('cpf') && !$user->doc) {
                $user->update(['doc' => $request->cpf]);
            }

            // Free plan: activate immediately (no payment)
            if (!$isPaidPlan) {
                $user->update([
                    'plan_id'        => $plan->id,
                    'plan_expires_at' => $this->planExpiresAt($plan, $period),
                ]);

                DB::commit();

                return redirect()->route('portal')->with('success', 'Plano ativado com sucesso!');
            }

            $mpAccessToken = trim((string) config('payments.mercadopago.access_token'));
            $mpPublicKey = trim((string) config('payments.mercadopago.public_key'));
            $paymentsConfigured = $mpAccessToken !== '' && $mpPublicKey !== '';
            $isSimulation = !$paymentsConfigured && config('app.debug');

            if (!$paymentsConfigured && !$isSimulation) {
                throw new \RuntimeException('MercadoPago não configurado para assinaturas.');
            }

            // Prorrata para upgrade
            $prorataAmount = 0.0;
            if ($user->plan_id && $user->plan_id != $plan->id) {
                $currentPlan = Plan::find($user->plan_id);
                if ($currentPlan && $effectivePrice > $currentPlan->getPriceForPeriod($period)) {
                    $prorataAmount = Plan::calculateProrata($currentPlan, $plan, $period);
                    $effectivePrice = min($effectivePrice, $prorataAmount > 0 ? $prorataAmount : $effectivePrice);
                }
            }

            // Create order (snapshot)
            $order = Order::create([
                'user_id'             => $user->id,
                'seller_id'           => null,
                'status'              => 'pending',
                'total_amount'        => $effectivePrice,
                'fee_amount'          => 0,
                'platform_fee_amount' => 0,
                'currency'            => 'BRL',
                'gateway'             => 'mercadopago',
                'gateway_account_id'  => null,
                'metadata' => [
                    'context'      => 'subscription',
                    'sale_type'    => 'subscription',
                    'period'       => $period,
                    'prorata'      => $prorataAmount > 0 ? $prorataAmount : null,
                    'public_token' => Str::random(40),
                ],
            ]);

            $order->items()->create([
                'item_type' => 'plan',
                'item_id'   => $plan->id,
                'title'     => $plan->name . ' (' . ucfirst($period) . ')',
                'price'     => $effectivePrice,
                'quantity'  => 1,
                'data' => [
                    'plan_slug' => $plan->slug,
                    'period'    => $period,
                    'prorata'   => $prorataAmount > 0 ? $prorataAmount : null,
                ],
            ]);

            // Charge via MercadoPago
            if ($isSimulation) {
                // Simular pagamento sem chamar a API (modo debug sem chaves MP)
                if ($request->payment_method === 'pix') {
                    // Pix simulado: gera QR Code fictício para demonstração do fluxo
                    $fakePixCode = 'SIM.' . strtoupper(Str::random(20)) . '.' . number_format((float) $effectivePrice, 2, '', '');
                    // QR Code 1x1 pixel PNG transparente em base64 (placeholder seguro)
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
            } else if ($plan->is_recurring && !empty($plan->mp_plan_id)) {
                $paymentResult = $this->mpService->subscribeUser($plan->mp_plan_id, [
                    'email' => $user->email,
                    'card_token' => $request->token,
                    'reason' => 'Assinatura: ' . $plan->name,
                    'external_reference' => (string) $order->id,
                    'back_url' => route('subscription.success', $order),
                ]);

                // Se gerou um init_point (não tinha token), redirecionamos
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

                // Se processou direto
                if (in_array($paymentResult['status'] ?? '', ['authorized', 'active'])) {
                    $paymentResult['status'] = 'approved'; // Map to approved for common logic below
                }
            } else if ($request->payment_method === 'pix') {
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
            } else if (in_array(($paymentResult['status'] ?? ''), ['pending', 'in_process'], true)) {
                // Pix ou pendente
                $order->update(['transaction_id' => (string) ($paymentResult['id'] ?? null)]);
            } else {
                $detail = $paymentResult['status_detail'] ?? $paymentResult['status'] ?? 'unknown';
                throw new \RuntimeException('Pagamento não aprovado: ' . $detail);
            }

            DB::commit();

            if ($request->payment_method === 'pix') {
                return view('site.subscription.pix', [
                    'order' => $order,
                    'qr_code' => $paymentResult['qr_code'],
                    'qr_code_base64' => $paymentResult['qr_code_base64']
                ]);
            }

            return redirect()->route('subscription.success', $order);

        } catch (\Exception $e) {
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
        // Período escolhido pelo usuário tem prioridade
        $period = $this->normalizePeriod($period);
        if ($period !== 'mensal') {
            return match ($period) {
                'trimestral' => now()->addMonths(3),
                'semestral'  => now()->addMonths(6),
                'anual'      => now()->addYear(),
                default      => now()->addMonth(),
            };
        }

        // Fallback: usa o período configurado no plano
        $planPeriod = trim((string) ($plan->period ?? ''));
        $periodLower = \Illuminate\Support\Str::lower($planPeriod);

        if ($periodLower === 'vitalício' || $periodLower === 'vitalicio') {
            return null;
        }

        if (ctype_digit($planPeriod)) {
            return now()->addDays((int) $planPeriod);
        }

        return match ($periodLower) {
            'mensal'      => now()->addMonth(),
            'trimestral'  => now()->addMonths(3),
            'semestral'   => now()->addMonths(6),
            'anual'       => now()->addYear(),
            default       => now()->addMonth(),
        };
    }

    private function normalizePeriod(string $period): string
    {
        $p = strtolower(trim($period));
        return in_array($p, ['mensal', 'trimestral', 'semestral', 'anual'], true) ? $p : 'mensal';
    }
}
