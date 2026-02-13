<?php

namespace App\Services\Payment;

use App\Models\Order;
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
        $token = $this->accessToken();
        $preferenceData = $this->buildPreferenceData($order, $options);

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/checkout/preferences", $preferenceData);

        if ($response->failed()) {
            throw new Exception('MercadoPago Preference Error: ' . $response->body());
        }

        return (array) $response->json();
    }

    public function createPixPayment(Order $order, array $data): array
    {
        $token = $this->accessToken();

        $paymentData = [
            'transaction_amount' => (float)$order->total_amount,
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
            'external_reference' => (string)$order->id,
            'notification_url' => $this->notificationUrl(),
        ];

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
        $token = $this->accessToken();

        $paymentData = [
            'transaction_amount' => (float)$order->total_amount,
            'token' => $data['token'],
            'description' => $this->orderDescription($order),
            'installments' => (int)$data['installments'],
            'payment_method_id' => $data['payment_method_id'],
            'issuer_id' => $data['issuer_id'],
            'payer' => [
                'email' => $data['email'] ?? $order->user->email,
                'identification' => [
                    'type' => 'CPF',
                    'number' => preg_replace('/\D/', '', $data['cpf'] ?? $order->user->doc)
                ]
            ],
            'external_reference' => (string)$order->id,
            'notification_url' => $this->notificationUrl(),
        ];

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
        foreach ($order->items as $item) {
            $items[] = [
                'title' => $item->title,
                'quantity' => $item->quantity,
                'currency_id' => 'BRL',
                'unit_price' => (float)$item->price
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

        return [
            'items' => $items,
            'payer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'back_urls' => $options['back_urls'] ?? $backUrls,
            'auto_return' => 'approved',
            'external_reference' => (string)$order->id,
            'statement_descriptor' => $statementDescriptor,
            'notification_url' => $this->notificationUrl(),
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
}
