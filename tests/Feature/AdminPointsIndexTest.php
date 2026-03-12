<?php

namespace Tests\Feature;

use App\Models\PointsRule;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class AdminPointsIndexTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-admin-points-index.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
        view()->share('errors', new ViewErrorBag());

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

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('requested_id');
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('points_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('category')->nullable();
            $table->string('description')->nullable();
            $table->integer('points')->default(0);
            $table->boolean('active')->default(true);
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('repeatable')->default(false);
            $table->integer('max_daily')->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamp('start_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
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

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_admin_points_index_displays_recurring_badge_for_repeatable_rules(): void
    {
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin-legacy-points@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        PointsRule::create([
            'key' => 'login_diario',
            'label' => 'Login diario',
            'category' => 'engajamento',
            'description' => 'Pontua o usuario ao entrar na plataforma.',
            'points' => 10,
            'active' => true,
            'icon' => 'fa-star',
            'sort_order' => 1,
            'repeatable' => true,
            'max_daily' => 1,
        ]);

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->get(route('admin.points-rules.index'))
            ->assertOk()
            ->assertSee('Login diario')
            ->assertSee('Recorrente')
            ->assertSee('fa-sync-alt', false)
            ->assertSee('max 1/dia');
    }
}
