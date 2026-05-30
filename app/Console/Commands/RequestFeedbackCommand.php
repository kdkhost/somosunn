<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Notifications\FeedbackRequestNotification;
use Illuminate\Console\Command;

class RequestFeedbackCommand extends Command
{
    protected $signature = 'feedback:request-daily';
    protected $description = 'Solicita feedback de compradores 14 dias após a compra';

    public function handle()
    {
        $this->info('Iniciando solicitação de feedback...');

        $fourteenDaysAgo = now()->subDays(14)->startOfDay();
        $today = now()->endOfDay();

        $orderItems = OrderItem::whereHas('order', function ($query) {
                $query->where('status', 'paid');
            })
            ->where('created_at', '>=', $fourteenDaysAgo)
            ->where('created_at', '<=', $today)
            ->whereDoesntHave('review')
            ->get();

        $count = 0;
        foreach ($orderItems as $orderItem) {
            $order = $orderItem->order;
            if (!$order || !$order->user) {
                continue;
            }

            Review::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'item_type' => $orderItem->item_type,
                'item_id' => $orderItem->item_id,
                'rating' => 5,
                'is_verified' => false,
                'is_approved' => true,
                'feedback_requested_at' => now(),
            ]);

            $order->user->notify(new FeedbackRequestNotification($orderItem));
            $count++;
        }

        $this->info("Solicitações de feedback enviadas: {$count}");
        return Command::SUCCESS;
    }
}
