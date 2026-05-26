<?php

namespace Tests\Feature\Payment;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\SumUpTransaction;
use App\Models\User;
use App\Services\Payment\SumUpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SumUpReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Queue::fake();
        Setting::flushRuntimeCache();

        Setting::updateOrCreate(['key' => 'sumup_api_key'], ['value' => 'test_sumup_token']);
        Setting::updateOrCreate(['key' => 'sumup_merchant_code'], ['value' => 'MTEST123']);
        Setting::updateOrCreate(['key' => 'email_dispatch_mode'], ['value' => 'queue']);
    }

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();
        parent::tearDown();
    }

    public function test_reconcile_order_marks_paid_even_when_latest_checkout_is_pending(): void
    {
        $user = User::create([
            'name' => 'Jorge Orlandi',
            'email' => 'jorge@example.test',
            'password' => bcrypt('secret123'),
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'SOMOS UNN SUMMIT 2026',
            'description' => 'Evento de teste',
            'start_at' => now()->addDays(10),
            'published' => true,
            'price' => 37.00,
            'is_ticket_enabled' => false,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total_amount' => 37.00,
            'currency' => 'BRL',
            'gateway' => 'sumup',
            'payment_method' => null,
            'metadata' => ['context' => 'event'],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'event',
            'item_id' => $event->id,
            'title' => $event->title,
            'price' => 37.00,
            'quantity' => 1,
        ]);

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'status' => EventRegistration::STATUS_PENDING,
            'price' => 37.00,
            'quantity' => 1,
        ]);

        $paidTransaction = SumUpTransaction::create([
            'order_id' => $order->id,
            'checkout_id' => 'paid-checkout',
            'status' => 'PENDING',
            'payment_type' => 'PIX',
            'amount' => 37.00,
            'currency' => 'BRL',
            'webhook_token' => str_repeat('a', 64),
            'webhook_url' => 'https://example.test/webhook/sumup',
            'raw_response' => ['id' => 'paid-checkout'],
        ]);

        SumUpTransaction::create([
            'order_id' => $order->id,
            'checkout_id' => 'latest-pending-checkout',
            'status' => 'PENDING',
            'payment_type' => 'PIX',
            'amount' => 37.00,
            'currency' => 'BRL',
            'webhook_token' => str_repeat('b', 64),
            'webhook_url' => 'https://example.test/webhook/sumup',
            'raw_response' => ['id' => 'latest-pending-checkout'],
        ]);

        Http::fake([
            'https://api.sumup.com/v0.1/checkouts/latest-pending-checkout' => Http::response([
                'id' => 'latest-pending-checkout',
                'status' => 'PENDING',
                'amount' => 37.00,
            ], 200),
            'https://api.sumup.com/v0.1/checkouts/paid-checkout' => Http::response([
                'id' => 'paid-checkout',
                'status' => 'PAID',
                'amount' => 37.00,
                'transactions' => [
                    [
                        'id' => 'tx-paid-123',
                        'status' => 'SUCCESSFUL',
                        'payment_type' => 'pix',
                        'amount' => 37.00,
                    ],
                ],
            ], 200),
        ]);

        $result = app(SumUpService::class)->reconcileOrderTransactions($order);

        $this->assertTrue($result['paid']);
        $this->assertSame('PAID', $result['status']);
        $this->assertSame('paid-checkout', $result['checkout_id']);
        $this->assertSame('tx-paid-123', $result['transaction_id']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
            'transaction_id' => 'tx-paid-123',
            'payment_method' => 'pix',
        ]);

        $this->assertDatabaseHas('event_registrations', [
            'id' => $registration->id,
            'status' => EventRegistration::STATUS_PAID,
        ]);

        $this->assertDatabaseHas('sumup_transactions', [
            'id' => $paidTransaction->id,
            'status' => 'PAID',
            'transaction_id' => 'tx-paid-123',
        ]);
    }
}
