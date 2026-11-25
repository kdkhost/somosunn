<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderSplit;
use App\Models\OrderSplitPayout;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderSplitPayoutService;
use App\Services\OrderSplitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderSplitAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::flushRuntimeCache();
    }

    public function test_admin_or_platform_originated_sumup_sale_is_exempt_from_superadmin_commission(): void
    {
        $buyer = $this->makeUser('member', 'comprador@teste.com');
        $admin = $this->makeUser('admin', 'admin@teste.com', 'pix-admin');
        $superadmin = $this->makeUser('superadmin', 'superadmin@teste.com', 'pix-mp-superadmin');

        Setting::set('marketplace_split_superadmin_percent', '10');
        Setting::set('superadmin_commission_pix_key', 'pix-mp-superadmin');
        Setting::set('superadmin_commission_auto_enabled', '1');
        Setting::set('superadmin_commission_payout_mode', 'efi_pix_out');

        // 1) Venda criada pelo administrador
        $adminOrder = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $admin->id,
            'status' => 'paid',
            'total_amount' => 200,
            'currency' => 'BRL',
            'gateway' => 'sumup',
        ]);

        app(OrderSplitService::class)->syncForPaidOrder($adminOrder);

        $this->assertDatabaseMissing('order_splits', [
            'order_id' => $adminOrder->id,
            'receiver_type' => 'superadmin',
        ]);
        $this->assertSame('0.00', $adminOrder->fresh()->platform_fee_amount);

        // 2) Venda institucional sem seller_id (ex.: plano da própria plataforma)
        $platformOrder = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => null,
            'status' => 'paid',
            'total_amount' => 150,
            'currency' => 'BRL',
            'gateway' => 'sumup',
        ]);

        app(OrderSplitService::class)->syncForPaidOrder($platformOrder);

        $this->assertDatabaseMissing('order_splits', [
            'order_id' => $platformOrder->id,
            'receiver_type' => 'superadmin',
        ]);
        $this->assertSame('0.00', $platformOrder->fresh()->platform_fee_amount);
    }

    public function test_member_sumup_sale_creates_pending_superadmin_payout_and_dispatches_efi_pix_out(): void
    {
        $buyer = $this->makeUser('member', 'comprador2@teste.com');
        $memberSeller = $this->makeUser('member', 'mentor@teste.com', 'pix-mentor');
        $admin = $this->makeUser('admin', 'admin2@teste.com', 'pix-admin2');
        $superadmin = $this->makeUser('superadmin', 'superadmin2@teste.com', 'pix-mp-dev@mercadopago.com');

        Setting::set('marketplace_split_seller_percent', '70');
        Setting::set('marketplace_split_platform_percent', '10');
        Setting::set('marketplace_split_traffic_percent', '10');
        Setting::set('marketplace_split_superadmin_percent', '10');
        Setting::set('superadmin_commission_pix_key', 'pix-mp-dev@mercadopago.com');
        Setting::set('superadmin_commission_auto_enabled', '1');
        Setting::set('superadmin_commission_payout_mode', 'efi_pix_out');
        Setting::set('superadmin_commission_payout_delay_days', '0');
        Setting::set('efi_enabled', '1');
        Setting::set('efi_env', 'sandbox');
        Setting::set('efi_client_id', 'client-test');
        Setting::set('efi_client_secret', 'secret-test');
        Setting::set('efi_pix_key', 'pix-empresa-efi@empresa.com');

        Http::fake([
            'https://pix-h.api.efipay.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-efi-token',
                'token_type' => 'Bearer',
            ], 200),
            'https://pix-h.api.efipay.com.br/v2/gn/saldo' => Http::response([
                'saldo' => '500.00',
            ], 200),
            'https://pix-h.api.efipay.com.br/v2/gn/pix/*' => Http::response([
                'idEnvio' => 'UNNSPLIT1',
                'e2eId' => 'E18236120202611250300TESTE123456',
                'status' => 'EM_PROCESSAMENTO',
            ], 200),
        ]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $memberSeller->id,
            'status' => 'paid',
            'paid_at' => now()->subMinute(),
            'total_amount' => 300,
            'currency' => 'BRL',
            'gateway' => 'sumup',
        ]);

        app(OrderSplitService::class)->syncForPaidOrder($order);

        $superSplit = OrderSplit::where('order_id', $order->id)
            ->where('receiver_type', 'superadmin')
            ->firstOrFail();

        $this->assertSame('pending', $superSplit->status);
        $this->assertSame('30.00', (string) $superSplit->amount);
        $this->assertSame('pix-mp-dev@mercadopago.com', $superSplit->pix_key);

        $this->assertDatabaseHas('order_split_payouts', [
            'order_split_id' => $superSplit->id,
            'provider' => 'efi_pix_out',
            'status' => 'pending',
        ]);

        // Executa o processamento automático agendado
        $summary = app(OrderSplitPayoutService::class)->processDueAutomaticPayouts();

        $this->assertSame(1, $summary['processed']);
        $this->assertSame(1, $summary['paid']);
        $this->assertSame(0, $summary['failed']);

        $this->assertDatabaseHas('order_splits', [
            'id' => $superSplit->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('order_split_payouts', [
            'order_split_id' => $superSplit->id,
            'provider' => 'efi_pix_out',
            'status' => 'paid',
            'external_id' => 'E18236120202611250300TESTE123456',
            'attempts' => 1,
        ]);

        // Segunda execução deve ser 100% idempotente (não dispara novamente)
        $secondRun = app(OrderSplitPayoutService::class)->processDueAutomaticPayouts();
        $this->assertSame(0, $secondRun['processed']);
    }

    public function test_insufficient_efi_balance_reschedules_without_failing(): void
    {
        $buyer = $this->makeUser('member', 'comprador3@teste.com');
        $memberSeller = $this->makeUser('member', 'produtor@teste.com', 'pix-produtor');
        $superadmin = $this->makeUser('superadmin', 'superadmin3@teste.com', 'pix-mp-dev3');

        Setting::set('marketplace_split_superadmin_percent', '10');
        Setting::set('superadmin_commission_pix_key', 'pix-mp-dev3');
        Setting::set('superadmin_commission_auto_enabled', '1');
        Setting::set('superadmin_commission_payout_mode', 'efi_pix_out');
        Setting::set('superadmin_commission_payout_delay_days', '0');
        Setting::set('efi_enabled', '1');
        Setting::set('efi_env', 'sandbox');
        Setting::set('efi_client_id', 'client-test');
        Setting::set('efi_client_secret', 'secret-test');
        Setting::set('efi_pix_key', 'pix-empresa-efi@empresa.com');

        Http::fake([
            'https://pix-h.api.efipay.com.br/oauth/token' => Http::response(['access_token' => 'fake-token'], 200),
            'https://pix-h.api.efipay.com.br/v2/gn/saldo' => Http::response(['saldo' => '5.00'], 200),
        ]);

        $order = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $memberSeller->id,
            'status' => 'paid',
            'paid_at' => now()->subMinute(),
            'total_amount' => 200,
            'currency' => 'BRL',
            'gateway' => 'sumup',
        ]);

        app(OrderSplitService::class)->syncForPaidOrder($order);
        $summary = app(OrderSplitPayoutService::class)->processDueAutomaticPayouts();

        $this->assertSame(1, $summary['processed']);
        $this->assertSame(0, $summary['paid']);
        $this->assertSame(1, $summary['waiting_balance']);
        $this->assertSame(0, $summary['failed']);

        $payout = OrderSplitPayout::whereHas('split', fn ($q) => $q->where('order_id', $order->id)->where('receiver_type', 'superadmin'))->firstOrFail();
        $this->assertSame('pending', $payout->status);
        $this->assertTrue($payout->scheduled_for->isFuture());
    }

    private function makeUser(string $role, string $email, ?string $pixKey = null): User
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
