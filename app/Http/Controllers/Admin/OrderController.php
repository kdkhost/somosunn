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
    public function approveManually(Order $order)
    {
        if ($order->status === 'paid') {
            return back()->with('error', 'Pedido já está pago.');
        }

        $manualEnabled = \App\Models\Setting::get('marketplace_manual_approval_enabled', 0);
        if (!$manualEnabled && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Aprovação manual desabilitada.');
        }

        $order->update([
            'status' => 'paid',
            'paid_at' => now(), // Important for stats
            'payment_method' => 'manual_approval',
            'metadata' => array_merge($order->metadata ?? [], ['manual_approver_id' => auth()->id()])
        ]);

        return back()->with('success', 'Pedido aprovado manualmente com sucesso (Permuta).');
    }

    public function cancel(Order $order)
    {
        if ($order->status === 'paid') {
            return $this->refund($order);
        }

        if ($order->status === 'cancelled') {
            return back()->with('error', 'Pedido já cancelado.');
        }

        if ($order->gateway === 'mercadopago' && $order->transaction_id) {
            try {
                $service = new \App\Services\Payment\MercadoPagoService();
                $service->cancelPayment($order);
            } catch (\Exception $e) {
                \Log::error('Erro ao cancelar no MP: ' . $e->getMessage());
                // Proceed with local cancellation
            }
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'metadata' => array_merge($order->metadata ?? [], ['cancelled_by' => auth()->id()])
        ]);

        return back()->with('success', 'Pedido cancelado.');
    }
}
