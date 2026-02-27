<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mercadopago_webhook_approves_order()
    {
        $this->withoutExceptionHandling();

        // Gateway controller uses configuration helpers, let's mock them 
        Config::set('payments.mercadopago.access_token', 'TEST-12345');
        Config::set('payments.mercadopago.platform_id', 'DEV_123');
        Setting::updateOrCreate(['key' => 'mercadopago_env'], ['value' => 'sandbox']);
        $user = User::create([
            'name' => 'Tester Webhook',
            'email' => 'webhook' . rand(1, 1000) . '@test.com',
            'password' => bcrypt('password')
        ]);
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-WH-' . rand(100, 999),
            'total_amount' => 100.00,
            'status' => 'pending'
        ]);

        // Simula a requisição do webhook (o controller faz um GET para obter detalhes do pagamento associado a essa ordem ref)
        Http::fake([
            'api.mercadopago.com/v1/payments/99999999*' => Http::response([
                'id' => 99999999,
                'status' => 'approved',
                'external_reference' => (string) $order->id,
            ], 200),
        ]);

        // Payload idêntico ao padrão oficial MercadoPago
        $payload = [
            'action' => 'payment.created',
            'api_version' => 'v1',
            'data' => [
                'id' => '99999999'
            ],
            'date_created' => now()->toIso8601String(),
            'id' => 99999999,
            'live_mode' => false,
            'type' => 'payment',
            'user_id' => '123456789'
        ];

        $response = $this->postJson(route('api.webhooks.mercadopago'), $payload);

        $response->assertStatus(200);

        // Verifica o fulfillment da Ordem alterando pendente para pago
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);
    }
}
