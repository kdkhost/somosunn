<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\GatewayAccount;
use Illuminate\Support\Facades\Http;
use Exception;

class PagSeguroService
{
    protected $baseUrlProd = 'https://api.pagseguro.com';
    protected $baseUrlSandbox = 'https://sandbox.api.pagseguro.com';

    protected function getBaseUrl(?GatewayAccount $account = null)
    {
        // Check local setting or account specific setting
        $isSandbox = \App\Models\Setting::get('payments.pagseguro.sandbox');
        return $isSandbox ? $this->baseUrlSandbox : $this->baseUrlProd;
    }

    public function createCheckoutSession(Order $order, GatewayAccount $account)
    {
        $accessToken = $account->access_token;
        $baseUrl = $this->getBaseUrl($account);
        
        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'reference_id' => (string)$item->id,
                'name' => $item->title,
                'quantity' => $item->quantity,
                'unit_amount' => (int)($item->price * 100) // cents
            ];
        }

        $body = [
            'reference_id' => (string)$order->id,
            'items' => $items,
            'customer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
                'tax_id' => '12345678909', // Requires real CPF in V4
            ], 
            'notification_urls' => [
               route('webhook.pagseguro')
            ]
        ];

        $response = Http::withToken($accessToken)
            ->post("{$baseUrl}/orders", $body);

        if ($response->failed()) {
            throw new Exception('PagSeguro Error: ' . $response->body());
        }

        return $response->json();
    }

    public function refundPayment(Order $order, GatewayAccount $account)
    {
        if (!$order->transaction_id) {
            throw new Exception('ID da transação não encontrado.');
        }

        $accessToken = $account->access_token;
        $baseUrl = $this->getBaseUrl($account);
        $paymentId = $order->transaction_id;

        // V4 Refund endpoint: /charges/{id}/cancel
        // Or orders/{id}/cancel ? Usually charge cancel. We assume stored transaction_id is the CHARGE ID.
        
        $response = Http::withToken($accessToken)
            ->post("{$baseUrl}/charges/{$paymentId}/cancel", [
                'amount' => ['value' => (int)($order->total_amount * 100)]
            ]);

        if ($response->failed()) {
             // Try refunding the order if charge cancel fails (depending on integration mode)
             $response = Http::withToken($accessToken)
                ->post("{$baseUrl}/orders/{$paymentId}/cancel"); // if transaction_id was order id
             
             if ($response->failed()) {
                 throw new Exception('Falha ao processar reembolso no PagSeguro: ' . $response->body());
             }
        }

        return $response->json();
    }
}
