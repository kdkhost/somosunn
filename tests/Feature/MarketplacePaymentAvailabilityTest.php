<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\GatewayAccount;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MarketplacePaymentAvailabilityTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::flushRuntimeCache();
        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-marketplace-payment-availability.sqlite');

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
            $table->json('extra_features')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('gateway_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider');
            $table->string('public_key')->nullable();
            $table->text('access_token')->nullable();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->string('pix_key')->nullable();
            $table->boolean('enabled')->default(true);
            $table->json('extra')->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('speaker')->nullable();
            $table->string('image')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('location')->nullable();
            $table->string('address')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->timestamp('flash_sale_ends_at')->nullable();
            $table->decimal('batch_1_price', 10, 2)->nullable();
            $table->timestamp('batch_1_deadline')->nullable();
            $table->decimal('batch_2_price', 10, 2)->nullable();
            $table->timestamp('batch_2_deadline')->nullable();
            $table->decimal('batch_3_price', 10, 2)->nullable();
            $table->timestamp('batch_3_deadline')->nullable();
            $table->boolean('published')->default(false);
            $table->boolean('all_day')->default(false);
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('status')->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentor_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->timestamp('flash_sale_ends_at')->nullable();
            $table->json('schedule')->nullable();
            $table->integer('slots')->default(0);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('status')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('logo')->nullable();
            $table->string('website_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        Setting::flushRuntimeCache();
        config()->set('database.default', $this->originalDefaultConnection);
        config()->set('database.connections.sqlite.database', $this->originalSqliteDatabase);

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_marketplace_keeps_future_event_buy_action_enabled_with_seller_gateway(): void
    {
        $seller = User::create([
            'name' => 'Organizador Teste',
            'email' => 'organizador@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'level' => 'iniciante',
        ]);

        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'mercadopago',
            'public_key' => 'TEST-PUBLIC-KEY-123',
            'access_token' => 'TEST-ACCESS-TOKEN-123',
            'enabled' => true,
        ]);

        $event = Event::create([
            'user_id' => $seller->id,
            'title' => 'Evento futuro com MercadoPago',
            'description' => 'Evento disponível para compra',
            'start_at' => now()->addDays(4)->setTime(13, 0),
            'published' => true,
            'price' => 150,
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertOk();
        $response->assertSee('Evento futuro com MercadoPago');
        $response->assertSee(route('events.checkout', $event), false);
        $response->assertDontSee('Compras pagas indisponíveis no momento');
    }
    public function test_marketplace_keeps_future_event_buy_action_enabled_with_global_gateway_and_legacy_null_owner(): void
    {
        Setting::set('mercadopago_env', 'production');
        Setting::set('mercadopago_prod_public_key', 'APP_USR-TEST-PUBLIC');
        Setting::set('mercadopago_prod_access_token', 'APP_USR-TEST-TOKEN');

        $event = Event::create([
            'user_id' => null,
            'title' => 'Evento legado com gateway global',
            'description' => 'Evento disponivel para compra com fallback global',
            'start_at' => now()->addDays(2)->setTime(19, 0),
            'published' => true,
            'price' => 180,
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertOk();
        $response->assertSee('Evento legado com gateway global');
        $response->assertSee(route('events.checkout', $event), false);
        $response->assertDontSee('Organizador indispon');
        $response->assertDontSee('Pagamento indispon');
    }

    public function test_marketplace_keeps_future_event_buy_action_enabled_even_when_organizer_has_no_sell_permission(): void
    {
        $seller = User::create([
            'name' => 'Organizador sem permissao',
            'email' => 'organizador-sem-permissao@test.com',
            'password' => bcrypt('password'),
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'mercadopago',
            'public_key' => 'TEST-PUBLIC-KEY-456',
            'access_token' => 'TEST-ACCESS-TOKEN-456',
            'enabled' => true,
        ]);

        $event = Event::create([
            'user_id' => $seller->id,
            'title' => 'Evento com organizador sem permissao',
            'description' => 'Evento publicado e com gateway configurado',
            'start_at' => now()->addDays(5)->setTime(18, 0),
            'published' => true,
            'price' => 120,
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertOk();
        $response->assertSee('Evento com organizador sem permissao');
        $response->assertSee(route('events.checkout', $event), false);
        $response->assertDontSee('Organizador indispon');
    }
}
