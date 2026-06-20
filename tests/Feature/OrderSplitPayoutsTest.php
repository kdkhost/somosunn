<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderSplit;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderSplitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSplitPayoutsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::flushRuntimeCache();
    }

    public function test_admin_seller_generates_internal_paid_payout_and_external_pending_payouts(): void
    {
        $buyer = $this->user('member', 'buyer-payouts@test.com');
        $adminSeller = $this->user('admin', 'admin-seller-payouts@test.com', 'pix-admin-seller');
        $superadmin = $this->user('superadmin', 'superadmin-payouts@test.com', 'pix-superadmin');
        $marketing = $this->user('member', 'marketing-payouts@test.com', 'pix-marketing');

        Setting::set('platform_marketing_user_id', (string) $marketing->id);
        Setting::set('marketplace_split_seller_percent', '70');
        Setting::set('marketplace_split_platform_percent', '10');
        Setting::set('marketplace_split_traffic_percent', '10');
        Setting::set('marketplace_split_superadmin_percent', '10');

        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $adminSeller->id,
            'status' => 'paid',
            'total_amount' => 100,
            'currency' => 'BRL',
            'gateway' => 'sumup',
        ]);

        app(OrderSplitService::class)->syncForPaidOrder($order);

        $sellerSplit = OrderSplit::where('order_id', $order->id)->where('receiver_type', 'seller')->firstOrFail();
        $trafficSplit = OrderSplit::where('order_id', $order->id)->where('receiver_type', 'traffic')->firstOrFail();
        $superadminSplit = OrderSplit::where('order_id', $order->id)->where('receiver_type', 'superadmin')->firstOrFail();

        $this->assertDatabaseHas('order_split_payouts', [
            'order_split_id' => $sellerSplit->id,
            'provider' => 'internal',
            'status' => 'paid',
            'amount' => 80,
            'pix_key' => 'pix-admin-seller',
        ]);

        $this->assertDatabaseHas('order_split_payouts', [
            'order_split_id' => $trafficSplit->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount' => 10,
            'pix_key' => 'pix-marketing',
        ]);

        $this->assertDatabaseHas('order_split_payouts', [
            'order_split_id' => $superadminSplit->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount' => 10,
            'pix_key' => 'pix-superadmin',
        ]);
    }

    public function test_admin_route_confirms_manual_payout_and_updates_split_and_payout(): void
    {
        $buyer = $this->user('member', 'buyer-manual@test.com');
        $seller = $this->user('member', 'seller-manual@test.com', 'pix-seller');
        $admin = $this->user('admin', 'admin-manual@test.com', 'pix-admin');
        $superadmin = $this->user('superadmin', 'superadmin-manual@test.com', 'pix-superadmin');
        $marketing = $this->user('member', 'marketing-manual@test.com', 'pix-marketing');

        Setting::set('platform_marketing_user_id', (string) $marketing->id);
        Setting::set('marketplace_split_seller_percent', '70');
        Setting::set('marketplace_split_platform_percent', '10');
        Setting::set('marketplace_split_traffic_percent', '10');
        Setting::set('marketplace_split_superadmin_percent', '10');

        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => 'paid',
            'total_amount' => 100,
            'currency' => 'BRL',
            'gateway' => 'sumup',
        ]);

        app(OrderSplitService::class)->syncForPaidOrder($order);

        $split = OrderSplit::where('order_id', $order->id)->where('receiver_type', 'traffic')->firstOrFail();

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->postJson(route('admin.splits.pay', $split))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('order_splits', [
            'id' => $split->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('order_split_payouts', [
            'order_split_id' => $split->id,
            'provider' => 'manual',
            'status' => 'paid',
            'attempts' => 1,
        ]);
    }

    private function user(string $role, string $email, ?string $pixKey = null): User
    {
        return User::create([
            'name' => $email,
            'email' => $email,
            'password' => bcrypt('secret123'),
            'role' => $role,
            'pix_key' => $pixKey,
            'email_verified_at' => now(),
        ]);
    }
}
