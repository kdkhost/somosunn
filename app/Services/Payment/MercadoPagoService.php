<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\GatewayAccount;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Exception;

class MercadoPagoService
{
    protected string $baseUrl = 'https://api.mercadopago.com';

    public function __construct()
    {
        //
    }

    public function createPreference(Order $order, array $options = []): array
    {
        $config = $this->getSellerConfig($order);
        $token = $config['token'];

        $preferenceData = $this->buildPreferenceData($order, $options);

        // Se não for venda da própria plataforma, aplicar marketplace_fee
        if (!$config['is_platform']) {
            $fee = $this->calculateApplicationFee($order);
            if ($fee > 0) {
                $preferenceData['marketplace_fee'] = $fee;
            }
        }

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/checkout/preferences", $preferenceData);

        if ($response->failed()) {
            throw new Exception('MercadoPago Preference Error: ' . $response->body());
        }

        return (array) $response->json();
    }

    public function createPixPayment(Order $order, array $data): array
    {
        $config = $this->getSellerConfig($order);
        $token = $config['token'];

        $paymentData = [
            'transaction_amount' => (float) $order->total_amount,
            'description' => $this->orderDescription($order),
            'payment_method_id' => 'pix',
            'payer' => [
                'email' => $data['email'] ?? $order->user->email,
                'first_name' => explode(' ', $data['name'] ?? $order->user->name)[0],
                'last_name' => collect(explode(' ', $data['name'] ?? $order->user->name))->slice(1)->join(' ') ?: 'User',
                'identification' => [
                    'type' => 'CPF',
                    'number' => preg_replace('/\D/', '', $data['cpf'] ?? $order->user->doc)
                ]
            ],
            'external_reference' => (string) $order->id,
            'notification_url' => $this->notificationUrl(),
        ];

        // Se usar token do vendedor, aplicar application_fee
        if (!$config['is_platform']) {
            $fee = $this->calculateApplicationFee($order);
            if ($fee > 0) {
                $paymentData['application_fee'] = $fee;
            }
        }

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v1/payments", $paymentData);

        if ($response->failed()) {
            throw new Exception('Falha ao criar Pix: ' . $response->body());
        }

        $body = (array) $response->json();

        return [
            'status' => $body['status'] ?? null,
            'id' => $body['id'] ?? null,
            'qr_code' => data_get($body, 'point_of_interaction.transaction_data.qr_code'),
            'qr_code_base64' => data_get($body, 'point_of_interaction.transaction_data.qr_code_base64'),
        ];
    }

    public function createCreditCardPayment(Order $order, array $data): array
    {
        $config = $this->getSellerConfig($order);
        $token = $config['token'];

        $paymentData = [
            'transaction_amount' => (float) $order->total_amount,
            'token' => $data['token'],
            'description' => $this->orderDescription($order),
            'installments' => (int) $data['installments'],
            'payment_method_id' => $data['payment_method_id'],
            'issuer_id' => $data['issuer_id'],
            'payer' => [
                'email' => $data['email'] ?? $order->user->email,
                'identification' => [
                    'type' => 'CPF',
                    'number' => preg_replace('/\D/', '', $data['cpf'] ?? $order->user->doc)
                ]
            ],
            'external_reference' => (string) $order->id,
            'notification_url' => $this->notificationUrl(),
        ];

        // Se usar token do vendedor, aplicar application_fee
        if (!$config['is_platform']) {
            $fee = $this->calculateApplicationFee($order);
            if ($fee > 0) {
                $paymentData['application_fee'] = $fee;
            }
        }

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v1/payments", $paymentData);

        if ($response->failed()) {
            throw new Exception('Falha ao processar cartão: ' . $response->body());
        }

        $body = (array) $response->json();
        return [
            'status' => $body['status'] ?? null,
            'id' => $body['id'] ?? null,
            'status_detail' => $body['status_detail'] ?? null,
        ];
    }

    public function subscribeUser(string $mpPlanId, array $userData): array
    {
        $token = $this->accessToken();

        $subscriptionData = [
            'preapproval_plan_id' => $mpPlanId,
            'payer_email' => $userData['email'],
            'back_url' => $userData['back_url'] ?? route('panel.dashboard'),
            'status' => 'authorized',
            'reason' => $userData['reason'] ?? 'Assinatura',
            'external_reference' => $userData['external_reference'] ?? '',
        ];

        if (!empty($userData['card_token'])) {
            $subscriptionData['card_token_id'] = $userData['card_token'];
        }

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/preapproval", $subscriptionData);

        if ($response->failed()) {
            throw new Exception('Erro ao criar assinatura MP: ' . $response->body());
        }

        return (array) $response->json();
    }

    public function cancelSubscription(string $preapprovalId): array
    {
        $token = $this->accessToken();
        $response = Http::withToken($token)
            ->put("{$this->baseUrl}/preapproval/{$preapprovalId}", [
                'status' => 'cancelled'
            ]);

        if ($response->failed()) {
            throw new Exception('Erro ao cancelar assinatura MP: ' . $response->body());
        }

        return (array) $response->json();
    }

    public function getPreapproval(string $preapprovalId): array
    {
        $token = $this->accessToken();
        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/preapproval/{$preapprovalId}");

        if ($response->failed()) {
            throw new Exception('Erro ao buscar assinatura MP: ' . $response->body());
        }

        return (array) $response->json();
    }

    public function createPreapprovalPlan(array $data): array
    {
        $token = $this->accessToken();

        // Marketplace application_fee (split) em assinaturas
        // Nota: O MP não suporta 'application_fee' diretamente no preapproval_plan.
        // O split deve ser feito na captura do pagamento gerado pela assinatura.
        // No entanto, podemos definir o 'collector_id' se estivermos usando aplicação marketplace.

        $planData = [
            'reason' => $data['name'],
            'auto_setup' => [
                'frequency' => (int) ($data['billing_cycle'] ?? 1),
                'frequency_type' => $this->mapPeriod($data['period'] ?? 'months'),
                'transaction_amount' => (float) $data['price'],
                'currency_id' => 'BRL',
            ],
            'back_url' => route('marketplace.index'),
            'status' => 'active',
        ];

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/preapproval_plan", $planData);

        if ($response->failed()) {
            throw new Exception('Erro ao criar plano recorrente MP: ' . $response->body());
        }

        return (array) $response->json();
    }

    private function mapPeriod(string $period): string
    {
        return match (strtolower($period)) {
            'day', 'days' => 'days',
            'month', 'months', 'mensal' => 'months',
            'year', 'years', 'anual' => 'months', // MP recorrente anual costuma ser 12 meses
            default => 'months',
        };
    }

    private function accessToken(): string
    {
        $token = trim((string) config('payments.mercadopago.access_token'));
        if ($token === '') {
            throw new Exception('MercadoPago não configurado. Verifique as configurações do gateway da plataforma.');
        }
        return $token;
    }

    private function notificationUrl(): string
    {
        return route('api.webhooks.mercadopago');
    }

    private function orderDescription(Order $order): string
    {
        return 'UNN - Pedido #' . $order->id;
    }

    private function buildPreferenceData(Order $order, array $options = []): array
    {
        $items = [];
        $passFee = false;
        $maxInstallments = 12;
        $enabledMethods = ['credit_card', 'pix', 'ticket'];

        // Buscar config do vendedor para customizar o checkout
        if ($order->seller_id) {
            $account = GatewayAccount::where('user_id', $order->seller_id)
                ->where('provider', 'mercadopago')
                ->where('enabled', true)
                ->first();

            if ($account && is_array($account->extra)) {
                $passFee = (bool) data_get($account->extra, 'pass_fee', false);
                $maxInstallments = (int) data_get($account->extra, 'max_installments', 12);
                $enabledMethods = data_get($account->extra, 'enabled_methods', ['credit_card', 'pix', 'ticket']);
            }
        }

        // Se o vendedor repassar a taxa, aumentamos o valor do item proporcionalmente (estimativa de 5% se não houver taxa exata)
        $feeMultiplier = $passFee ? 1.05 : 1.0;

        foreach ($order->items as $item) {
            $items[] = [
                'title' => $item->title,
                'quantity' => $item->quantity,
                'currency_id' => 'BRL',
                'unit_price' => round((float) $item->price * $feeMultiplier, 2)
            ];
        }

        $context = (string) data_get($order->metadata, 'context', '');
        if ($context === '') {
            $firstType = (string) optional($order->items->first())->item_type;
            $context = $firstType ?: 'unknown';
        }

        $token = (string) data_get($order->metadata, 'public_token', '');

        $fallbackPlanId = (int) optional($order->items->first())->item_id;
        $subscriptionCheckoutUrl = $fallbackPlanId ? route('subscription.checkout', ['plan' => $fallbackPlanId]) : url('/');

        $backUrls = match ($context) {
            'course', 'mentorship', 'marketplace' => [
                'success' => route('checkout.success', ['order' => $order->id]),
                'failure' => route('checkout.failure', ['order' => $order->id]),
                'pending' => route('checkout.pending', ['order' => $order->id]),
            ],
            'event' => [
                'success' => route('events.payment.success', ['order' => $order->id, 'token' => $token]),
                'failure' => route('events.payment.failure', ['order' => $order->id, 'token' => $token]),
                'pending' => route('events.payment.pending', ['order' => $order->id, 'token' => $token]),
            ],
            'subscription' => [
                'success' => route('subscription.success', ['order' => $order->id]),
                'failure' => $subscriptionCheckoutUrl,
                'pending' => $subscriptionCheckoutUrl,
            ],
            default => [
                'success' => route('checkout.success', ['order' => $order->id]),
                'failure' => route('checkout.failure', ['order' => $order->id]),
                'pending' => route('checkout.pending', ['order' => $order->id]),
            ],
        };

        $statementDescriptor = trim((string) ($options['statement_descriptor'] ?? 'UNN PLATAFORMA'));
        if ($statementDescriptor === '') {
            $statementDescriptor = 'UNN PLATAFORMA';
        }

        // Configurar métodos de pagamento e parcelas
        $paymentMethods = [
            'installments' => $maxInstallments,
        ];

        $excludedMethods = [];
        if (!in_array('credit_card', $enabledMethods))
            $excludedMethods[] = ['id' => 'credit_card'];
        if (!in_array('pix', $enabledMethods))
            $excludedMethods[] = ['id' => 'pix'];
        if (!in_array('ticket', $enabledMethods))
            $excludedMethods[] = ['id' => 'ticket'];

        if (!empty($excludedMethods)) {
            $paymentMethods['excluded_payment_types'] = $excludedMethods;
        }

        return [
            'items' => $items,
            'payer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'back_urls' => $options['back_urls'] ?? $backUrls,
            'auto_return' => 'approved',
            'external_reference' => (string) $order->id,
            'statement_descriptor' => $statementDescriptor,
            'notification_url' => $this->notificationUrl(),
            'payment_methods' => $paymentMethods,
        ];
    }

    public function refundPayment(Order $order): array
    {
        // For MercadoPago, we need the Payment ID (not Preference ID).
        // Assuming we stored the payment ID in the order or a transaction table.
        // For now, we'll try to use the external reference's payment collection fetch or assume order->payment_id exists.

        if (!$order->transaction_id) {
            throw new Exception('ID da transação de pagamento não encontrado para este pedido.');
        }

        $accessToken = $this->accessToken();
        $paymentId = $order->transaction_id;

        // Refund full amount
        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v1/payments/{$paymentId}/refunds");

        if ($response->failed()) {
            throw new Exception('Falha ao processar reembolso no MercadoPago: ' . $response->body());
        }

        return (array) $response->json();
    }
    public function getBalance(?int $sellerId = null): array
    {
        $token = null;

        if ($sellerId) {
            $account = GatewayAccount::where('user_id', $sellerId)
                ->where('provider', 'mercadopago')
                ->where('enabled', true)
                ->first();
            $token = $account->access_token ?? null;
        }

        if (!$token) {
            $token = trim((string) config('payments.mercadopago.access_token'));
        }

        if (empty($token)) {
            throw new Exception('Token do Mercado Pago não encontrado.');
        }

        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/users/me/mercadopago_account/balance");

        if ($response->failed()) {
            // Se falhar, tenta o endpoint alternativo ou retorna zerado
            return [
                'total_amount' => 0,
                'available_balance' => 0,
                'unavailable_balance' => 0,
                'currency_id' => 'BRL'
            ];
        }

        return (array) $response->json();
    }

    private function getSellerConfig(Order $order): array
    {
        // Default: Platform Config
        $config = [
            'token' => trim((string) config('payments.mercadopago.access_token')),
            'public_key' => trim((string) config('payments.mercadopago.public_key')),
            'is_platform' => true
        ];

        // Check if order has a specific seller
        if ($order->seller_id) {
            $sellerAccount = GatewayAccount::where('user_id', $order->seller_id)
                ->where('provider', 'mercadopago')
                ->where('enabled', true)
                ->first();

            if ($sellerAccount && !empty($sellerAccount->access_token)) {
                $config = [
                    'token' => $sellerAccount->access_token,
                    'public_key' => $sellerAccount->public_key,
                    'is_platform' => false
                ];
            }
        }

        if (empty($config['token'])) {
            throw new Exception('MercadoPago não configurado para processar este pedido (Seller ou Plataforma).');
        }

        return $config;
    }

    private function calculateApplicationFee(Order $order): ?float
    {
        $percentage = (float) Setting::get('marketplace_fee', 10);

        if ($percentage <= 0) {
            return null;
        }

        $fee = ($order->total_amount * $percentage) / 100;
        return round($fee, 2);
    }
}
