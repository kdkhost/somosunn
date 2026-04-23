<?php

namespace App\Services;

use App\Models\Order;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\SumUpService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderRefundService
{
    public function refund(Order $order, ?float $amount = null): Order
    {
        if ((string) $order->status !== 'paid') {
            throw new RuntimeException('Apenas pedidos pagos podem ser reembolsados.');
        }

        $remainingAmount = round((float) $order->remaining_refundable_amount, 2);
        if ($remainingAmount <= 0) {
            throw new RuntimeException('Nao ha saldo disponivel para estorno neste pedido.');
        }

        $requestedAmount = $amount !== null ? round($amount, 2) : $remainingAmount;
        if ($requestedAmount <= 0) {
            throw new RuntimeException('Informe um valor de estorno maior que zero.');
        }

        if ($requestedAmount > $remainingAmount) {
            throw new RuntimeException('O valor solicitado excede o saldo disponivel para estorno.');
        }

        $response = $this->refundOnGateway($order, $requestedAmount, $amount !== null);
        $processedAmount = $this->resolveProcessedAmount($response, $requestedAmount);

        return DB::transaction(function () use ($order, $requestedAmount, $processedAmount, $response) {
            $order->refresh();

            $now = now();
            $chargedAmount = round((float) $order->charged_amount, 2);
            $previousRefundedAmount = round((float) $order->refunded_amount, 2);
            $newRefundedAmount = min($chargedAmount, round($previousRefundedAmount + $processedAmount, 2));
            $isFullyRefunded = $newRefundedAmount >= ($chargedAmount - 0.009);

            $metadata = is_array($order->metadata) ? $order->metadata : [];
            $refunds = is_array($metadata['refunds'] ?? null) ? $metadata['refunds'] : [];
            $history = is_array($refunds['history'] ?? null) ? $refunds['history'] : [];

            $history[] = [
                'gateway' => (string) $order->gateway,
                'type' => $isFullyRefunded ? ($previousRefundedAmount > 0 ? 'remaining' : 'full') : 'partial',
                'requested_amount' => $requestedAmount,
                'amount' => $processedAmount,
                'processed_at' => $now->toIso8601String(),
                'processed_by' => auth()->id(),
                'gateway_refund_id' => (string) (data_get($response, 'id') ?? data_get($response, 'refund_id') ?? ''),
                'gateway_response' => $response,
            ];

            $metadata['refunds'] = [
                'charged_amount' => $chargedAmount,
                'total_amount' => $newRefundedAmount,
                'last_amount' => $processedAmount,
                'last_requested_amount' => $requestedAmount,
                'last_refunded_at' => $now->toIso8601String(),
                'last_refunded_by' => auth()->id(),
                'history' => $history,
            ];

            $order->metadata = $metadata;
            $order->status = $isFullyRefunded ? 'refunded' : 'paid';
            $order->refunded_at = $isFullyRefunded ? $now : null;
            $order->save();

            return $order->fresh(['user', 'items', 'invoice', 'manualApprover']);
        });
    }

    private function refundOnGateway(Order $order, float $requestedAmount, bool $isPartial): array
    {
        return match ((string) $order->gateway) {
            'mercadopago' => app(\App\Services\Payment\MercadoPagoService::class)
                ->refundPayment($order, $isPartial ? $requestedAmount : null),
            'sumup' => app(SumUpService::class)
                ->refundPayment($order, $isPartial ? $requestedAmount : null),
            default => throw new RuntimeException('Gateway nao suportado para reembolso automatico.'),
        };
    }

    private function resolveProcessedAmount(array $response, float $fallbackAmount): float
    {
        $amount = data_get($response, 'amount');
        if (!is_numeric($amount) || (float) $amount <= 0) {
            $amount = data_get($response, 'transaction_amount');
        }

        if (!is_numeric($amount) || (float) $amount <= 0) {
            $amount = $fallbackAmount;
        }

        return round((float) $amount, 2);
    }
}
