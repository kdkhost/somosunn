<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PanelAdminEventsTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-panel-admin-events.sqlite');

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
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->json('extra_features')->nullable();
            $table->string('referral_code')->nullable()->unique();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->string('location')->nullable();
            $table->text('address')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->dateTime('flash_sale_ends_at')->nullable();
            $table->integer('capacity')->nullable();
            $table->boolean('published')->default(false);
            $table->string('color', 7)->nullable()->default('#3788d8');
            $table->boolean('all_day')->default(false);
            $table->boolean('is_certificate_enabled')->default(false);
            $table->json('certificate_settings')->nullable();
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

    public function test_panel_admin_events_feed_returns_only_current_user_events_for_instructor_scope(): void
    {
        $user = User::create([
            'name' => 'Produtor de Eventos',
            'email' => 'produtor-eventos@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'extra_features' => ['events_access'],
        ]);

        $otherUser = User::create([
            'name' => 'Outro Produtor',
            'email' => 'outro-produtor@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'extra_features' => ['events_access'],
        ]);

        $ownEvent = Event::create([
            'user_id' => $user->id,
            'title' => 'Evento do painel',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'published' => false,
            'color' => '#3b82f6',
        ]);

        Event::create([
            'user_id' => $otherUser->id,
            'title' => 'Evento de outro usuário',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHours(2),
            'published' => true,
            'color' => '#ef4444',
        ]);

        $start = now()->startOfDay()->toIso8601String();
        $end = now()->addDays(7)->endOfDay()->toIso8601String();

        $this->withoutMiddleware()
            ->actingAs($user)
            ->getJson(route('panel.admin.events.feed', ['start' => $start, 'end' => $end]))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $ownEvent->id,
                'title' => $ownEvent->title,
            ])
            ->assertJsonMissing([
                'title' => 'Evento de outro usuário',
            ]);
    }

    public function test_panel_admin_events_store_persists_event_data_from_new_form(): void
    {
        $user = User::create([
            'name' => 'Criador de Eventos',
            'email' => 'criador-eventos@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
            'extra_features' => ['events_access'],
        ]);

        $response = $this->withoutMiddleware()
            ->actingAs($user)
            ->post(route('panel.admin.events.store'), [
                'title' => 'Evento novo do painel',
                'start_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'end_at' => now()->addDays(2)->addHour()->format('Y-m-d\TH:i'),
                'price' => '1.234,56',
                'flash_sale_price' => '99,90',
                'flash_sale_ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'published' => '1',
                'color' => '#2563eb',
                'description' => 'Evento criado pelo formulário do painel novo.',
            ]);

        $response->assertRedirect(route('panel.admin.events.index'));

        $event = Event::query()->where('title', 'Evento novo do painel')->firstOrFail();

        $this->assertSame($user->id, (int) $event->user_id);
        $this->assertTrue((bool) $event->published);
        $this->assertSame(1234.56, (float) $event->price);
        $this->assertSame(99.90, (float) $event->flash_sale_price);
    }
}
