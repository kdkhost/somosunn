<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PanelMarketplaceAccountingExportTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-panel-marketplace-accounting.sqlite');

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
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('requested_id');
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('manual_approved_at')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->decimal('platform_fee_amount', 10, 2)->default(0);
            $table->text('metadata')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->string('title');
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->text('data')->nullable();
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

    public function test_member_accounting_csv_exports_sales_and_purchases(): void
    {
        $seller = User::create([
            'name' => 'Seller Admin',
            'email' => 'seller-admin@test.com',
            'role' => 'admin',
            'level' => 'superadmin',
        ]);

        $buyer = User::create([
            'name' => 'Buyer User',
            'email' => 'buyer-user@test.com',
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        $otherSeller = User::create([
            'name' => 'Other Seller',
            'email' => 'other-seller@test.com',
            'role' => 'admin',
            'level' => 'superadmin',
        ]);

        $saleOrder = Order::create([
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => 'paid',
            'paid_at' => now(),
            'total_amount' => 150,
            'fee_amount' => 5,
            'platform_fee_amount' => 15,
        ]);
        $saleOrder->items()->create([
            'item_type' => 'event',
            'item_id' => 10,
            'title' => 'Evento Pago',
            'price' => 150,
            'quantity' => 1,
        ]);

        $purchaseOrder = Order::create([
            'user_id' => $seller->id,
            'seller_id' => $otherSeller->id,
            'status' => 'paid',
            'paid_at' => now(),
            'total_amount' => 80,
            'fee_amount' => 0,
            'platform_fee_amount' => 0,
        ]);
        $purchaseOrder->items()->create([
            'item_type' => 'course',
            'item_id' => 20,
            'title' => 'Curso Comprado',
            'price' => 80,
            'quantity' => 1,
        ]);

        $response = $this
            ->actingAs($seller)
            ->get(route('panel.marketplace.accounting.export', ['period' => 'annual']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Contabilidade do Membro', $content);
        $this->assertStringContainsString('#' . $saleOrder->id, $content);
        $this->assertStringContainsString('#' . $purchaseOrder->id, $content);
        $this->assertStringContainsString('Evento Pago', $content);
        $this->assertStringContainsString('Curso Comprado', $content);
    }
}
