<?php

namespace Tests\Unit;

use App\Http\Controllers\PaymentWebhookController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentWebhookPlanPeriodTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-payment-webhook-plan-period.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->string('referral_code')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('period')->default('mensal');
            $table->string('billing_cycle')->nullable();
            $table->boolean('is_free')->default(false);
            $table->json('permissions')->nullable();
            $table->json('benefits')->nullable();
            $table->json('price_periods')->nullable();
            $table->json('period_settings')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->decimal('platform_fee_amount', 10, 2)->default(0);
            $table->string('currency')->nullable();
            $table->string('gateway')->nullable();
            $table->json('metadata')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('item_type');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('title')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        config()->set('database.default', $this->originalDefaultConnection);
        config()->set('database.connections.sqlite.database', $this->originalSqliteDatabase);

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_activate_plan_for_order_uses_item_period_before_plan_default(): void
    {
        $user = User::create([
            'name' => 'Teste',
            'email' => 'teste-periodo-item@example.com',
        ]);

        $plan = Plan::create([
            'name' => 'Plano Pro',
            'slug' => 'pro',
            'price' => 97,
            'period' => 'mensal',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 249.9,
            'metadata' => ['period' => 'anual'],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'plan',
            'item_id' => $plan->id,
            'title' => 'Plano Pro',
            'price' => 249.9,
            'quantity' => 1,
            'data' => ['period' => 'trimestral'],
        ]);

        $method = new \ReflectionMethod(PaymentWebhookController::class, 'activatePlanForOrder');
        $method->setAccessible(true);
        $method->invoke(app(PaymentWebhookController::class), $order->fresh());

        $user->refresh();

        $this->assertSame($plan->id, $user->plan_id);
        $this->assertNotNull($user->plan_expires_at);
        $this->assertTrue($user->plan_expires_at->between(
            now()->addMonths(3)->subMinute(),
            now()->addMonths(3)->addMinute()
        ));
    }

    public function test_activate_plan_for_order_falls_back_to_order_metadata_period(): void
    {
        $user = User::create([
            'name' => 'Teste',
            'email' => 'teste-periodo-metadata@example.com',
        ]);

        $plan = Plan::create([
            'name' => 'Plano Elite',
            'slug' => 'elite',
            'price' => 297,
            'period' => 'mensal',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 899.9,
            'metadata' => ['period' => 'anual'],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'plan',
            'item_id' => $plan->id,
            'title' => 'Plano Elite',
            'price' => 899.9,
            'quantity' => 1,
            'data' => [],
        ]);

        $method = new \ReflectionMethod(PaymentWebhookController::class, 'activatePlanForOrder');
        $method->setAccessible(true);
        $method->invoke(app(PaymentWebhookController::class), $order->fresh());

        $user->refresh();

        $this->assertTrue($user->plan_expires_at->between(
            now()->addYear()->subMinute(),
            now()->addYear()->addMinute()
        ));
    }
}
