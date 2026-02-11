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
        // Se usuário logado já tiver plano ativo, redirecionar
        if (Auth::check() && Auth::user()->plan_id == $plan->id) {
            return redirect()->route('portal')->with('info', 'Você já possui este plano ativo.');
        }

        $publicKey = (string) config('payments.mercadopago.public_key');
        $accessToken = (string) config('payments.mercadopago.access_token');
        $paymentConfigured = trim($accessToken) !== '' && trim($publicKey) !== '';

        return view('site.subscription.checkout', compact('plan', 'publicKey', 'paymentConfigured'));
    }

    public function process(Request $request, Plan $plan)
    {
        $isPaidPlan = (float) $plan->price > 0;

        $request->validate([
            'payment_method' => $isPaidPlan ? 'required|in:credit_card,pix' : 'nullable',
            // Dados pessoais (se não logado)
            'name' => Auth::check() ? 'nullable' : 'required|string|max:255',
            'email' => Auth::check() ? 'nullable' : 'required|email|unique:users,email',
            'cpf' => (Auth::check() && Auth::user()?->doc) ? 'nullable|string' : 'required|string', // Validar CPF se possível
            'password' => Auth::check() ? 'nullable' : 'required|min:8|confirmed',
            // Cartão de crédito
            'token' => $isPaidPlan ? 'required_if:payment_method,credit_card' : 'nullable',
            'installments' => $isPaidPlan ? 'required_if:payment_method,credit_card' : 'nullable',
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
                    'level' => 'Iniciante',
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
                    'plan_id' => $plan->id,
                    'plan_expires_at' => $this->planExpiresAt($plan),
                ]);

                DB::commit();

                return redirect()->route('portal')->with('success', 'Plano ativado com sucesso!');
            }

            $mpAccessToken = trim((string) config('payments.mercadopago.access_token'));
            $mpPublicKey = trim((string) config('payments.mercadopago.public_key'));
            $paymentsConfigured = $mpAccessToken !== '' && $mpPublicKey !== '';

            if (!$paymentsConfigured) {
                throw new \RuntimeException('MercadoPago não configurado para assinaturas.');
            }

            // Create order (snapshot)
            $order = Order::create([
                'user_id' => $user->id,
                'seller_id' => null,
                'status' => 'pending',
                'total_amount' => $plan->price,
                'fee_amount' => 0,
                'platform_fee_amount' => 0,
                'currency' => 'BRL',
                'gateway' => 'mercadopago',
                'gateway_account_id' => null,
                'metadata' => [
                    'context' => 'subscription',
                    'sale_type' => 'subscription',
                    'public_token' => Str::random(40),
                ],
            ]);

            $order->items()->create([
                'item_type' => 'plan',
                'item_id' => $plan->id,
                'title' => $plan->name,
                'price' => $plan->price,
                'quantity' => 1,
                'data' => [
                    'plan_slug' => $plan->slug,
                    'period' => $plan->period,
                ],
            ]);

            // Charge via MercadoPago
            if ($request->payment_method === 'pix') {
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
                    $order->update([
                        'status' => 'paid',
                        'transaction_id' => (string) ($paymentResult['id'] ?? null),
                    ]);
                // Ativar plano no usuário
                $user->update([
                    'plan_id' => $plan->id,
                    'plan_expires_at' => $this->planExpiresAt($plan),
                ]);
                
                // Enviar email de confirmação
                try {
                    Mail::to($user)->send(new PaymentConfirmedMail($order));
                } catch (\Exception $e) {
                    \Log::error('Erro ao enviar email de pagamento: ' . $e->getMessage());
                }

                // Emitir fatura em PDF (email)
                app(InvoiceService::class)->issueAndQueueForOrder($order);
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

    private function planExpiresAt(Plan $plan): ?\Carbon\Carbon
    {
        $period = trim((string) ($plan->period ?? ''));
        $periodLower = Str::lower($period);

        if ($periodLower === 'vitalício' || $periodLower === 'vitalicio') {
            return null;
        }

        if (ctype_digit($period)) {
            return now()->addDays((int) $period);
        }

        return match ($periodLower) {
            'mensal' => now()->addMonth(),
            'trimestral' => now()->addMonths(3),
            'semestral' => now()->addMonths(6),
            'anual' => now()->addYear(),
            default => now()->addMonth(),
        };
    }
}
