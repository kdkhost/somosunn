<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderCancellationService;
use App\Services\OrderRefundService;
use App\Services\SalesAnalyticsService;
use Carbon\Carbon;
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

        if (request('sale_type')) {
            $query->saleType(request('sale_type'));
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('panel.admin.orders.index', [
            'orders' => $orders,
            'saleTypeLabels' => Order::SALE_TYPE_LABELS,
            'saleType' => request('sale_type'),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items', 'invoice']);

        return view('panel.admin.orders.show', compact('order'));
    }

    public function salesReport(Request $request, SalesAnalyticsService $salesAnalyticsService)
    {
        $search = trim((string) $request->input('search', ''));
        $saleType = trim((string) $request->input('sale_type', ''));
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));

        $from = $dateFrom !== '' ? Carbon::parse($dateFrom) : now()->startOfMonth();
        $to = $dateTo !== '' ? Carbon::parse($dateTo) : now()->endOfMonth();

        $report = $salesAnalyticsService->productSalesReport(
            $search,
            $saleType,
            $from,
            $to,
            20
        );

        return view('panel.admin.orders.sales_report', [
            'rows' => $report['rows'],
            'summary' => $report['summary'],
            'search' => $search,
            'saleType' => $saleType,
            'saleTypeLabels' => Order::SALE_TYPE_LABELS,
            'dateFrom' => $from->format('Y-m-d'),
            'dateTo' => $to->format('Y-m-d'),
        ]);
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

    public function cancel(Order $order, OrderCancellationService $orderCancellationService)
    {
        try {
            $order = $orderCancellationService->cancel($order);

            return back()->with('success', $order->status === 'refunded'
                ? 'Pedido pago estornado com sucesso.'
                : 'Pedido cancelado com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao cancelar pedido: ' . $e->getMessage());
        }
    }
}
