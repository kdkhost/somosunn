<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\GatewayAccount;
use App\Services\Payment\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use App\Mail\PaymentConfirmedMail;

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

        return view('site.subscription.checkout', compact('plan'));
    }

    public function process(Request $request, Plan $plan)
    {
        $request->validate([
            'payment_method' => 'required|in:credit_card,pix',
            // Dados pessoais (se não logado)
            'name' => Auth::check() ? 'nullable' : 'required|string|max:255',
            'email' => Auth::check() ? 'nullable' : 'required|email|unique:users,email',
            'cpf' => Auth::check() ? 'nullable' : 'required|string', // Validar CPF se possível
            'password' => Auth::check() ? 'nullable' : 'required|min:8|confirmed',
            // Cartão de crédito
            'token' => 'required_if:payment_method,credit_card',
            'installments' => 'required_if:payment_method,credit_card',
            'payment_method_id' => 'required_if:payment_method,credit_card',
            'issuer_id' => 'required_if:payment_method,credit_card',
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
                    'is_active' => true, // Ativo para acesso básico
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

            // ...

            if ($paymentResult['status'] === 'approved') {
                $order->update(['status' => 'paid', 'paid_at' => now()]);
                // Ativar plano no usuário
                $user->update([
                    'plan_id' => $plan->id,
                    'plan_expires_at' => now()->addDays($plan->period ?? 30)
                ]);
                
                // Enviar email de confirmação
                try {
                    Mail::to($user)->send(new PaymentConfirmedMail($order));
                } catch (\Exception $e) {
                    \Log::error('Erro ao enviar email de pagamento: ' . $e->getMessage());
                }
            } else if ($paymentResult['status'] === 'pending') {
                // Pix ou pendente
                $order->update(['transaction_id' => $paymentResult['id']]);
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
        return view('site.subscription.success', compact('order'));
    }
}
