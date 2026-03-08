<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\GatewayAccount;
use App\Models\Mentorship;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CheckoutGatewayAvailabilityTest extends TestCase
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
        $this->sqlitePath = database_path('testing-checkout-gateway-availability.sqlite');

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
            $table->string('password');
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->string('referral_code')->nullable()->unique();
            $table->json('extra_features')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
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

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->dateTime('flash_sale_ends_at')->nullable();
            $table->string('author_name')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_certificate_enabled')->default(false);
            $table->boolean('video_block_download')->default(false);
            $table->boolean('video_floating_enabled')->default(false);
            $table->integer('video_floating_width')->nullable();
            $table->integer('video_floating_height')->nullable();
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
            $table->dateTime('flash_sale_ends_at')->nullable();
            $table->json('schedule')->nullable();
            $table->integer('slots')->nullable();
            $table->string('type')->nullable();
            $table->string('video_platform')->nullable();
            $table->string('video_link')->nullable();
            $table->string('demo_link')->nullable();
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

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('status')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        Setting::flushRuntimeCache();
        config()->set('database.default', $this->originalDefaultConnection);
        config()->set('database.connections.sqlite.database', $this->originalSqliteDatabase);

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_course_checkout_accepts_global_database_gateway_when_seller_has_incomplete_account(): void
    {
        Setting::set('mercadopago_env', 'production');
        Setting::set('mercadopago_prod_public_key', 'APP_USR-TEST-PUBLIC');
        Setting::set('mercadopago_prod_access_token', 'APP_USR-TEST-TOKEN');

        $seller = $this->createAdminUser('seller-course@test.com');
        $buyer = $this->createAdminUser('buyer-course@test.com');

        GatewayAccount::create([
            'user_id' => $seller->id,
            'provider' => 'mercadopago',
            'enabled' => true,
            'public_key' => null,
            'access_token' => null,
        ]);

        $course = Course::create([
            'user_id' => $seller->id,
            'title' => 'Curso com gateway global',
            'slug' => 'curso-com-gateway-global',
            'price' => 149.90,
            'status' => 'published',
            'published' => true,
        ]);

        $this->actingAs($buyer)
            ->get(route('checkout.show', $course))
            ->assertOk()
            ->assertSee('Finalizar compra')
            ->assertDontSee('ainda não configurou um método de pagamento');
    }

    public function test_mentorship_checkout_accepts_global_database_gateway_when_seller_has_no_account(): void
    {
        Setting::set('mercadopago_env', 'production');
        Setting::set('mercadopago_prod_public_key', 'APP_USR-TEST-PUBLIC');
        Setting::set('mercadopago_prod_access_token', 'APP_USR-TEST-TOKEN');

        $seller = $this->createAdminUser('seller-mentorship@test.com');
        $buyer = $this->createAdminUser('buyer-mentorship@test.com');

        $mentorship = Mentorship::create([
            'mentor_id' => $seller->id,
            'title' => 'Mentoria com gateway global',
            'price' => 299.90,
            'type' => 'online',
        ]);

        $this->actingAs($buyer)
            ->get(route('mentorships.checkout.show', $mentorship))
            ->assertOk()
            ->assertSee('Finalizar compra')
            ->assertDontSee('ainda não configurou um método de pagamento');
    }

    private function createAdminUser(string $email): User
    {
        return User::create([
            'name' => 'Usuario Teste',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }
}
