<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderRefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_refund_tracks_metadata_and_keeps_order_paid(): void
    {
        $order = $this->createPaidMercadoPagoOrder(100.00);

        Http::fake([
            'api.mercadopago.com/v1/payments/*/refunds' => Http::response([
                'id' => 321,
                'amount' => 25.00,
            ], 201),
        ]);

        app(OrderRefundService::class)->refund($order, 25.00);

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            $payload = $request->data();

            return str_contains($request->url(), '/v1/payments/MP-ORDER-1/refunds')
                && (float) ($payload['amount'] ?? 0) === 25.00;
        });

        $order->refresh();

        $this->assertSame('paid', $order->status);
        $this->assertNull($order->refunded_at);
        $this->assertSame(25.00, (float) $order->refunded_amount);
        $this->assertSame(75.00, (float) $order->remaining_refundable_amount);
        $this->assertSame('partial', data_get($order->metadata, 'refunds.history.0.type'));
    }

    public function test_full_refund_marks_order_as_refunded(): void
    {
        $order = $this->createPaidMercadoPagoOrder(100.00);

        Http::fake([
            'api.mercadopago.com/v1/payments/*/refunds' => Http::response([
                'id' => 654,
                'amount' => 100.00,
            ], 201),
        ]);

        app(OrderRefundService::class)->refund($order);

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            $payload = $request->data();

            return str_contains($request->url(), '/v1/payments/MP-ORDER-1/refunds')
                && !array_key_exists('amount', $payload);
        });

        $order->refresh();

        $this->assertSame('refunded', $order->status);
        $this->assertNotNull($order->refunded_at);
        $this->assertSame(100.00, (float) $order->refunded_amount);
        $this->assertSame(0.00, (float) $order->remaining_refundable_amount);
        $this->assertSame('full', data_get($order->metadata, 'refunds.history.0.type'));
    }

    private function createPaidMercadoPagoOrder(float $amount): Order
    {
        Setting::updateOrCreate(['key' => 'mercadopago_env'], ['value' => 'sandbox']);
        Setting::updateOrCreate(['key' => 'mercadopago_sandbox_access_token'], ['value' => 'TEST-12345']);
        Setting::updateOrCreate(['key' => 'mercadopago_integrator_id'], ['value' => 'DEV_123']);

        $user = User::create([
            'name' => 'Cliente Refund',
            'email' => 'refund' . rand(1, 100000) . '@test.com',
            'password' => bcrypt('password'),
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => $amount,
            'currency' => 'BRL',
            'gateway' => 'mercadopago',
            'transaction_id' => 'MP-ORDER-1',
            'paid_at' => now(),
            'metadata' => [
                'webhook_data' => [
                    'transaction_amount' => $amount,
                ],
            ],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'course',
            'item_id' => 1,
            'title' => 'Produto de Teste',
            'price' => $amount,
            'quantity' => 1,
        ]);

        return $order->fresh(['items']);
    }
}
