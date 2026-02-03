<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\GatewayAccount;
use Illuminate\Support\Facades\Http;
use Exception;

class MercadoPagoService
{
    protected $baseUrl = 'https://api.mercadopago.com';

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

        // Add platform fee as a separate item? No, MP Split is better.
        // If we uses Seller's credentials directly, we can't easily split to Platform unless we use application_fee and we form a Marketplace.
        // For this implementation, we assume simple processing via Seller's credentials.
        
        // If Platform Fee is needed and we are using Seller's Token:
        // We might be limited. We'll simplify: The Full Amount goes to Seller.
        // The Platform Fee logic would ideally require OAuth "MercadoPago Connect".
        
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

        // Marketplace Split logic (only works if using Platform Token + Access Token of seller, or Marketplace App ID)
        // Since user said "configures their own keys", we assume Direct Payment.
        // We will just process the payment.
        
        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/checkout/preferences", $preferenceData);

        if ($response->failed()) {
            throw new Exception('MercadoPago Error: ' . $response->body());
        }

        return $response->json();
    }
}
