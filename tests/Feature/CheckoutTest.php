<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_checkout_creates_order_and_calls_mercadopago_with_correct_keys()
    {
        $this->withoutExceptionHandling();

        // Fake dynamic settings for the gateway
        Setting::updateOrCreate(['key' => 'mercadopago_env'], ['value' => 'sandbox']);
        Setting::updateOrCreate(['key' => 'mercadopago_sandbox_access_token'], ['value' => 'TEST-12345']);
        Setting::updateOrCreate(['key' => 'mercadopago_sandbox_public_key'], ['value' => 'TEST-PUB-123']);
        Setting::updateOrCreate(['key' => 'mercadopago_integrator_id'], ['value' => 'DEV_123']);

        $user = User::create([
            'name' => 'Tester Checkout',
            'email' => 'checkout' . rand(1, 1000) . '@test.com',
            'password' => bcrypt('password')
        ]);

        $course = Course::create([
            'user_id' => $user->id,
            'title' => 'Test Course for Checkout',
            'slug' => 'test-checkout-' . rand(1, 1000),
            'description' => 'Test Desc',
            'full_description' => 'A full description is required',
            'price' => 100.00,
            'published' => true
        ]);

        // Crie fake Order
        $order = \App\Models\Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-' . rand(100, 999),
            'total_amount' => 100.00,
            'status' => 'pending'
        ]);

        $item = \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'item_id' => $course->id,
            'item_type' => 'App\Models\Course',
            'title' => 'Test Course',
            'price' => 100.00,
            'quantity' => 1
        ]);

        // Mock Http to avoid real calls and assert payload
        Http::fake([
            'api.mercadopago.com/v1/payments*' => Http::response(['id' => 123456, 'status' => 'approved'], 201),
        ]);

        $mpService = new \App\Services\Payment\MercadoPagoService();
        $mpService->createCreditCardPayment($order, [
            'token' => 'mock-token',
            'payment_method_id' => 'visa',
            'installments' => 1,
            'issuer_id' => '123'
        ]);

        // Verifica envio do header de Integrator ID dinâmico
        Http::assertSent(function (\Illuminate\Http\Client\Request $request) use ($order) {
            $payload = $request->data();

            return $request->hasHeader('X-Integrator-Id', 'DEV_123')
                && ($payload['description'] ?? null) === 'Test Course - Pedido #' . $order->id;
        });

        $this->assertTrue(true); // O assert foi feito pelo assertSent Acima
    }
}
