<?php

namespace App\Services;

use App\Models\Order;
use App\Services\Payment\MercadoPagoService;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OrderCancellationService
{
    private const AUTO_REFUND_GATEWAYS = ['mercadopago', 'sumup'];

    public function cancel(Order $order): Order
    {
        if ((string) $order->status === 'cancelled') {
            throw new RuntimeException('Pedido já cancelado.');
        }

        if ((string) $order->status === 'paid') {
            return $this->cancelPaidOrder($order);
        }

        if ((string) $order->status !== 'pending') {
            throw new RuntimeException('Apenas pedidos pendentes ou pagos podem ser cancelados.');
        }

        $this->cancelPendingGatewayPayment($order);

        return $this->markCancelled($order, 'pending_order_cancelled');
    }

    private function cancelPaidOrder(Order $order): Order
    {
        $remainingAmount = round((float) $order->remaining_refundable_amount, 2);

        if ($remainingAmount <= 0.009 || $this->canCancelWithoutGatewayRefund($order)) {
            return $this->markCancelled($order, 'paid_order_without_gateway_refund');
        }

        if (!$this->canRefundAutomatically($order)) {
            throw new RuntimeException('Este pedido pago não pode ser cancelado automaticamente. Use o estorno manual antes de cancelar.');
        }

        return app(OrderRefundService::class)->refund($order);
    }

    private function canRefundAutomatically(Order $order): bool
    {
        return in_array((string) $order->gateway, self::AUTO_REFUND_GATEWAYS, true)
            && !$order->is_manual_approval
            && !empty($order->transaction_id)
            && (float) $order->remaining_refundable_amount > 0.009;
    }

    private function canCancelWithoutGatewayRefund(Order $order): bool
    {
        return (string) $order->gateway === 'free'
            || (bool) $order->is_manual_approval
            || (bool) data_get($order->metadata, 'is_free_checkout');
    }

    private function cancelPendingGatewayPayment(Order $order): void
    {
        if ((string) $order->gateway !== 'mercadopago' || empty($order->transaction_id)) {
            return;
        }

        try {
            app(MercadoPagoService::class)->cancelPayment($order);
        } catch (\Throwable $e) {
            Log::error('Erro ao cancelar pagamento pendente no Mercado Pago: ' . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }
    }

    private function markCancelled(Order $order, string $reason): Order
    {
        $metadata = is_array($order->metadata) ? $order->metadata : [];
        $previousCancellation = is_array($metadata['cancellation'] ?? null) ? $metadata['cancellation'] : [];

        $metadata['cancellation'] = array_merge($previousCancellation, [
            'reason' => $reason,
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now()->toIso8601String(),
        ]);

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'metadata' => $metadata,
        ]);

        return $order->fresh(['user', 'items', 'invoice', 'manualApprover']);
    }
}
