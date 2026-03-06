<?php

namespace Tests\Feature;

use App\Http\Controllers\Panel\DashboardController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Tests\TestCase;

class PanelDashboardSellerHealthFallbackTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-panel-dashboard-seller-health-fallback.sqlite');

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
            $table->decimal('price', 10, 2)->default(0);
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
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->json('extra_features')->nullable();
            $table->boolean('hide_profile')->default(false);
            $table->string('interests')->nullable();
            $table->string('phone')->nullable();
            $table->string('occupation')->nullable();
            $table->string('company')->nullable();
            $table->text('bio')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('photo')->nullable();
            $table->string('referral_code')->nullable()->unique();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('status')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('platform_fee_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id')->nullable();
            $table->unsignedBigInteger('requested_id')->nullable();
            $table->timestamps();
        });

        Schema::create('points_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action_key');
            $table->integer('points')->default(0);
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('redeemable_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        DB::table('plans')->insert([
            'id' => 1,
            'name' => 'Plano Pago',
            'price' => 49.90,
            'permissions' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_dashboard_does_not_break_when_seller_health_columns_are_missing(): void
    {
        $seller = User::create([
            'name' => 'Vendedor',
            'email' => 'seller-dashboard@example.com',
            'password' => Hash::make('password'),
            'plan_id' => 1,
            'extra_features' => ['marketplace.sell'],
        ]);

        $this->be($seller);

        $view = app(DashboardController::class)->index();

        $this->assertInstanceOf(View::class, $view);
        $data = $view->getData();

        $this->assertArrayHasKey('sellerHealthChecks', $data);
        $this->assertSame([], $data['sellerHealthChecks']);
        $this->assertArrayHasKey('myHealthDetails', $data);
        $this->assertArrayNotHasKey('catalogo_resgate_ativo', $data['myHealthDetails']);
    }
}
