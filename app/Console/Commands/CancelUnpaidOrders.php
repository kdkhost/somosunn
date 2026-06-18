<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Setting;
use App\Services\EventExhibitorService;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\SumUpService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelUnpaidOrders extends Command
{
    protected $signature = 'orders:cancel-unpaid';
    protected $description = 'Cancels unpaid orders based on payment method deadlines (PIX = 24h, card = 48h)';

    public function handle()
    {
        $this->info('Starting checks for unpaid orders...');

        // Deadlines configuraveis via Settings (em horas)
        $pixCancelHours  = (int) (Setting::get('pix_cancel_hours', 24));
        $cardCancelHours = (int) (Setting::get('card_cancel_hours', 48));
        $defaultHours    = 48;

        // Otimizacao: so buscar pedidos pendentes criados ha mais de X horas (minimo possivel)
        $minHours = min($pixCancelHours, $cardCancelHours);
        $maxAge   = Carbon::now()->subHours($minHours);

        $count = 0;

        Order::where('status', 'pending')
            ->where('created_at', '<', $maxAge)
            ->with('items')
            ->chunkById(100, function ($orders) use ($pixCancelHours, $cardCancelHours, $defaultHours, &$count) {
                foreach ($orders as $order) {
                    $deadline = $this->computeDeadline($order, $pixCancelHours, $cardCancelHours, $defaultHours);

                    if ($order->created_at->lt($deadline)) {
                        $hours = $this->getHoursForMethod($order, $pixCancelHours, $cardCancelHours, $defaultHours);
                        $this->cancelOrder($order, $hours);
                        $count++;
                    }
                }
            });

        $this->info("Processed $count orders.");

        return self::SUCCESS;
    }

    /**
     * Calcula o deadline para o pedido considerando metodo de pagamento.
     * Retorna o timestamp limite: se created_at < deadline, o pedido expirou.
     */
    private function computeDeadline(Order $order, int $pixHours, int $cardHours, int $defaultHours): Carbon
    {
        $hours = $this->getHoursForMethod($order, $pixHours, $cardHours, $defaultHours);
        return Carbon::now()->subHours($hours);
    }

    /**
     * Retorna a quantidade de horas de tolerancia para o metodo de pagamento.
     */
    private function getHoursForMethod(Order $order, int $pixHours, int $cardHours, int $defaultHours): int
    {
        $paymentMethod = (string) ($order->payment_method ?? '');

        // PIX: usa pix_cancel_hours
        if (stripos($paymentMethod, 'pix') !== false) {
            return $pixHours;
        }

        // Cartao: usa card_cancel_hours
        if (stripos($paymentMethod, 'card') !== false
            || stripos($paymentMethod, 'credit') !== false
            || stripos($paymentMethod, 'debit') !== false) {
            return $cardHours;
        }

        // Boleto (ticket): 72h (3 dias)
        if (stripos($paymentMethod, 'ticket') !== false || stripos($paymentMethod, 'boleto') !== false) {
            return 72;
        }

        // Default: 48h
        return $defaultHours;
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
}
