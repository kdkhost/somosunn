<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class SumUpService
{
    protected $baseUrl = 'https://api.sumup.com/v1';

    /**
     * Cria um checkout na SumUp.
     * 
     * @param Order $order
     * @param string $accessToken
     * @return array|null
     */
    public function createCheckout(Order $order, string $accessToken)
    {
        try {
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/checkouts", [
                    'checkout_reference' => (string) $order->id,
                    'amount' => (float) $order->total_amount,
                    'currency' => 'BRL',
                    'pay_to_email' => config('mail.from.address'), // E-mail da plataforma
                    'description' => "Pedido #{$order->id} - " . config('app.name'),
                    'return_url' => route('checkout.success', $order),
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erro ao criar checkout SumUp', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $order->id
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exceção ao criar checkout SumUp: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca detalhes de um checkout.
     */
    public function getCheckout(string $checkoutId, string $accessToken)
    {
        $response = Http::withToken($accessToken)->get("{$this->baseUrl}/checkouts/{$checkoutId}");
        return $response->successful() ? $response->json() : null;
    }
}
