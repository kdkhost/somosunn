<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\RedeemableItem;
use App\Models\Redemption;
use App\Models\User;
use App\Notifications\RedemptionRequestedForProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PanelAdminSellerRedemptionsTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-panel-admin-seller-redemptions.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_free')->default(false);
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->integer('points')->default(0);
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('occupation')->nullable();
            $table->string('company')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->json('extra_features')->nullable();
            $table->string('referral_code')->nullable()->unique();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('redeemable_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('points_cost');
            $table->integer('stock')->default(-1);
            $table->boolean('is_active')->default(true);
            $table->string('provider_type', 20)->default('platform');
            $table->unsignedBigInteger('provider_user_id')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('item_type', 20)->default('service');
            $table->text('fulfillment_instructions')->nullable();
            $table->decimal('reference_value', 10, 2)->nullable();
            $table->unsignedInteger('delivery_lead_days')->default(7);
            $table->timestamps();
        });

        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('redeemable_item_id');
            $table->string('provider_type', 20)->default('platform');
            $table->unsignedBigInteger('provider_user_id')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('item_type', 20)->nullable();
            $table->integer('points_spent');
            $table->decimal('reference_value', 10, 2)->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('fulfillment_instructions')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('tracking_url')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('points_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action_key');
            $table->integer('points');
            $table->text('meta')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_seller_can_create_redemption_item_with_locked_provider_and_points_from_money(): void
    {
        $plan = Plan::create([
            'name' => 'Plano Pago',
            'slug' => 'plano-pago',
            'price' => 49.90,
            'is_free' => false,
            'permissions' => ['community'],
        ]);

        $seller = User::create([
            'name' => 'Vendedor Teste',
            'email' => 'seller-redemption@example.com',
            'password' => Hash::make('password'),
            'role' => 'membro',
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth(),
            'extra_features' => ['marketplace.sell'],
        ]);

        $this->actingAs($seller)
            ->post(route('panel.admin.redemptions.store'), [
                'name' => 'Kit Premium',
                'description' => 'Produto fisico para teste.',
                'reference_value' => '10,00',
                'stock' => 5,
                'delivery_lead_days' => 7,
                'is_active' => 1,
                'item_type' => 'physical',
                'fulfillment_instructions' => 'Enviar apos combinar endereco.',
            ])
            ->assertRedirect(route('panel.admin.redemptions.index'));

        $item = RedeemableItem::firstOrFail();

        $this->assertSame('seller', $item->provider_type);
        $this->assertSame($seller->id, $item->provider_user_id);
        $this->assertSame($seller->name, $item->provider_name);
        $this->assertSame(1000, (int) $item->points_cost);
        $this->assertSame('physical', $item->item_type);
        $this->assertSame('Enviar apos combinar endereco.', $item->fulfillment_instructions);

        $this->actingAs($seller)
            ->put(route('panel.admin.redemptions.update', $item), [
                'name' => 'Kit Premium Atualizado',
                'description' => 'Produto fisico atualizado.',
                'reference_value' => '12,00',
                'stock' => 8,
                'delivery_lead_days' => 10,
                'is_active' => 1,
                'provider_name' => 'Outro Nome',
                'item_type' => 'digital',
                'fulfillment_instructions' => 'Liberar download apos confirmacao.',
            ])
            ->assertRedirect(route('panel.admin.redemptions.index'));

        $item->refresh();

        $this->assertSame($seller->name, $item->provider_name);
        $this->assertSame(1200, (int) $item->points_cost);
        $this->assertSame('digital', $item->item_type);
        $this->assertSame('Liberar download apos confirmacao.', $item->fulfillment_instructions);
    }

    public function test_redemption_snapshots_provider_delivery_window_and_notifies_provider(): void
    {
        Notification::fake();

        $plan = Plan::create([
            'name' => 'Plano Ativo',
            'slug' => 'plano-ativo',
            'price' => 49.90,
            'is_free' => false,
            'permissions' => ['community'],
        ]);

        $seller = User::create([
            'name' => 'Fornecedor',
            'email' => 'supplier@example.com',
            'password' => Hash::make('password'),
            'role' => 'membro',
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth(),
            'extra_features' => ['marketplace.sell'],
        ]);

        $buyer = User::create([
            'name' => 'Comprador',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password'),
            'role' => 'membro',
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth(),
            'points' => 5000,
        ]);

        $item = RedeemableItem::create([
            'name' => 'Produto com Entrega',
            'description' => 'Item com rastreio.',
            'points_cost' => 1200,
            'reference_value' => 12.00,
            'stock' => 3,
            'is_active' => true,
            'provider_type' => 'seller',
            'provider_user_id' => $seller->id,
            'provider_name' => $seller->name,
            'item_type' => 'digital',
            'fulfillment_instructions' => 'Entregar o acesso por email.',
            'delivery_lead_days' => 9,
        ]);

        $this->actingAs($buyer)
            ->post(route('panel.redemptions.redeem', $item))
            ->assertRedirect(route('panel.redemptions.shop'));

        $redemption = Redemption::with(['providerUser'])->firstOrFail();

        $this->assertSame($seller->id, $redemption->provider_user_id);
        $this->assertSame($seller->name, $redemption->provider_name);
        $this->assertSame(12.00, (float) $redemption->reference_value);
        $this->assertNotNull($redemption->estimated_delivery_at);
        $this->assertSame('pending', $redemption->status);
        $this->assertSame('digital', $redemption->item_type);
        $this->assertSame('Entregar o acesso por email.', $redemption->fulfillment_instructions);
        $this->assertSame(3800, (int) $buyer->fresh()->points);
        $this->assertSame(2, (int) $item->fresh()->stock);

        Notification::assertSentTo(
            $seller,
            RedemptionRequestedForProvider::class,
            function (RedemptionRequestedForProvider $notification) use ($redemption): bool {
                $payload = $notification->toArray($redemption->providerUser);

                return $payload['redemption_id'] === $redemption->id
                    && $payload['item_type'] === 'digital'
                    && $payload['points_spent'] === 1200;
            }
        );
    }
}
