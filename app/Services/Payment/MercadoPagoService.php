<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\GatewayAccount;
use Illuminate\Support\Facades\Http;
use Exception;

class MercadoPagoService
{
    protected $baseUrl = 'https://api.mercadopago.com';

    public function __construct()
    {
        // Sandbox check can be done per request if account varies, 
        // but base URL is usually same for MP, just tokens differ.
        // However, we will respect the pattern.
    }

    public function createPreference(Order $order, GatewayAccount $account)
    {
        // ... (existing code)
        $this->ensureAccessToken($account);
        $preferenceData = $this->buildPreferenceData($order, $account);

        $response = Http::withToken($account->access_token)
            ->post("{$this->baseUrl}/checkout/preferences", $preferenceData);

        if ($response->failed()) {
            throw new Exception('MercadoPago Preference Error: ' . $response->body());
        }

        return $response->json();
    }

    public function createPixPayment(Order $order, array $data, GatewayAccount $account)
    {
        $this->ensureAccessToken($account);

        $paymentData = [
            'transaction_amount' => (float)$order->total_amount,
            'description' => 'UNN - Pedido #' . $order->id,
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
            'notification_url' => route('webhook.mercadopago', ['seller_id' => $account->user_id]),
        ];

        $response = Http::withToken($account->access_token)
            ->post("{$this->baseUrl}/v1/payments", $paymentData);

        if ($response->failed()) {
            throw new Exception('Falha ao criar Pix: ' . $response->body());
        }
        
        $body = $response->json();
        
        return [
            'status' => $body['status'],
            'id' => $body['id'],
            'qr_code' => $body['point_of_interaction']['transaction_data']['qr_code'],
            'qr_code_base64' => $body['point_of_interaction']['transaction_data']['qr_code_base64'],
        ];
    }

    public function createCreditCardPayment(Order $order, array $data, GatewayAccount $account)
    {
        $this->ensureAccessToken($account);

        $paymentData = [
            'transaction_amount' => (float)$order->total_amount,
            'token' => $data['token'],
            'description' => 'UNN - Pedido #' . $order->id,
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
            'notification_url' => route('webhook.mercadopago', ['seller_id' => $account->user_id]),
        ];

        $response = Http::withToken($account->access_token)
            ->post("{$this->baseUrl}/v1/payments", $paymentData);

        if ($response->failed()) {
            throw new Exception('Falha ao processar cartão: ' . $response->body());
        }

        $body = $response->json();
        return [
            'status' => $body['status'],
            'id' => $body['id'],
            'status_detail' => $body['status_detail']
        ];
    }
    
    private function ensureAccessToken($account) {
        if (!$account->access_token) {
            // Em dev/demo, podemos usar credenciais de teste se não configurado
            // Mas em prod deve falhar
            $account->access_token = config('payments.mercadopago.access_token');
        }
    }
    
    // Helper para manter a compatibilidade com código existente
    private function buildPreferenceData($order, $account) {
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

        $backUrls = match ($context) {
            'course' => [
                'success' => route('checkout.success', ['order' => $order->id]),
                'failure' => route('checkout.failure', ['order' => $order->id]),
                'pending' => route('checkout.pending', ['order' => $order->id]),
            ],
            default => [
                'success' => route('subscription.success', ['order' => $order->id]),
                'failure' => route('subscription.checkout', ['plan' => $order->items->first()->item_id]),
                'pending' => route('subscription.checkout', ['plan' => $order->items->first()->item_id]),
            ],
        };

        return [
            'items' => $items,
            'payer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'back_urls' => $backUrls,
            'auto_return' => 'approved',
            'external_reference' => (string)$order->id,
            'statement_descriptor' => 'UNN PLATAFORMA',
            'notification_url' => route('webhook.mercadopago', ['seller_id' => $account->user_id]),
        ];
    }

    public function refundPayment(Order $order, GatewayAccount $account)
    {
        // For MercadoPago, we need the Payment ID (not Preference ID).
        // Assuming we stored the payment ID in the order or a transaction table.
        // For now, we'll try to use the external reference's payment collection fetch or assume order->payment_id exists.
        
        if (!$order->transaction_id) {
            throw new Exception('ID da transação de pagamento não encontrado para este pedido.');
        }

        $accessToken = $account->access_token;
        $paymentId = $order->transaction_id;

        // Refund full amount
        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v1/payments/{$paymentId}/refunds");

        if ($response->failed()) {
            throw new Exception('Falha ao processar reembolso no MercadoPago: ' . $response->body());
        }

        return $response->json();
    }
}
