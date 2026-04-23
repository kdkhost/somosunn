<?php

namespace App\Services\Payment;

use App\Models\GatewayAccount;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SumUpTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class SumUpService
{
    private const BASE_URL = 'https://api.sumup.com';

    // -------------------------------------------------------------------------
    // Checkout (pagamento único)
    // -------------------------------------------------------------------------

    /**
     * Cria um checkout SumUp e registra a SumUpTransaction.
     * Também registra o webhook dinâmico para esta transação.
     */
    public function createCheckout(Order $order, array $options = []): array
    {
        $config      = $this->getSellerConfig($order);
        $webhookToken = Str::random(64);
        $webhookUrl   = $this->buildWebhookUrl($order->id, $webhookToken);

        $payload = [
            'checkout_reference' => 'ORDER-' . $order->id . '-' . time(),
            'amount'             => (float) $order->total_amount,
            'currency'           => $order->currency ?? 'BRL',
            'merchant_code'      => $config['merchant_code'],
            'description'        => $this->orderDescription($order),
            'return_url'         => $options['return_url'] ?? route('checkout.success', $order),
        ];

        $response = $this->post('/v0.1/checkouts', $payload, $config['api_key']);

        if (empty($response['id'])) {
            throw new RuntimeException('SumUp: falha ao criar checkout. ' . json_encode($response));
        }

        // Registra webhook dinâmico
        $this->registerWebhook($order, $webhookToken, $config['api_key']);

        // Persiste a transação
        SumUpTransaction::create([
            'order_id'      => $order->id,
            'checkout_id'   => $response['id'],
            'status'        => 'PENDING',
            'payment_type'  => strtoupper($options['payment_type'] ?? 'CARD'),
            'amount'        => $order->total_amount,
            'currency'      => $order->currency ?? 'BRL',
            'webhook_token' => $webhookToken,
            'webhook_url'   => $webhookUrl,
            'raw_response'  => $response,
        ]);

        return [
            'checkout_id'   => $response['id'],
            'webhook_token' => $webhookToken,
            'raw'           => $response,
        ];
    }

    /**
     * Processa pagamento com token de cartão (SumUp.js).
     */
    public function processCardCheckout(string $checkoutId, string $cardToken, string $apiKey): array
    {
        $payload = [
            'payment_type' => 'card',
            'card'         => ['token' => $cardToken],
        ];

        $response = $this->put("/v0.1/checkouts/{$checkoutId}", $payload, $apiKey);

        $this->updateTransactionStatus($checkoutId, $response);

        return $response;
    }

    /**
     * Cria checkout PIX e retorna QR Code.
     */
    public function processPixCheckout(Order $order): array
    {
        $config   = $this->getSellerConfig($order);
        $checkout = $this->createCheckout($order, ['payment_type' => 'PIX']);

        $payload = ['payment_type' => 'boleto'];

        $response = $this->put("/v0.1/checkouts/{$checkout['checkout_id']}", $payload, $config['api_key']);

        $qrCode   = data_get($response, 'transaction_code') ?? data_get($response, 'pix.qr_code', '');
        $copyPaste = data_get($response, 'pix.copy_paste', '');

        return [
            'checkout_id' => $checkout['checkout_id'],
            'qr_code'     => $qrCode,
            'copy_paste'  => $copyPaste,
            'raw'         => $response,
        ];
    }

    /**
     * Consulta status de um checkout.
     */
    public function getCheckout(string $checkoutId, string $apiKey): array
    {
        return $this->get("/v0.1/checkouts/{$checkoutId}", $apiKey);
    }

    // -------------------------------------------------------------------------
    // Reembolso
    // -------------------------------------------------------------------------

    /**
     * Reembolsa um pagamento SumUp (total ou parcial).
     */
    public function refundPayment(Order $order, ?float $amount = null): array
    {
        $config        = $this->getSellerConfig($order);
        $transactionId = $order->transaction_id;

        if (empty($transactionId)) {
            throw new RuntimeException('SumUp: transaction_id nao encontrado no pedido #' . $order->id);
        }

        $payload = [];
        if ($amount !== null) {
            $payload['amount'] = round($amount, 2);
        }

        $response = $this->post("/v0.1/me/refund/{$transactionId}", $payload, $config['api_key']);

        Log::info('SumUp refund', ['order_id' => $order->id, 'amount' => $amount, 'response' => $response]);

        return $response;
    }

    // -------------------------------------------------------------------------
    // Assinaturas
    // -------------------------------------------------------------------------

    public function createSubscription(Order $order, array $userData): array
    {
        $config  = $this->getSellerConfig($order);
        $payload = [
            'checkout_reference' => 'SUB-' . $order->id . '-' . time(),
            'amount'             => (float) $order->total_amount,
            'currency'           => $order->currency ?? 'BRL',
            'merchant_code'      => $config['merchant_code'],
            'description'        => $this->orderDescription($order),
            'customer_id'        => (string) $order->user_id,
        ];

        return $this->post('/v0.1/subscriptions', $payload, $config['api_key']);
    }

    public function cancelSubscription(string $subscriptionId, string $apiKey): array
    {
        return $this->delete("/v0.1/subscriptions/{$subscriptionId}", $apiKey);
    }

    public function getSubscription(string $subscriptionId, string $apiKey): array
    {
        return $this->get("/v0.1/subscriptions/{$subscriptionId}", $apiKey);
    }

    // -------------------------------------------------------------------------
    // Webhook dinâmico
    // -------------------------------------------------------------------------

    /**
     * Registra URL de webhook dinâmica na API SumUp para esta transação.
     */
    public function registerWebhook(Order $order, string $token, string $apiKey): string
    {
        $url = $this->buildWebhookUrl($order->id, $token);

        $payload = [
            'url'        => $url,
            'event_types' => [
                'payment.succeeded',
                'payment.failed',
                'payment.refunded',
                'checkout.completed',
                'subscription.renewed',
                'subscription.cancelled',
            ],
        ];

        $this->post('/v0.1/me/webhooks', $payload, $apiKey);

        return $url;
    }

    /**
     * Valida assinatura HMAC do webhook.
     */
    public function validateWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    // -------------------------------------------------------------------------
    // Validação de credenciais
    // -------------------------------------------------------------------------

    public function validateCredentials(?int $userId = null): bool
    {
        try {
            $apiKey = $userId
                ? $this->getApiKeyForUser($userId)
                : $this->apiKey();

            if (empty($apiKey)) {
                return false;
            }

            $response = $this->get('/v0.1/me', $apiKey);
            return !empty($response['merchant_code'] ?? $response['username'] ?? null);
        } catch (\Throwable $e) {
            Log::warning('SumUp validateCredentials failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function getSellerConfig(Order $order): array
    {
        // Tenta credenciais do vendedor primeiro
        if ($order->seller_id) {
            $account = GatewayAccount::where('user_id', $order->seller_id)
                ->where('provider', 'sumup')
                ->where('enabled', true)
                ->first();

            if ($account && !empty($account->access_token)) {
                $extra = $account->extra ?? [];
                return [
                    'api_key'       => $account->access_token,
                    'merchant_code' => $extra['merchant_code'] ?? $this->merchantCode(),
                    'source'        => 'seller',
                ];
            }
        }

        // Fallback para credenciais globais
        return [
            'api_key'       => $this->apiKey(),
            'merchant_code' => $this->merchantCode(),
            'source'        => 'global',
        ];
    }

    private function getApiKeyForUser(int $userId): string
    {
        $account = GatewayAccount::where('user_id', $userId)
            ->where('provider', 'sumup')
            ->where('enabled', true)
            ->first();

        return $account?->access_token ?? $this->apiKey();
    }

    private function apiKey(): string
    {
        return (string) (Setting::get('sumup_api_key') ?: config('payments.sumup.api_key', ''));
    }

    private function merchantCode(): string
    {
        return (string) (Setting::get('sumup_merchant_code') ?: config('payments.sumup.merchant_code', ''));
    }
    private function buildWebhookUrl(int $orderId, string $token): string
    {
        return url("/webhook/sumup/{$orderId}/{$token}");
    }

    private function orderDescription(Order $order): string
    {
        $items = $order->items->pluck('name')->implode(', ');
        return $items ?: 'Pedido #' . $order->id;
    }

    private function headers(string $apiKey): array
    {
        return [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    private function post(string $endpoint, array $data, string $apiKey): array
    {
        $url      = self::BASE_URL . $endpoint;
        $response = Http::withHeaders($this->headers($apiKey))
            ->post($url, $data);

        Log::debug('SumUp POST', [
            'endpoint' => $endpoint,
            'status'   => $response->status(),
        ]);

        return $response->json() ?? [];
    }

    private function put(string $endpoint, array $data, string $apiKey): array
    {
        $url      = self::BASE_URL . $endpoint;
        $response = Http::withHeaders($this->headers($apiKey))
            ->put($url, $data);

        Log::debug('SumUp PUT', [
            'endpoint' => $endpoint,
            'status'   => $response->status(),
        ]);

        return $response->json() ?? [];
    }

    private function get(string $endpoint, string $apiKey): array
    {
        $url      = self::BASE_URL . $endpoint;
        $response = Http::withHeaders($this->headers($apiKey))
            ->get($url);

        Log::debug('SumUp GET', [
            'endpoint' => $endpoint,
            'status'   => $response->status(),
        ]);

        return $response->json() ?? [];
    }

    private function delete(string $endpoint, string $apiKey): array
    {
        $url      = self::BASE_URL . $endpoint;
        $response = Http::withHeaders($this->headers($apiKey))
            ->delete($url);

        return $response->json() ?? [];
    }

    private function updateTransactionStatus(string $checkoutId, array $response): void
    {
        $status = match (strtoupper($response['status'] ?? '')) {
            'PAID', 'SUCCESSFUL' => 'PAID',
            'FAILED'             => 'FAILED',
            default              => 'PENDING',
        };

        SumUpTransaction::where('checkout_id', $checkoutId)->update([
            'status'         => $status,
            'transaction_id' => $response['transaction_id'] ?? $response['id'] ?? null,
            'raw_response'   => $response,
        ]);
    }
}
