<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\Payment\MercadoPagoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CancelUnpaidOrders extends Command
{
    protected $signature = 'orders:cancel-unpaid';
    protected $description = 'Cancels unpaid orders older than 48 hours';

    public function handle()
    {
        $this->info('Starting checks for unpaid orders...');

        $deadline = Carbon::now()->subHours(48);

        $orders = Order::where('status', 'pending')
            ->where('created_at', '<', $deadline)
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $this->cancelOrder($order);
            $count++;
        }

        $this->info("Processed $count orders.");
    }

    private function cancelOrder(Order $order)
    {
        $this->info("Cancelling Order #{$order->id}");

        try {
            if ($order->gateway === 'mercadopago' && $order->transaction_id) {
                $service = new MercadoPagoService();
                $service->cancelPayment($order);
            }
        } catch (\Exception $e) {
            Log::error("Failed to cancel payment for Order #{$order->id}: " . $e->getMessage());
            // We continue to cancel locally even if MP fails (e.g. expired)
        }

        // Cancel associated Event Registrations
        foreach ($order->items as $item) {
            if ($item->item_type === 'event_registration') {
                // Assuming item_id points to EventRegistration or we query by order_id
                // EventRegistration table has order_id
                \App\Models\EventRegistration::where('order_id', $order->id)
                    ->where('status', '!=', 'cancelled')
                    ->update(['status' => 'cancelled']);
            }
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'metadata' => array_merge($order->metadata ?? [], ['cancelled_reason' => 'System Auto-Cancel (Timeout)'])
        ]);

        Log::info("Order #{$order->id} auto-cancelled.");
    }
}
