<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $query = Order::with(['user', 'items']);

        if (request('search')) {
            $term = request('search');
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', "%{$term}%")
                    ->orWhere('transaction_id', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($u) use ($term) {
                        $u->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
            });
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('panel.admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items', 'invoice']);
        return view('panel.admin.orders.show', compact('order'));
    }

    public function refund(Order $order)
    {
        if ($order->status !== 'paid') {
            return back()->with('error', 'Apenas pedidos pagos podem ser reembolsados.');
        }

        try {
            if ($order->gateway === 'mercadopago') {
                $service = new \App\Services\Payment\MercadoPagoService();
                $service->refundPayment($order);
            } elseif ($order->gateway === 'pagseguro') {
                $service = new \App\Services\Payment\PagSeguroService();
                $service->refundPayment($order);
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
