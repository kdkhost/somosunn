<?php

namespace App\Jobs;

use App\Http\Controllers\PaymentWebhookController;
use App\Models\Order;
use App\Models\Setting;
use App\Services\EventExhibitorService;
use App\Services\OrderAccessRevocationService;
use App\Services\Payment\MercadoPagoService;
use App\Services\Payment\SumUpWebhookProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $provider,
        public readonly array $payload = [],
        public readonly array $context = []
    ) {
    }

    public function handle(): void
    {
        match ($this->provider) {
            'mercadopago' => $this->handleMercadoPago(),
            'sumup' => $this->handleSumUp(),
            'efi' => $this->handleEfi(),
            default => Log::warning('ProcessPaymentWebhookJob: provedor desconhecido.', ['provider' => $this->provider]),
        };
    }

    private function handleMercadoPago(): void
    {
        $type = (string) ($this->payload['type'] ?? $this->payload['topic'] ?? '');
        $dataId = (string) ($this->payload['data']['id'] ?? $this->payload['id'] ?? $this->context['data_id'] ?? '');
        $sellerId = (string) ($this->context['seller_id'] ?? 'platform');

        if ($type === 'subscription_preapproval' || $type === 'preapproval') {
            if ($dataId === '') {
                return;
            }

            $mpService = app(MercadoPagoService::class);
            $data = $mpService->getPreapproval($dataId);
            $orderId = $data['external_reference'] ?? null;
            $status = (string) ($data['status'] ?? '');

            if (!$orderId || !in_array($status, ['authorized', 'active'], true)) {
                return;
            }

            $order = Order::find($orderId);
            if ($order) {
                app(PaymentWebhookController::class)->processPaidOrder($order, $dataId, $data);
            }

            return;
        }

        if ($type !== 'payment' || $dataId === '') {
            return;
        }

        $token = (string) config('payments.mercadopago.access_token');
        if ($token === '') {
            $env = (string) Setting::get('mercadopago_env', 'sandbox');
            $prefix = $env === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';
            $token = (string) Setting::get($prefix . 'access_token', Setting::get('mercadopago_access_token', ''));
        }

        if ($token === '') {
            Log::warning('MP Webhook Job: token ausente.', ['seller_id' => $sellerId, 'payment_id' => $dataId]);
            return;
        }

        $response = Http::withToken($token)->timeout(20)->get('https://api.mercadopago.com/v1/payments/' . $dataId);
        if (!$response->successful()) {
            Log::warning('MP Webhook Job: falha ao consultar pagamento.', [
                'payment_id' => $dataId,
                'status' => $response->status(),
            ]);
            return;
        }

        $data = (array) $response->json();
        $orderRef = (string) ($data['external_reference'] ?? '');
        $status = (string) ($data['status'] ?? '');

        if ($orderRef === '') {
            return;
        }

        $order = app(EventExhibitorService::class)->resolveOrderFromReference($orderRef);
        if (!$order) {
            return;
        }

        if ($status === 'approved') {
            app(PaymentWebhookController::class)->processPaidOrder($order, $dataId, $data);
            return;
        }

        if (in_array($status, ['rejected', 'cancelled', 'cancelled_by_collector', 'cancelled_by_user'], true)) {
            $order->update([
                'status' => 'failed',
                'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $data]),
            ]);
            app(EventExhibitorService::class)->releaseOrder($order, 'failed');
            return;
        }

        if (in_array($status, ['refunded', 'charged_back'], true)) {
            $order->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'metadata' => array_merge($order->metadata ?? [], ['webhook_data' => $data]),
            ]);
            app(EventExhibitorService::class)->markOrderRefunded($order, true);
            app(OrderAccessRevocationService::class)->revoke($order->fresh(['items', 'user']), 'gateway_refunded');
        }
    }

    private function handleSumUp(): void
    {
        $token = (string) ($this->context['token'] ?? '');
        if ($token !== '') {
            app(SumUpWebhookProcessor::class)->process($this->payload, $token);
            return;
        }

        $checkoutId = (string) ($this->payload['checkout_id'] ?? '');
        $transactionId = (string) ($this->payload['transaction_id'] ?? '');
        $eventType = (string) ($this->payload['event_type'] ?? '');

        if ($checkoutId === '' && $transactionId === '') {
            return;
        }

        $order = Order::query()
            ->when($checkoutId !== '', fn ($q) => $q->where('gateway_transaction_id', $checkoutId))
            ->when($transactionId !== '', fn ($q) => $q->orWhere('gateway_transaction_id', $transactionId))
            ->first();

        if (!$order) {
            return;
        }

        if ($eventType === 'CHECKOUT_PAID') {
            app(PaymentWebhookController::class)->processPaidOrder($order, $transactionId ?: $checkoutId, $this->payload);
        } elseif ($eventType === 'CHECKOUT_FAILED') {
            $order->update([
                'status' => 'failed',
                'metadata' => array_merge($order->metadata ?? [], ['sumup_webhook_data' => $this->payload]),
            ]);
        } elseif ($eventType === 'CHECKOUT_CANCELLED') {
            $order->update([
                'status' => 'cancelled',
                'metadata' => array_merge($order->metadata ?? [], ['sumup_webhook_data' => $this->payload]),
            ]);
            app(OrderAccessRevocationService::class)->revoke($order->fresh(['items', 'user']), 'gateway_cancelled');
        }
    }

    private function handleEfi(): void
    {
        $pixEvents = $this->payload['pix'] ?? [];
        if (!is_array($pixEvents) || $pixEvents === []) {
            return;
        }

        foreach ($pixEvents as $pixItem) {
            if (!is_array($pixItem)) {
                continue;
            }

            $txid = trim((string) ($pixItem['txid'] ?? ''));
            $e2eId = trim((string) ($pixItem['endToEndId'] ?? ''));
            if ($txid === '') {
                continue;
            }

            $order = Order::query()
                ->where('gateway', 'efi')
                ->where(function ($query) use ($txid) {
                    $query->where('gateway_transaction_id', $txid)
                        ->orWhere('transaction_id', $txid);
                })
                ->first();

            if (!$order) {
                Log::warning('Efi Webhook Job: pedido nao encontrado para txid.', ['txid' => $txid]);
                continue;
            }

            app(PaymentWebhookController::class)->processPaidOrder($order, $e2eId ?: $txid, $pixItem);
        }
    }
}
