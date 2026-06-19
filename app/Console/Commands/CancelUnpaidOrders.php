<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Setting;
use App\Services\EventExhibitorService;
use App\Services\OrderPendingPaymentNotificationService;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\SumUpService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelUnpaidOrders extends Command
{
    protected $signature = 'orders:cancel-unpaid';
    protected $description = 'Cancela pedidos nao pagos apos o prazo configurado e notifica o cliente';

    public function handle()
    {
        if ((int) Setting::get('cron_orders_cancel_enabled', 1) !== 1) {
            $this->info('Cron de cancelamento de pedidos nao pagos desativado.');

            return self::SUCCESS;
        }

        $this->info('Starting checks for unpaid orders...');

        $cancelAfterHours = $this->cancelAfterHours();
        $maxAge = Carbon::now($this->timezone())->subHours($cancelAfterHours);

        $count = 0;

        Order::where('status', 'pending')
            ->where('created_at', '<=', $maxAge)
            ->with(['items', 'user'])
            ->chunkById(100, function ($orders) use ($cancelAfterHours, &$count) {
                foreach ($orders as $order) {
                    if ($order->created_at && $order->created_at->lte(Carbon::now($this->timezone())->subHours($cancelAfterHours))) {
                        $this->cancelOrder($order, $cancelAfterHours);
                        $count++;
                    }
                }
            });

        $this->info("Processed $count orders.");

        return self::SUCCESS;
    }

    private function cancelOrder(Order $order, int $hours): void
    {
        $paymentMethod = (string) ($order->payment_method ?? 'unknown');

        $this->info("Cancelling Order #{$order->id} (gateway={$order->gateway}, method={$paymentMethod})");

        // 1. Cancelar no gateway
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

        // 2. Liberar cupons reservados
        $this->releaseCoupons($order);

        // 3. Cancelar inscricoes de evento associadas
        $this->releaseEventRegistrations($order);
        app(EventExhibitorService::class)->releaseOrder($order, 'expired');

        // 4. Atualizar pedido com metadados detalhados
        $cancelReason = $this->buildCancelReason($paymentMethod, $hours);

        $order->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'metadata'     => array_merge($order->metadata ?? [], [
                'cancelled_reason'  => $cancelReason,
                'cancelled_at_auto' => now()->toIso8601String(),
            ]),
        ]);

        app(\App\Services\OrderAccessRevocationService::class)->revoke(
            $order->fresh(['items', 'user']),
            'payment_window_expired'
        );

        $freshOrder = $order->fresh(['items', 'user']);
        if ($freshOrder) {
            $sent = app(OrderPendingPaymentNotificationService::class)
                ->sendAutoCancellation($freshOrder, $hours, $cancelReason);

            if ($sent) {
                $metadata = is_array($freshOrder->metadata) ? $freshOrder->metadata : [];
                $notifications = is_array($metadata['notifications'] ?? null) ? $metadata['notifications'] : [];
                $notifications['unpaid_auto_cancelled'] = [
                    'sent_at' => now($this->timezone())->toIso8601String(),
                ];
                $metadata['notifications'] = $notifications;
                $freshOrder->update(['metadata' => $metadata]);
            }
        }

        Log::info("Order #{$order->id} auto-cancelled", [
            'gateway'        => $order->gateway,
            'payment_method' => $paymentMethod,
            'reason'         => $cancelReason,
        ]);
    }

    /**
     * Libera cupons reservados associados ao pedido.
     */
    private function releaseCoupons(Order $order): void
    {
        \App\Models\CouponRedemption::where('order_id', $order->id)
            ->where('status', 'reserved')
            ->update([
                'status'         => 'cancelled',
                'reserved_until' => null,
            ]);
    }

    /**
     * Cancela inscricoes de evento associadas ao pedido.
     */
    private function releaseEventRegistrations(Order $order): void
    {
        foreach ($order->items as $item) {
            if (in_array((string) $item->item_type, ['event_registration', 'event'], true)) {
                \App\Models\EventRegistration::where('order_id', $order->id)
                    ->where('status', '!=', 'cancelled')
                    ->update(['status' => 'cancelled']);
                break; // Ja atualizou todos de uma vez
            }
        }
    }

    /**
     * Monta a razao de cancelamento com metodo e horas.
     */
    private function buildCancelReason(string $paymentMethod, int $hours): string
    {
        $method = strtolower(trim($paymentMethod)) ?: 'unknown';
        return "Auto-cancel: payment window expired ({$method}, {$hours}h)";
    }

    private function cancelAfterHours(): int
    {
        $hours = (int) Setting::get('orders_unpaid_cancel_after_hours', 24);

        return max(1, min(720, $hours));
    }

    private function timezone(): string
    {
        $timezone = trim((string) Setting::get('system_timezone', config('app.timezone', 'America/Sao_Paulo')));

        return $timezone !== '' ? $timezone : 'America/Sao_Paulo';
    }
}
