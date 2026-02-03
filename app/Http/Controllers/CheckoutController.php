<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Order;
use App\Models\GatewayAccount;
use App\Services\Payment\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function show(Course $course)
    {
        // Check if seller has gateway configured
        $gateway = GatewayAccount::where('user_id', $course->user_id)
            ->where('enabled', true)
            ->first();

        if (!$gateway) {
            return back()->with('error', 'Este criador ainda não configurou o recebimento de pagamentos.');
        }

        return view('checkout.index', compact('course', 'gateway'));
    }

    public function process(Request $request, Course $course, MercadoPagoService $mpService)
    {
        $gateway = GatewayAccount::where('user_id', $course->user_id)
            ->where('enabled', true)
            ->firstOrFail();

        // Create Order
        $order = Order::create([
            'user_id' => Auth::id(),
            'seller_id' => $course->user_id,
            'status' => 'pending',
            'total_amount' => $course->price,
            'fee_amount' => 0, // Calculate later
            'platform_fee_amount' => 0, // Calculate later
            'currency' => 'BRL',
            'gateway' => $gateway->provider,
            'gateway_account_id' => $gateway->id,
        ]);

        $order->items()->create([
            'item_type' => 'course',
            'item_id' => $course->id,
            'title' => $course->title,
            'price' => $course->price,
            'quantity' => 1,
        ]);

        try {
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
