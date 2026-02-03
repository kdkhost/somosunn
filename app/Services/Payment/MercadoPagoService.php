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
        $accessToken = $account->access_token;
        
        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'title' => $item->title,
                'quantity' => $item->quantity,
                'currency_id' => 'BRL',
                'unit_price' => (float)$item->price
            ];
        }

        $preferenceData = [
            'items' => $items,
            'payer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'back_urls' => [
                'success' => route('checkout.success', ['order' => $order->id]),
                'failure' => route('checkout.failure', ['order' => $order->id]),
                'pending' => route('checkout.pending', ['order' => $order->id]),
            ],
            'auto_return' => 'approved',
            'external_reference' => (string)$order->id,
            'statement_descriptor' => 'UNN ' . substr($order->seller->name ?? '', 0, 10),
            'notification_url' => route('webhook.mercadopago', ['seller_id' => $account->user_id]),
        ];

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/checkout/preferences", $preferenceData);

        if ($response->failed()) {
            throw new Exception('MercadoPago Error: ' . $response->body());
        }

        return $response->json();
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
