<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\SumUpTransaction;
use App\Services\Payment\SumUpService;
use Illuminate\Console\Command;

class ReconcilePendingSumUpOrders extends Command
{
    protected $signature = 'sumup:reconcile-pending
        {--limit=100 : Quantidade maxima de pedidos pendentes a verificar}
        {--order_id= : Verifica somente um pedido especifico}';

    protected $description = 'Reconcilia pedidos SumUp pendentes consultando a API oficial';

    public function handle(SumUpService $sumUpService): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $orderId = $this->option('order_id');

        $orderIds = $orderId
            ? collect([(int) $orderId])
            : SumUpTransaction::query()
                ->where('status', 'PENDING')
                ->whereHas('order', function ($query) {
                    $query->where('gateway', 'sumup')
                        ->where('status', 'pending');
                })
                ->orderByDesc('id')
                ->limit($limit)
                ->pluck('order_id')
                ->unique()
                ->values();

        $checked = 0;
        $paid = 0;

        foreach ($orderIds as $id) {
            $order = Order::find($id);
            if (!$order) {
                continue;
            }

            $checked++;
            $result = $sumUpService->reconcileOrderTransactions($order);

            if ($result['paid'] ?? false) {
                $paid++;
                $this->info("Pedido #{$order->id} conciliado como pago.");
            }
        }

        $this->info("Pedidos verificados: {$checked}. Pagos conciliados: {$paid}.");

        return self::SUCCESS;
    }
}
