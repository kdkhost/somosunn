<?php

namespace App\Services\Payment;

use App\Models\GatewayAccount;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SumUpService
{
    protected string $baseUrl = 'https://api.sumup.com';

    // ─────────────────────────────────────────
    // API Key Resolution
    // ─────────────────────────────────────────

    /**
     * Retorna a API Key da plataforma (settings).
     */
    public function platformApiKey(): string
    {
        $key = trim((string) Setting::get('sumup_access_token', ''));
        if ($key === '') {
            throw new Exception('SumUp não configurado. Adicione a API Key nas configurações de Gateway.');
        }
        return $key;
    }

    /**
     * Resolve a API Key para um pedido: usa a do vendedor (GatewayAccount) ou da plataforma.
     */
    public function resolveApiKey(Order $order): string
    {
        if ($order->seller_id) {
            $account = GatewayAccount::where('user_id', $order->seller_id)
                ->where('provider', 'sumup')
                ->where('enabled', true)
                ->first();

            if ($account && !empty($account->access_token)) {
                return trim($account->access_token);
            }
        }

        return $this->platformApiKey();
    }

    // ─────────────────────────────────────────
    // Validation
    // ─────────────────────────────────────────

    /**
     * Testa a chave consultando /v0.1/me.
     * Retorna os dados do merchant ou lança Exception.
     */
    public function validateApiKey(string $apiKey): array
    {
        $response = Http::withToken($apiKey)
            ->get("{$this->baseUrl}/v0.1/me");

        if ($response->failed()) {
            $error = $response->json('message') ?? $response->json('error_message') ?? 'Chave inválida ou sem permissão.';
            throw new Exception("SumUp: {$error}");
        }

        return (array) $response->json();
    }

    // ─────────────────────────────────────────
    // Checkout (Cartão via Hosted Page)
    // ─────────────────────────────────────────

    /**
     * Cria um checkout hosted na SumUp e retorna dados com o ID e a URL de redirect.
     */
    public function createCheckout(Order $order, ?string $apiKey = null): array
    {
        $apiKey = $apiKey ?? $this->resolveApiKey($order);
        $order->loadMissing('items', 'user');

        $description = $this->orderDescription($order);

        $payload = [
            'checkout_reference' => 'order-' . $order->id,
            'amount'            => round((float) $order->total_amount, 2),
            'currency'          => 'BRL',
            'description'       => $description,
            'return_url'        => route('checkout.success', $order),
            'merchant_code'     => $this->getMerchantCode($apiKey),
        ];

        // URL de notificação (webhook)
        if (app()->environment('production')) {
            $payload['redirect_url'] = route('api.webhooks.sumup');
        }

        $response = Http::withToken($apiKey)
            ->post("{$this->baseUrl}/v0.1/checkouts", $payload);

        if ($response->failed()) {
            $error = $response->json('message') ?? $response->body();
            Log::error('SumUp createCheckout failed', [
                'order_id' => $order->id,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            throw new Exception("SumUp Checkout: {$error}");
        }

        $data = (array) $response->json();

        return [
            'id'           => $data['id'],
            'status'       => $data['status'] ?? 'pending',
            'checkout_url' => "https://pay.sumup.com/b2c/pay?checkout_id={$data['id']}",
            'raw'          => $data,
        ];
    }

    /**
     * Consulta o status de um checkout existente.
     */
    public function getCheckout(string $checkoutId, ?string $apiKey = null): array
    {
        $apiKey = $apiKey ?? $this->platformApiKey();

        $response = Http::withToken($apiKey)
            ->get("{$this->baseUrl}/v0.1/checkouts/{$checkoutId}");

        if ($response->failed()) {
            throw new Exception('SumUp: falha ao buscar checkout: ' . $response->body());
        }

        return (array) $response->json();
    }

    // ─────────────────────────────────────────
    // Cobrança Recorrente (Subscriptions)
    // ─────────────────────────────────────────

    /**
     * Cria um plano de assinatura recorrente na SumUp.
     * Retorna os dados do plano criado.
     */
    public function createSubscriptionPlan(array $data, ?string $apiKey = null): array
    {
        $apiKey = $apiKey ?? $this->platformApiKey();

        $interval = $this->mapPeriodToInterval($data['period'] ?? 'monthly');

        $payload = [
            'name'          => $data['name'],
            'amount'        => round((float) $data['price'] * 100), // SumUp usa centavos
            'currency'      => 'BRL',
            'interval_type' => $interval,
            'interval'      => (int) ($data['billing_cycle'] ?? 1),
        ];

        $response = Http::withToken($apiKey)
            ->post("{$this->baseUrl}/v0.1/subscriptions/plans", $payload);

        if ($response->failed()) {
            throw new Exception('SumUp create plan: ' . $response->body());
        }

        return (array) $response->json();
    }

    /**
     * Assina um usuário em um plano recorrente.
     */
    public function subscribeUser(string $planId, array $userData, ?string $apiKey = null): array
    {
        $apiKey = $apiKey ?? $this->platformApiKey();

        $payload = [
            'plan_id'  => $planId,
            'email'    => $userData['email'],
            'name'     => $userData['name'] ?? '',
            'metadata' => [
                'order_id'           => $userData['external_reference'] ?? '',
                'external_reference' => $userData['external_reference'] ?? '',
            ],
        ];

        $response = Http::withToken($apiKey)
            ->post("{$this->baseUrl}/v0.1/subscriptions", $payload);

        if ($response->failed()) {
            throw new Exception('SumUp subscribe: ' . $response->body());
        }

        return (array) $response->json();
    }

    /**
     * Cancela uma assinatura ativa.
     */
    public function cancelSubscription(string $subscriptionId, ?string $apiKey = null): array
    {
        $apiKey = $apiKey ?? $this->platformApiKey();

        $response = Http::withToken($apiKey)
            ->delete("{$this->baseUrl}/v0.1/subscriptions/{$subscriptionId}");

        if ($response->failed() && $response->status() !== 404) {
            throw new Exception('SumUp cancel subscription: ' . $response->body());
        }

        return ['status' => 'cancelled', 'id' => $subscriptionId];
    }

    // ─────────────────────────────────────────
    // Helpers Internos
    // ─────────────────────────────────────────

    private function getMerchantCode(string $apiKey): string
    {
        try {
            $me = $this->validateApiKey($apiKey);
            return $me['merchant_profile']['merchant_code'] ?? '';
        } catch (\Throwable) {
            return '';
        }
    }

    private function orderDescription(Order $order): string
    {
        $firstItem = optional($order->items->first())->title;
        if (!$firstItem) {
            return 'Pedido #' . $order->id . ' - ' . config('app.name');
        }
        $extra = max(0, $order->items->count() - 1);
        $desc = $extra > 0 ? "{$firstItem} + {$extra} item(ns)" : $firstItem;
        return substr("{$desc} - Pedido #{$order->id}", 0, 200);
    }

    private function mapPeriodToInterval(string $period): string
    {
        return match (strtolower($period)) {
            'day', 'days', 'diário', 'diario' => 'day',
            'week', 'weeks', 'semanal'         => 'week',
            'month', 'months', 'mensal'        => 'month',
            'year', 'years', 'anual'            => 'year',
            default                             => 'month',
        };
    }
}
