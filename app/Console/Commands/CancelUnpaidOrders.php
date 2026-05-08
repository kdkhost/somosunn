<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\SumUpService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelUnpaidOrders extends Command
{
    protected $signature = 'orders:cancel-unpaid';
    protected $description = 'Cancels unpaid orders based on payment method deadlines (PIX = configured minutes, card = 24h, default = 48h)';

    public function handle()
    {
        $this->info('Starting checks for unpaid orders...');

        // Deadlines configuráveis
        $mpPixMinutes = (int) (Setting::get('mercadopago_pix_expiration_minutes') ?? Setting::get('pix_expiration_minutes') ?? 10);
        $sumupPixMinutes = (int) (Setting::get('sumup_pix_expiration_minutes') ?? 10);
        $cardHours = 24;   // cartão: 24h padrão
        $defaultHours = 48; // sem método: 48h

        $orders = Order::where('status', 'pending')
            ->whereNotNull('created_at')
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $deadline = $this->computeDeadline($order, $mpPixMinutes, $sumupPixMinutes, $cardHours, $defaultHours);

            if ($order->created_at->lt($deadline)) {
                $this->cancelOrder($order);
                $count++;
            }
        }

        $this->info("Processed $count orders.");

        return self::SUCCESS;
    }

    /**
     * Calcula o deadline para o pedido considerando método de pagamento.
     */
    private function computeDeadline(Order $order, int $mpPixMin, int $sumupPixMin, int $cardHours, int $defaultHours): Carbon
    {
        $paymentMethod = (string) ($order->payment_method ?? '');
        $gateway = (string) ($order->gateway ?? '');

        // PIX: usa expiração configurada no gateway
        if (stripos($paymentMethod, 'pix') !== false) {
            $minutes = $gateway === 'sumup' ? $sumupPixMin : $mpPixMin;
            return Carbon::now()->subMinutes(max(1, $minutes));
        }

        // Cartão: 24h
        if (stripos($paymentMethod, 'card') !== false
            || stripos($paymentMethod, 'credit') !== false
            || stripos($paymentMethod, 'debit') !== false) {
            return Carbon::now()->subHours($cardHours);
        }

        // Boleto (ticket): 3 dias
        if (stripos($paymentMethod, 'ticket') !== false || stripos($paymentMethod, 'boleto') !== false) {
            return Carbon::now()->subDays(3);
        }

        // Default: 48h
        return Carbon::now()->subHours($defaultHours);
    }

    private function cancelOrder(Order $order): void
    {
        $this->info("Cancelling Order #{$order->id} (gateway={$order->gateway}, method={$order->payment_method})");

        try {
            if ($order->gateway === 'mercadopago' && $order->transaction_id) {
                $service = app(MercadoPagoService::class);
                $service->cancelPayment($order);
            } elseif ($order->gateway === 'sumup') {
                $checkoutId = data_get($order->metadata, 'sumup_checkout_id');
                if ($checkoutId) {
                    try {
                        $service = app(SumUpService::class);
                        if (method_exists($service, 'cancelCheckout')) {
                            $service->cancelCheckout($checkoutId);
                        }
                    } catch (\Throwable $e) {
                        Log::warning("SumUp cancel failed for Order #{$order->id}: " . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to cancel payment for Order #{$order->id}: " . $e->getMessage());
            // Continua cancelando localmente mesmo se gateway falhar
        }

        // Cancelar inscrições de evento associadas
        foreach ($order->items as $item) {
            if ($item->item_type === 'event_registration') {
                \App\Models\EventRegistration::where('order_id', $order->id)
                    ->where('status', '!=', 'cancelled')
                    ->update(['status' => 'cancelled']);
            }
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'metadata' => array_merge($order->metadata ?? [], [
                'cancelled_reason' => 'Auto-cancel: payment window expired',
                'cancelled_at_auto' => now()->toIso8601String(),
            ])
        ]);

        Log::info("Order #{$order->id} auto-cancelled due to expired payment window.");
    }
}
