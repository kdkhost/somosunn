<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\LegalConsentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class EventReservationDuplicateRegistrationTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Setting::flushRuntimeCache();

        $legalConsentMock = Mockery::mock(LegalConsentService::class);
        $legalConsentMock->shouldReceive('hasAcceptedCurrentVersion')->andReturn(true);
        $this->app->instance(LegalConsentService::class, $legalConsentMock);

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-event-duplicate-registration.sqlite');

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
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->timestamp('flash_sale_ends_at')->nullable();
            $table->decimal('batch_1_price', 10, 2)->nullable();
            $table->timestamp('batch_1_deadline')->nullable();
            $table->decimal('batch_2_price', 10, 2)->nullable();
            $table->timestamp('batch_2_deadline')->nullable();
            $table->decimal('batch_3_price', 10, 2)->nullable();
            $table->timestamp('batch_3_deadline')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('published')->default(false);
            $table->boolean('all_day')->default(false);
            $table->boolean('is_ticket_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('ticket_code')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamp('check_in_at')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'user_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->decimal('platform_fee_amount', 10, 2)->default(0);
            $table->string('currency')->nullable();
            $table->string('gateway')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('rate_limit_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip_address', 45);
            $table->string('reason', 100);
            $table->timestamp('blocked_until');
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('status')->default('inactive');
            $table->string('cycle')->nullable();
            $table->boolean('prorata')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
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

    public function test_free_event_reservation_is_idempotent_when_legacy_unique_index_still_exists(): void
    {
        $user = User::create([
            'name' => 'Evento Duplicado',
            'email' => 'evento-duplicado@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'member',
            'level' => 'iniciante',
        ]);

        $event = Event::create([
            'title' => 'Evento Gratuito',
            'description' => 'Evento com indice unico legado',
            'start_at' => now()->addDay(),
            'published' => true,
            'price' => 0,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total_amount' => 0,
            'currency' => 'BRL',
            'gateway' => 'free',
        ]);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'status' => EventRegistration::STATUS_CONFIRMED,
            'price' => 0,
            'quantity' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('events.reserve', $event), [
                'quantity' => 1,
            ]);

        $response->assertRedirect(route('events.show', $event));
        $response->assertSessionHas('success', function ($message) {
            return is_string($message) && Str::contains(Str::lower($message), 'confirm');
        });
        $this->assertSame(1, EventRegistration::count());
        $this->assertSame(EventRegistration::STATUS_CONFIRMED, EventRegistration::first()->status);
    }
}
