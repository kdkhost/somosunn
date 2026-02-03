<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\GatewayAccount;
use Illuminate\Support\Facades\Http;
use Exception;

class PagSeguroService
{
    protected $baseUrl = 'https://api.pagseguro.com'; // V4 API Sandbox/Prod

    public function createCheckoutSession(Order $order, GatewayAccount $account)
    {
        $accessToken = $account->access_token; // User must provide V4 token
        
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
            ],
            // 'payment_methods' => ...
        ];

        // PagSeguro Split logic is complex here, assumes direct payment using Seller Token
        
        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/orders", $body);

        if ($response->failed()) {
            throw new Exception('PagSeguro Error: ' . $response->body());
        }

        return $response->json();
    }
}
