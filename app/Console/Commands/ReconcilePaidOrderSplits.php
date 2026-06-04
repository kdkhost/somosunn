<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderSplitService;
use Illuminate\Console\Command;

class ReconcilePaidOrderSplits extends Command
{
    protected $signature = 'splits:reconcile-paid {--order= : Reconciliar somente um pedido}';

    protected $description = 'Recalcula e consolida os rateios de pedidos pagos';

    public function handle(OrderSplitService $splitService): int
    {
        $query = Order::query()->where('status', 'paid');
        $orderId = (int) $this->option('order');

        if ($orderId > 0) {
            $query->whereKey($orderId);
        }

        $count = 0;
        $query->orderBy('id')->chunkById(100, function ($orders) use ($splitService, &$count) {
            foreach ($orders as $order) {
                $splitService->syncForPaidOrder($order);
                $count++;
            }
        });

        $this->info("Rateios reconciliados para {$count} pedido(s).");

        return self::SUCCESS;
    }
}
