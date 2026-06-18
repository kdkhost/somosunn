<?php

namespace App\Services\Payment;

use App\Http\Controllers\PaymentWebhookController;
use App\Models\Order;
use App\Models\SumUpTransaction;
use App\Models\SumUpWebhookLog;
use App\Services\EventExhibitorService;
use Illuminate\Support\Facades\Log;

class SumUpWebhookProcessor
{
    public function __construct(
        private readonly PaymentWebhookController $webhookController
    ) {}

    /**
     * Dispatcher principal de eventos SumUp.
     */
    public function process(array $payload, string $webhookToken): void
    {
        $eventType = $payload['event_type'] ?? $payload['type'] ?? 'unknown';
        $orderId   = $payload['order_id'] ?? null;

        Log::info('SumUp webhook received', ['event' => $eventType, 'order_id' => $orderId]);

        match ($eventType) {
            'payment.succeeded', 'checkout.completed' => $this->handlePaymentSucceeded($payload, $webhookToken),
            'payment.failed'                          => $this->handlePaymentFailed($payload, $webhookToken),
            'payment.refunded'                        => $this->handlePaymentRefunded($payload, $webhookToken),
            'subscription.renewed'                    => $this->handleSubscriptionRenewed($payload, $webhookToken),
            'subscription.cancelled'                  => $this->handleSubscriptionCancelled($payload, $webhookToken),
            default                                   => Log::warning('SumUp: evento desconhecido', ['event' => $eventType]),
        };
    }

    // -------------------------------------------------------------------------
    // Handlers de eventos
    // -------------------------------------------------------------------------

    private function handlePaymentSucceeded(array $payload, string $webhookToken): void
    {
        $order = $this->findOrderByWebhookToken($webhookToken);
        if (!$order) {
            Log::error('SumUp payment.succeeded: order nao encontrado', ['token' => $webhookToken]);
            return;
        }

        // Idempotência: não reprocessar se já pago
        if ($this->isAlreadyProcessed($order, 'payment.succeeded')) {
            Log::info('SumUp payment.succeeded: ja processado', ['order_id' => $order->id]);
            return;
        }

        $transactionId = $payload['transaction_id']
            ?? $payload['id']
            ?? data_get($payload, 'transaction.id', '');

        // Atualiza SumUpTransaction
        SumUpTransaction::where('webhook_token', $webhookToken)->update([
            'status'         => 'PAID',
            'transaction_id' => $transactionId,
            'raw_response'   => $payload,
        ]);

        // Delega ao processador existente (mesmo fluxo do Mercado Pago)
        $this->webhookController->processPaidOrder($order, $transactionId, $payload);

        $this->markProcessed($order, 'payment.succeeded');
    }

    private function handlePaymentFailed(array $payload, string $webhookToken): void
    {
        $order = $this->findOrderByWebhookToken($webhookToken);
        if (!$order) {
            return;
        }

        SumUpTransaction::where('webhook_token', $webhookToken)->update([
            'status'       => 'FAILED',
            'raw_response' => $payload,
        ]);

        $order->update(['status' => 'failed']);
        app(EventExhibitorService::class)->releaseOrder($order, 'failed');

        // Notifica comprador
        if ($order->user) {
            $order->user->notify(new \App\Notifications\AppNotification([
                'message'      => 'Seu pagamento via SumUp falhou. Tente novamente.',
                'type'         => 'PaymentFailed',
                'action_url'   => route('panel.dashboard'),
                'action_label' => 'Ver pedidos',
            ]));
        }

        Log::info('SumUp payment.failed', ['order_id' => $order->id]);
    }

    private function handlePaymentRefunded(array $payload, string $webhookToken): void
    {
        $order = $this->findOrderByWebhookToken($webhookToken);
        if (!$order) {
            return;
        }

        SumUpTransaction::where('webhook_token', $webhookToken)->update([
            'status'       => 'REFUNDED',
            'raw_response' => $payload,
        ]);

        $order->update([
            'status' => 'refunded',
            'refunded_at' => now(),
            'metadata' => array_merge($order->metadata ?? [], [
                'sumup_refund_webhook_data' => $payload,
            ]),
        ]);

        app(EventExhibitorService::class)->markOrderRefunded($order, true);
        app(\App\Services\OrderAccessRevocationService::class)->revoke($order->fresh(['items', 'user']), 'gateway_refunded');

        Log::info('SumUp payment.refunded', ['order_id' => $order->id]);
    }

    private function handleSubscriptionRenewed(array $payload, string $webhookToken): void
    {
        $subscriptionId = $payload['subscription_id'] ?? null;
        if (!$subscriptionId) {
            return;
        }

        $subscription = \App\Models\Subscription::where('sumup_subscription_id', $subscriptionId)->first();
        if (!$subscription) {
            Log::warning('SumUp subscription.renewed: subscription nao encontrada', ['id' => $subscriptionId]);
            return;
        }

        // Renova a data de expiração
        $subscription->update([
            'expires_at' => now()->addMonth(),
            'status'     => 'active',
        ]);

        Log::info('SumUp subscription.renewed', ['subscription_id' => $subscriptionId]);
    }

    private function handleSubscriptionCancelled(array $payload, string $webhookToken): void
    {
        $subscriptionId = $payload['subscription_id'] ?? null;
        if (!$subscriptionId) {
            return;
        }

        $subscription = \App\Models\Subscription::where('sumup_subscription_id', $subscriptionId)->first();
        if ($subscription) {
            $subscription->update(['status' => 'cancelled']);
        }

        Log::info('SumUp subscription.cancelled', ['subscription_id' => $subscriptionId]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function findOrderByWebhookToken(string $token): ?Order
    {
        $transaction = SumUpTransaction::where('webhook_token', $token)->first();
        if (!$transaction) {
            return null;
        }

        return Order::find($transaction->order_id);
    }

    /**
     * Idempotência: verifica se o evento já foi processado para este pedido.
     */
    private function isAlreadyProcessed(Order $order, string $eventType): bool
    {
        if ($eventType === 'payment.succeeded' && (string) $order->status === 'paid') {
            return true;
        }

        return SumUpWebhookLog::where('order_id', $order->id)
            ->where('event_type', $eventType)
            ->where('is_valid', true)
            ->whereNotNull('processed_at')
            ->exists();
    }

    private function markProcessed(Order $order, string $eventType): void
    {
        SumUpWebhookLog::where('order_id', $order->id)
            ->where('event_type', $eventType)
            ->whereNull('processed_at')
            ->update(['processed_at' => now()]);
    }
}
