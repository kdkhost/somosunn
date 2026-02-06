<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'items'])->latest()->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items', 'invoice']);
        return view('admin.orders.show', compact('order'));
    }

    public function refund(Order $order)
    {
        if ($order->status !== 'paid') {
            return back()->with('error', 'Apenas pedidos pagos podem ser reembolsados.');
        }

        try {
            $account = $order->gatewayAccount;
            if (!$account) {
                 // Fallback: try to find seller's account for this gateway
                 $account = \App\Models\GatewayAccount::where('user_id', $order->seller_id)
                    ->where('gateway', $order->gateway)
                    ->firstOrFail();
            }

            if ($order->gateway === 'mercadopago') {
                $service = new \App\Services\Payment\MercadoPagoService();
                $service->refundPayment($order, $account);
            } elseif ($order->gateway === 'pagseguro') {
                $service = new \App\Services\Payment\PagSeguroService();
                $service->refundPayment($order, $account);
            } else {
                return back()->with('error', 'Gateway não suportado para reembolso automático.');
            }

            $order->update([
                'status' => 'refunded',
                'refunded_at' => now()
            ]);

            return back()->with('success', 'Reembolso processado com sucesso.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar reembolso: ' . $e->getMessage());
        }
    }
}
