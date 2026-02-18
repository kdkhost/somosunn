<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartEmails extends Command
{
    protected $signature = 'orders:abandoned-cart';
    protected $description = 'Sends email reminders for unpaid orders (abandoned carts) > 24h';

    public function handle()
    {
        $this->info('Checking for abandoned carts...');

        $startWindow = Carbon::now()->subHours(48);
        $endWindow = Carbon::now()->subHours(24);

        // Find orders created between 24h and 48h ago, pending, and not notified yet
        $orders = Order::where('status', 'pending')
            ->whereBetween('created_at', [$startWindow, $endWindow])
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $metadata = $order->metadata ?? [];
            if (isset($metadata['abandoned_email_sent_at'])) {
                continue;
            }

            $this->sendEmail($order);

            $metadata['abandoned_email_sent_at'] = now();
            $order->update(['metadata' => $metadata]);
            $count++;
        }

        $this->info("Sent $count reminder emails.");
    }

    private function sendEmail(Order $order)
    {
        try {
            Mail::to($order->user->email)->send(new \App\Mail\OrderAbandonedCart($order));
            Log::info("Sent Abandoned Cart Email to {$order->user->email} for Order #{$order->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send email for Order #{$order->id}: " . $e->getMessage());
        }
    }
}
