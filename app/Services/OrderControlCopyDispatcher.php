<?php

namespace App\Services;

use App\Jobs\SendOrderControlCopyEmailJob;
use App\Models\Order;
use App\Support\EmailQueueSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderControlCopyDispatcher
{
    public function dispatch(Order $order): void
    {
        if ((string) $order->status !== 'paid') {
            return;
        }

        $orderId = (int) $order->id;

        DB::afterCommit(function () use ($orderId): void {
            try {
                EmailQueueSettings::dispatch(new SendOrderControlCopyEmailJob($orderId));
            } catch (\Throwable $exception) {
                Log::error('order.control_copy.dispatch_failed', [
                    'order_id' => $orderId,
                    'error' => $exception->getMessage(),
                ]);
            }
        });
    }
}
