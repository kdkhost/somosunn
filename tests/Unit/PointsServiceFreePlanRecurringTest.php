<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\PointsLog;
use App\Models\PointsRule;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PointsServiceFreePlanRecurringTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-points-service-free-plan.sqlite');

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
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->json('extra_features')->nullable();
            $table->integer('points')->default(0);
            $table->string('referral_code')->nullable()->unique();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('points_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('category')->nullable();
            $table->integer('points')->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('repeatable')->default(false);
            $table->unsignedInteger('max_daily')->nullable();
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

    public function test_free_plan_user_does_not_receive_recurring_points(): void
    {
        $plan = Plan::create([
            'name' => 'Plano Gratuito',
            'price' => 0,
            'is_free' => true,
            'permissions' => ['community'],
        ]);

        $user = User::create([
            'name' => 'Membro Gratuito',
            'email' => 'gratuito@example.com',
            'password' => Hash::make('password'),
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth(),
        ]);

        PointsRule::create([
            'key' => 'daily_login',
            'label' => 'Login diário',
            'category' => 'engajamento',
            'points' => 5,
            'active' => true,
            'repeatable' => true,
        ]);

        $awarded = app(PointsService::class)->award($user, 'daily_login');

        $this->assertFalse($awarded);
        $this->assertSame(0, PointsLog::count());
        $this->assertSame(0, (int) $user->fresh()->points);
    }

    public function test_free_plan_user_can_still_receive_non_recurring_points(): void
    {
        $plan = Plan::create([
            'name' => 'Plano Gratuito',
            'price' => 0,
            'is_free' => true,
            'permissions' => ['community'],
        ]);

        $user = User::create([
            'name' => 'Membro Gratuito',
            'email' => 'gratuito-unico@example.com',
            'password' => Hash::make('password'),
            'plan_id' => $plan->id,
            'plan_expires_at' => now()->addMonth(),
        ]);

        PointsRule::create([
            'key' => 'signup',
            'label' => 'Cadastro',
            'category' => 'conquistas',
            'points' => 50,
            'active' => true,
            'repeatable' => false,
        ]);

        $awarded = app(PointsService::class)->award($user, 'signup');

        $this->assertTrue($awarded);
        $this->assertSame(1, PointsLog::count());
        $this->assertSame(50, (int) $user->fresh()->points);
    }
}
