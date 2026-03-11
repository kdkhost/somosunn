<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();

        parent::tearDown();
    }

    public function test_checkout_creates_order_and_calls_mercadopago_with_correct_keys()
    {
        $this->withoutExceptionHandling();

        // Fake dynamic settings for the gateway
        Setting::flushRuntimeCache();
        Setting::set('mercadopago_env', 'sandbox');
        Setting::set('mercadopago_sandbox_access_token', 'TEST-12345');
        Setting::set('mercadopago_sandbox_public_key', 'TEST-PUB-123');
        Setting::set('mercadopago_integrator_id', 'DEV_123');

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

    public function test_create_preference_retries_without_optional_policy_headers_when_policy_agent_blocks_request()
    {
        Setting::flushRuntimeCache();
        Setting::set('mercadopago_env', 'sandbox');
        Setting::set('mercadopago_sandbox_access_token', 'TEST-12345');
        Setting::set('mercadopago_sandbox_public_key', 'TEST-PUB-123');
        Setting::set('mercadopago_integrator_id', 'DEV_123');
        Setting::set('mercadopago_platform_id', 'PLATFORM_123');

        $user = User::create([
            'name' => 'Tester Preference',
            'email' => 'preference' . rand(1, 1000) . '@test.com',
            'password' => bcrypt('password'),
        ]);

        $course = Course::create([
            'user_id' => $user->id,
            'title' => 'Test Course Preference',
            'slug' => 'test-preference-' . rand(1, 1000),
            'description' => 'Test Desc',
            'full_description' => 'A full description is required',
            'price' => 100.00,
            'published' => true,
        ]);

        $order = \App\Models\Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-PREF-' . rand(100, 999),
            'total_amount' => 100.00,
            'status' => 'pending',
            'metadata' => [
                'context' => 'course',
                'public_token' => 'token-test',
            ],
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'item_id' => $course->id,
            'item_type' => 'App\Models\Course',
            'title' => 'Test Course Preference',
            'price' => 100.00,
            'quantity' => 1,
        ]);

        Http::fake([
            'api.mercadopago.com/checkout/preferences*' => Http::sequence()
                ->push([
                    'code' => 'PA_UNAUTHORIZED_RESULT_FROM_POLICIES',
                    'status' => 403,
                    'message' => 'At least one policy returned UNAUTHORIZED.',
                    'blocked_by' => 'PolicyAgent',
                ], 403)
                ->push([
                    'id' => 'pref_123',
                    'init_point' => 'https://mp.test/checkout',
                ], 201),
        ]);

        $response = (new \App\Services\Payment\MercadoPagoService())->createPreference($order);

        $this->assertSame('pref_123', $response['id'] ?? null);

        $requests = Http::recorded();
        $this->assertCount(2, $requests);
        $this->assertTrue($requests[0][0]->hasHeader('X-Integrator-Id', 'DEV_123'));
        $this->assertTrue($requests[0][0]->hasHeader('X-Platform-Id', 'PLATFORM_123'));
        $this->assertFalse($requests[1][0]->hasHeader('X-Integrator-Id'));
        $this->assertFalse($requests[1][0]->hasHeader('X-Platform-Id'));
    }

    public function test_create_preference_retries_with_alternate_platform_token_after_policy_block()
    {
        Setting::flushRuntimeCache();
        Setting::set('mercadopago_env', 'sandbox');
        Setting::set('mercadopago_sandbox_access_token', 'TEST-BLOCKED');
        Setting::set('mercadopago_sandbox_public_key', 'TEST-PUB-123');
        Setting::set('mercadopago_access_token', 'APP_USR-FALLBACK-TOKEN');
        Setting::set('mercadopago_integrator_id', 'DEV_123');
        Setting::set('mercadopago_platform_id', 'PLATFORM_123');

        $user = User::create([
            'name' => 'Tester Alternate Token',
            'email' => 'alt-token' . rand(1, 1000) . '@test.com',
            'password' => bcrypt('password'),
        ]);

        $course = Course::create([
            'user_id' => $user->id,
            'title' => 'Test Course Alternate Token',
            'slug' => 'test-alt-token-' . rand(1, 1000),
            'description' => 'Test Desc',
            'full_description' => 'A full description is required',
            'price' => 100.00,
            'published' => true,
        ]);

        $order = \App\Models\Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-ALT-' . rand(100, 999),
            'total_amount' => 100.00,
            'status' => 'pending',
            'metadata' => [
                'context' => 'course',
                'public_token' => 'token-test',
            ],
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'item_id' => $course->id,
            'item_type' => 'App\Models\Course',
            'title' => 'Test Course Alternate Token',
            'price' => 100.00,
            'quantity' => 1,
        ]);

        Http::fake([
            'api.mercadopago.com/checkout/preferences*' => Http::sequence()
                ->push([
                    'code' => 'PA_UNAUTHORIZED_RESULT_FROM_POLICIES',
                    'status' => 403,
                    'message' => 'At least one policy returned UNAUTHORIZED.',
                    'blocked_by' => 'PolicyAgent',
                ], 403)
                ->push([
                    'code' => 'PA_UNAUTHORIZED_RESULT_FROM_POLICIES',
                    'status' => 403,
                    'message' => 'At least one policy returned UNAUTHORIZED.',
                    'blocked_by' => 'PolicyAgent',
                ], 403)
                ->push([
                    'id' => 'pref_alt_123',
                    'init_point' => 'https://mp.test/checkout-alt',
                ], 201),
        ]);

        $response = (new \App\Services\Payment\MercadoPagoService())->createPreference($order);

        $this->assertSame('pref_alt_123', $response['id'] ?? null);

        $requests = Http::recorded();
        $this->assertCount(3, $requests);
        $this->assertSame(['Bearer TEST-BLOCKED'], $requests[0][0]->header('Authorization'));
        $this->assertSame(['Bearer TEST-BLOCKED'], $requests[1][0]->header('Authorization'));
        $this->assertSame(['Bearer APP_USR-FALLBACK-TOKEN'], $requests[2][0]->header('Authorization'));
        $this->assertFalse($requests[2][0]->hasHeader('X-Integrator-Id'));
        $this->assertFalse($requests[2][0]->hasHeader('X-Platform-Id'));
    }
}
