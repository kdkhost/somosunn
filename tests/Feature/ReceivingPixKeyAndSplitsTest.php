<?php

namespace Tests\Feature;

use App\Http\Controllers\PaymentWebhookController;
use App\Models\Order;
use App\Models\OrderSplit;
use App\Models\Setting;
use App\Models\User;
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

        Setting::set('platform_marketing_user_id', (string) $firstMarketing->id);

        $this->assertTrue($admin->canManageReceivingPixKey());
        $this->assertTrue($superadmin->canManageReceivingPixKey());
        $this->assertTrue($firstMarketing->canManageReceivingPixKey());
        $this->assertFalse($secondMarketing->canManageReceivingPixKey());
        $this->assertFalse($member->canManageReceivingPixKey());

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

        $method = new \ReflectionMethod(PaymentWebhookController::class, 'calculateAndSaveSplits');
        $method->setAccessible(true);
        $method->invoke(app(PaymentWebhookController::class), $order);

        $this->assertSplit($order, 'seller', $seller, 'pix-seller');
        $this->assertSplit($order, 'platform', $admin, 'pix-admin');
        $this->assertSplit($order, 'traffic', $marketing, 'pix-marketing');
        $this->assertSplit($order, 'superadmin', $superadmin, 'pix-superadmin');
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

    private function assertSplit(Order $order, string $type, User $receiver, string $pixKey): void
    {
        $this->assertDatabaseHas('order_splits', [
            'order_id' => $order->id,
            'receiver_type' => $type,
            'receiver_id' => $receiver->id,
            'amount' => 25,
            'percentage' => 25,
            'pix_key' => $pixKey,
            'status' => 'pending',
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
        ]);
    }
}
