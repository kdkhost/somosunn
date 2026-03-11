<?php

namespace App\Services\Payment;

use App\Models\GatewayAccount;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SumUpService
{
    protected string $baseUrl = 'https://api.sumup.com';

    // ─────────────────────────────────────────
    // OAuth 2.0 — Client Credentials Flow
    // ─────────────────────────────────────────

    /**
     * Obtém um access token via Client Credentials.
     * O token é cacheado por 55 minutos (SumUp tokens duram 60 min).
     */
    public function getOAuthToken(): string
    {
        $clientId     = trim((string) Setting::get('sumup_client_id', ''));
        $clientSecret = trim((string) Setting::get('sumup_client_secret', ''));

        if ($clientId === '' || $clientSecret === '') {
            // Fallback para Personal API Key
            return $this->platformApiKey();
        }

        $cacheKey = 'sumup_oauth_token_' . md5($clientId);

        return Cache::remember($cacheKey, 55 * 60, function () use ($clientId, $clientSecret) {
            $response = Http::asForm()->post("{$this->baseUrl}/token", [
                'grant_type'    => 'client_credentials',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if ($response->failed()) {
                $error = $response->json('error_description')
                    ?? $response->json('error')
                    ?? $response->body();
                throw new Exception("SumUp OAuth falhou: {$error}");
            }

            $token = $response->json('access_token');
            if (empty($token)) {
                throw new Exception('SumUp OAuth: access_token não retornado.');
            }

            Log::info('SumUp OAuth token renovado com sucesso.');
            return $token;
        });
    }

    // ─────────────────────────────────────────
    // API Key Resolution
    // ─────────────────────────────────────────

    /**
     * Personal API Key (sup_pk_*) — fallback quando não há OAuth.
     */
    public function platformApiKey(): string
    {
        $key = trim((string) Setting::get('sumup_access_token', ''));
        if ($key === '') {
            throw new Exception('SumUp não configurado. Adicione a API Key ou credenciais OAuth nas configurações de Gateway.');
        }
        return $key;
    }

    /**
     * Resolve o mejor token disponível:
     * 1. OAuth (Client Credentials) — preferido
     * 2. GatewayAccount do vendedor
     * 3. Personal API Key da plataforma
     */
    public function resolveToken(?Order $order = null): string
    {
        // 1. OAuth Client Credentials (mais poderoso)
        try {
            return $this->getOAuthToken();
        } catch (\Throwable) {
            // OAuth não disponível, tenta alternativas abaixo
        }

        // 2. GatewayAccount do vendedor
        if ($order && $order->seller_id) {
            $account = GatewayAccount::where('user_id', $order->seller_id)
                ->where('provider', 'sumup')
                ->where('enabled', true)
                ->first();

            if ($account && !empty($account->access_token)) {
                return trim($account->access_token);
            }
        }

        // 3. Personal API Key da plataforma
        return $this->platformApiKey();
    }

    // ─────────────────────────────────────────
    // Validation
    // ─────────────────────────────────────────

    /**
     * Testa a conexão: primeiro tenta OAuth, depois Personal Key.
     * Usa o endpoint correto para cada tipo de token.
     */
    public function testConnection(?string $apiKey = null): array
    {
        // Tenta OAuth primeiro
        try {
            $token    = $this->getOAuthToken();
            $response = Http::withToken($token)->get("{$this->baseUrl}/v0.1/me");
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success'  => true,
                    'method'   => 'oauth',
                    'merchant' => $data['merchant_profile']['legal_name'] ?? $data['email'] ?? 'N/A',
                    'code'     => $data['merchant_profile']['merchant_code'] ?? 'N/A',
                    'data'     => $data,
                ];
            }
        } catch (\Throwable) {
            // OAuth falhou, tenta Personal Key
        }

        // Personal Key
        $key = $apiKey ?? $this->platformApiKey();
        $response = Http::withToken($key)->get("{$this->baseUrl}/v0.1/me");

        if ($response->failed()) {
            $error = $response->json('message')
                ?? $response->json('error_message')
                ?? $response->json('error')
                ?? 'Chave inválida ou sem permissão.';
            throw new Exception("SumUp: {$error}");
        }

        $data = $response->json();
        return [
            'success'  => true,
            'method'   => 'personal_key',
            'merchant' => $data['merchant_profile']['legal_name'] ?? $data['email'] ?? 'N/A',
            'code'     => $data['merchant_profile']['merchant_code'] ?? 'N/A',
            'data'     => $data,
        ];
    }

    // ─────────────────────────────────────────
    // Checkout (Cartão via Hosted Page)
    // ─────────────────────────────────────────

    /**
     * Cria um checkout hosted na SumUp.
     */
    public function createCheckout(Order $order, ?string $apiKey = null): array
    {
        $token = $apiKey ? $apiKey : $this->resolveToken($order);
        $order->loadMissing('items', 'user');

        $merchantCode = $this->getMerchantCode($token);

        $payload = [
            'checkout_reference' => 'order-' . $order->id,
            'amount'             => round((float) $order->total_amount, 2),
            'currency'           => 'BRL',
            'description'        => $this->orderDescription($order),
            'return_url'         => route('checkout.success', $order),
        ];

        if (!empty($merchantCode)) {
            $payload['merchant_code'] = $merchantCode;
        }

        if (app()->environment('production')) {
            $payload['redirect_url'] = route('api.webhooks.sumup');
        }

        $response = Http::withToken($token)
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
        $token = $apiKey ?? $this->resolveToken();

        $response = Http::withToken($token)
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
     */
    public function createSubscriptionPlan(array $data, ?string $apiKey = null): array
    {
        $token    = $apiKey ?? $this->resolveToken();
        $interval = $this->mapPeriodToInterval($data['period'] ?? 'monthly');

        $payload = [
            'name'          => $data['name'],
            'amount'        => (int) round((float) $data['price'] * 100),
            'currency'      => 'BRL',
            'interval_type' => $interval,
            'interval'      => (int) ($data['billing_cycle'] ?? 1),
        ];

        $response = Http::withToken($token)
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
        $token = $apiKey ?? $this->resolveToken();

        $payload = [
            'plan_id'  => $planId,
            'email'    => $userData['email'],
            'name'     => $userData['name'] ?? '',
            'metadata' => [
                'order_id'           => $userData['external_reference'] ?? '',
                'external_reference' => $userData['external_reference'] ?? '',
            ],
        ];

        $response = Http::withToken($token)
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
        $token = $apiKey ?? $this->resolveToken();

        $response = Http::withToken($token)
            ->delete("{$this->baseUrl}/v0.1/subscriptions/{$subscriptionId}");

        if ($response->failed() && $response->status() !== 404) {
            throw new Exception('SumUp cancel subscription: ' . $response->body());
        }

        return ['status' => 'cancelled', 'id' => $subscriptionId];
    }

    // ─────────────────────────────────────────
    // Helpers Internos
    // ─────────────────────────────────────────

    private function getMerchantCode(string $token): string
    {
        try {
            $response = Http::withToken($token)->get("{$this->baseUrl}/v0.1/me");
            if ($response->successful()) {
                return $response->json('merchant_profile.merchant_code') ?? '';
            }
        } catch (\Throwable) {
        }
        return '';
    }

    private function orderDescription(Order $order): string
    {
        $firstItem = optional($order->items->first())->title;
        if (!$firstItem) {
            return 'Pedido #' . $order->id . ' - ' . config('app.name');
        }
        $extra = max(0, $order->items->count() - 1);
        $desc  = $extra > 0 ? "{$firstItem} + {$extra} item(ns)" : $firstItem;
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
