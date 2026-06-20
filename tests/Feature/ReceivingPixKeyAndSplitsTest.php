<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderSplit;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderSplitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceivingPixKeyAndSplitsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::flushRuntimeCache();
    }

    public function test_pix_key_access_follows_admin_and_current_marketing_manager(): void
    {
        $admin = $this->user('admin', 'admin@unn.test', 'pix-admin');
        $superadmin = $this->user('superadmin', 'superadmin@unn.test', 'pix-superadmin');
        $firstMarketing = $this->user('member', 'marketing-1@unn.test', 'pix-marketing-antigo');
        $secondMarketing = $this->user('member', 'marketing-2@unn.test', null);
        $member = $this->user('member', 'member@unn.test', 'pix-legado');
        $successfulMember = $this->user('member', 'successful-member@unn.test', null);
        $successfulMember->update(['level' => 'sucesso']);

        Setting::set('platform_marketing_user_id', (string) $firstMarketing->id);

        $this->assertTrue($admin->canManageReceivingPixKey());
        $this->assertTrue($superadmin->canManageReceivingPixKey());
        $this->assertTrue($firstMarketing->canManageReceivingPixKey());
        $this->assertFalse($secondMarketing->canManageReceivingPixKey());
        $this->assertFalse($member->canManageReceivingPixKey());
        $this->assertFalse($successfulMember->canManageReceivingPixKey());

        Setting::set('platform_marketing_user_id', (string) $secondMarketing->id);

        $this->assertFalse($firstMarketing->canManageReceivingPixKey());
        $this->assertTrue($secondMarketing->canManageReceivingPixKey());
        $this->assertSame('pix-marketing-antigo', $firstMarketing->fresh()->pix_key);
        $this->assertNull($secondMarketing->fresh()->pix_key);
    }

    public function test_paid_order_splits_use_current_default_recipients_and_their_pix_keys(): void
    {
        $buyer = $this->user('member', 'buyer@unn.test');
        $seller = $this->user('member', 'seller@unn.test', 'pix-seller');
        $admin = $this->user('admin', 'admin@unn.test', 'pix-admin');
        $superadmin = $this->user('superadmin', 'superadmin@unn.test', 'pix-superadmin');
        $marketing = $this->user('member', 'marketing@unn.test', 'pix-marketing');

        Setting::set('platform_marketing_user_id', (string) $marketing->id);
        Setting::set('marketplace_split_seller_percent', '25');
        Setting::set('marketplace_split_platform_percent', '25');
        Setting::set('marketplace_split_traffic_percent', '25');
        Setting::set('marketplace_split_superadmin_percent', '25');

        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => 'paid',
            'total_amount' => 100,
            'currency' => 'BRL',
            'gateway' => 'sumup',
        ]);

        app(OrderSplitService::class)->syncForPaidOrder($order);

        $this->assertSplit($order, 'seller', $seller, 'pix-seller', 25, 25, 'pending');
        $this->assertSplit($order, 'platform', $admin, 'pix-admin', 25, 25, 'pending');
        $this->assertSplit($order, 'traffic', $marketing, 'pix-marketing', 25, 25, 'pending');
        $this->assertSplit($order, 'superadmin', $superadmin, 'pix-superadmin', 25, 25, 'pending');
    }

    public function test_admin_seller_keeps_own_shares_without_self_repayment(): void
    {
        $buyer = $this->user('member', 'buyer-admin-sale@unn.test');
        $adminSeller = $this->user('admin', 'admin-seller@unn.test', 'pix-admin-seller');
        $superadmin = $this->user('superadmin', 'superadmin-admin-sale@unn.test', 'pix-superadmin');
        $marketing = $this->user('member', 'marketing-admin-sale@unn.test', 'pix-marketing');

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

        $this->assertSame(1, OrderSplit::where('order_id', $order->id)->count());
        $this->assertSplit($order, 'seller', $adminSeller, 'pix-admin-seller', 100, 100, 'paid');
        $this->assertDatabaseMissing('order_splits', [
            'order_id' => $order->id,
            'receiver_type' => 'traffic',
        ]);
        $this->assertDatabaseMissing('order_splits', [
            'order_id' => $order->id,
            'receiver_type' => 'superadmin',
        ]);
        $this->assertSame('0.00', $order->fresh()->platform_fee_amount);
        $this->assertSame(0.0, (float) data_get($order->fresh()->metadata, 'platform_fee_percent'));
    }

    public function test_superadmin_seller_keeps_superadmin_share_and_only_deducts_external_shares(): void
    {
        $buyer = $this->user('member', 'buyer-superadmin-sale@unn.test');
        $admin = $this->user('admin', 'admin-superadmin-sale@unn.test', 'pix-admin');
        $superadminSeller = $this->user('superadmin', 'superadmin-seller@unn.test', 'pix-superadmin-seller');
        $marketing = $this->user('member', 'marketing-superadmin-sale@unn.test', 'pix-marketing');

        Setting::set('platform_marketing_user_id', (string) $marketing->id);

        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $superadminSeller->id,
            'status' => 'paid',
            'total_amount' => 100,
            'currency' => 'BRL',
            'gateway' => 'sumup',
        ]);

        app(OrderSplitService::class)->syncForPaidOrder($order);

        $this->assertSame(1, OrderSplit::where('order_id', $order->id)->count());
        $this->assertSplit($order, 'seller', $superadminSeller, 'pix-superadmin-seller', 100, 100, 'paid');
        $this->assertDatabaseMissing('order_splits', [
            'order_id' => $order->id,
            'receiver_type' => 'platform',
        ]);
        $this->assertDatabaseMissing('order_splits', [
            'order_id' => $order->id,
            'receiver_type' => 'traffic',
        ]);
        $this->assertSame('0.00', $order->fresh()->platform_fee_amount);
        $this->assertSame(0.0, (float) data_get($order->fresh()->metadata, 'platform_fee_percent'));
    }

    public function test_marketing_manager_seller_keeps_traffic_share_and_only_deducts_external_shares(): void
    {
        $buyer = $this->user('member', 'buyer-marketing-sale@unn.test');
        $sellerMarketing = $this->user('member', 'seller-marketing@unn.test', 'pix-seller-marketing');
        $admin = $this->user('admin', 'admin-marketing-sale@unn.test', 'pix-admin');
        $superadmin = $this->user('superadmin', 'superadmin-marketing-sale@unn.test', 'pix-superadmin');

        Setting::set('platform_marketing_user_id', (string) $sellerMarketing->id);

        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $sellerMarketing->id,
            'status' => 'paid',
            'total_amount' => 100,
            'currency' => 'BRL',
            'gateway' => 'sumup',
        ]);

        app(OrderSplitService::class)->syncForPaidOrder($order);

        $this->assertSame(1, OrderSplit::where('order_id', $order->id)->count());
        $this->assertSplit($order, 'seller', $sellerMarketing, 'pix-seller-marketing', 100, 100, 'paid');
        $this->assertDatabaseMissing('order_splits', [
            'order_id' => $order->id,
            'receiver_type' => 'platform',
        ]);
        $this->assertDatabaseMissing('order_splits', [
            'order_id' => $order->id,
            'receiver_type' => 'superadmin',
        ]);
        $this->assertSame('0.00', $order->fresh()->platform_fee_amount);
        $this->assertSame(0.0, (float) data_get($order->fresh()->metadata, 'platform_fee_percent'));
    }

    public function test_member_with_platform_fee_disabled_keeps_full_sale_value(): void
    {
        $buyer = $this->user('member', 'buyer-fee-disabled@unn.test');
        $seller = $this->user('member', 'seller-fee-disabled@unn.test', 'pix-seller');
        $this->user('admin', 'admin-fee-disabled@unn.test', 'pix-admin');
        $this->user('superadmin', 'superadmin-fee-disabled@unn.test', 'pix-superadmin');
        $marketing = $this->user('member', 'marketing-fee-disabled@unn.test', 'pix-marketing');

        $seller->forceFill(['platform_fee_enabled' => false])->save();
        Setting::set('platform_marketing_user_id', (string) $marketing->id);

        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => 'paid',
            'total_amount' => 250,
            'currency' => 'BRL',
            'gateway' => 'sumup',
        ]);

        app(OrderSplitService::class)->syncForPaidOrder($order);

        $this->assertSame(1, OrderSplit::where('order_id', $order->id)->count());
        $this->assertSplit($order, 'seller', $seller, 'pix-seller', 250, 100, 'paid');
        $this->assertSame('0.00', $order->fresh()->platform_fee_amount);
        $this->assertSame(0.0, (float) data_get($order->fresh()->metadata, 'platform_fee_percent'));
    }

    public function test_free_paid_order_has_no_splits_and_updates_effective_percentage(): void
    {
        $buyer = $this->user('member', 'buyer-free-sale@unn.test');
        $adminSeller = $this->user('admin', 'admin-free-sale@unn.test', 'pix-admin');
        $this->user('superadmin', 'superadmin-free-sale@unn.test', 'pix-superadmin');
        $marketing = $this->user('member', 'marketing-free-sale@unn.test', 'pix-marketing');

        Setting::set('platform_marketing_user_id', (string) $marketing->id);

        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $adminSeller->id,
            'status' => 'paid',
            'total_amount' => 0,
            'platform_fee_amount' => 30,
            'currency' => 'BRL',
            'gateway' => 'free',
            'metadata' => ['platform_fee_percent' => 30],
        ]);

        app(OrderSplitService::class)->syncForPaidOrder($order);

        $this->assertSame(0, OrderSplit::where('order_id', $order->id)->count());
        $this->assertSame('0.00', $order->fresh()->platform_fee_amount);
        $this->assertSame(0.0, (float) data_get($order->fresh()->metadata, 'platform_fee_percent'));
    }

    public function test_regular_member_cannot_change_pix_key_through_profile_endpoint(): void
    {
        $member = $this->user('member', 'member-profile@unn.test', 'pix-original');

        $this->withoutMiddleware()
            ->actingAs($member)
            ->post(route('panel.profile.update'), [
                'name' => $member->name,
                'email' => $member->email,
                'pix_key' => 'pix-nao-autorizado',
            ])
            ->assertRedirect(route('panel.profile.edit'));

        $this->assertSame('pix-original', $member->fresh()->pix_key);
    }

    public function test_pix_key_field_is_hidden_for_member_and_visible_for_marketing_manager(): void
    {
        $member = $this->user('member', 'member-view@unn.test');
        $marketing = $this->user('member', 'marketing-view@unn.test');

        $this->withoutMiddleware()
            ->actingAs($member)
            ->get(route('panel.profile.edit'))
            ->assertOk()
            ->assertDontSee('name="pix_key"', false);

        Setting::set('platform_marketing_user_id', (string) $marketing->id);

        $this->actingAs($marketing)
            ->get(route('panel.profile.edit'))
            ->assertOk()
            ->assertSee('name="pix_key"', false);
    }

    public function test_authorized_profile_requires_pix_key(): void
    {
        $admin = $this->user('admin', 'admin-required-pix@unn.test');

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->post(route('panel.profile.update'), [
                'name' => $admin->name,
                'email' => $admin->email,
            ])
            ->assertSessionHasErrors('pix_key');
    }

    private function assertSplit(Order $order, string $type, User $receiver, string $pixKey, float $amount, float $percentage, string $status): void
    {
        $this->assertDatabaseHas('order_splits', [
            'order_id' => $order->id,
            'receiver_type' => $type,
            'receiver_id' => $receiver->id,
            'amount' => $amount,
            'percentage' => $percentage,
            'pix_key' => $pixKey,
            'status' => $status,
        ]);
    }

    private function user(string $role, string $email, ?string $pixKey = null): User
    {
        return User::create([
            'name' => $email,
            'email' => $email,
            'password' => bcrypt('secret123'),
            'role' => $role,
            'level' => $role === 'superadmin' ? 'superadmin' : 'iniciante',
            'pix_key' => $pixKey,
            'platform_fee_enabled' => true,
        ]);
    }
}
