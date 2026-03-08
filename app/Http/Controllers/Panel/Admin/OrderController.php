<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderRefundService;
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

    public function refund(Request $request, Order $order, OrderRefundService $orderRefundService)
    {
        try {
            $amount = $this->parseRefundAmount($request);
            $order = $orderRefundService->refund($order, $amount);

            return back()->with('success', $order->is_fully_refunded
                ? 'Estorno total processado com sucesso.'
                : 'Estorno parcial processado com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar reembolso: ' . $e->getMessage());
        }
    }

    private function parseRefundAmount(Request $request): ?float
    {
        $rawAmount = trim((string) $request->input('amount', ''));
        if ($rawAmount === '') {
            return null;
        }

        $normalizedAmount = str_replace(',', '.', $rawAmount);
        if (!is_numeric($normalizedAmount)) {
            throw new \InvalidArgumentException('Valor de estorno invalido.');
        }

        return round((float) $normalizedAmount, 2);
    }
}
