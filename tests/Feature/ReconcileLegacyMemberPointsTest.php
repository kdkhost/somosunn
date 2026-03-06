<?php

namespace Tests\Feature;

use App\Models\PointsLog;
use App\Models\PointsRule;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReconcileLegacyMemberPointsTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-reconcile-legacy-member-points.sqlite');

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
            $table->integer('points')->default(0);
            $table->string('role')->nullable();
            $table->string('level')->nullable();
            $table->string('phone')->nullable();
            $table->string('occupation')->nullable();
            $table->string('company')->nullable();
            $table->text('bio')->nullable();
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

        Schema::create('points_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action_key');
            $table->integer('points');
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('enrollable_id')->nullable();
            $table->string('enrollable_type');
            $table->string('status')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentor_id')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        foreach ([
            ['key' => 'signup', 'label' => 'Cadastro', 'points' => 50],
            ['key' => 'complete_profile', 'label' => 'Perfil completo', 'points' => 30],
            ['key' => 'first_course', 'label' => 'Primeiro curso', 'points' => 100],
            ['key' => 'mentor', 'label' => 'Mentor', 'points' => 100],
        ] as $index => $rule) {
            PointsRule::create([
                'key' => $rule['key'],
                'label' => $rule['label'],
                'category' => 'engajamento',
                'description' => $rule['label'],
                'points' => $rule['points'],
                'active' => true,
                'icon' => 'fa-star',
                'sort_order' => $index,
                'repeatable' => false,
            ]);
        }
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_command_awards_missing_historical_points_without_duplication(): void
    {
        $mentor = User::create([
            'name' => 'Mentor Antigo',
            'email' => 'mentor-antigo@example.com',
            'password' => Hash::make('password'),
            'phone' => '21999990000',
            'occupation' => 'Consultor',
            'company' => 'UNN',
            'bio' => 'Perfil completo do mentor.',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'photo' => 'uploads/avatar.jpg',
        ]);

        DB::table('enrollments')->insert([
            'user_id' => $mentor->id,
            'enrollable_id' => 10,
            'enrollable_type' => 'App\\Models\\Course',
            'status' => 'completed',
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('mentorships')->insert([
            'mentor_id' => $mentor->id,
            'title' => 'Mentoria Antiga',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $partial = User::create([
            'name' => 'Membro Parcial',
            'email' => 'membro-parcial@example.com',
            'password' => Hash::make('password'),
        ]);

        $existing = User::create([
            'name' => 'Membro Já Pontuado',
            'email' => 'membro-ja-pontuado@example.com',
            'password' => Hash::make('password'),
            'points' => 50,
        ]);

        PointsLog::create([
            'user_id' => $existing->id,
            'action_key' => 'signup',
            'points' => 50,
            'meta' => json_encode(['source' => 'original']),
        ]);

        Artisan::call('points:reconcile-legacy-members');

        $mentor->refresh();
        $partial->refresh();
        $existing->refresh();

        $this->assertSame(280, (int) $mentor->points);
        $this->assertSame(50, (int) $partial->points);
        $this->assertSame(50, (int) $existing->points);

        $this->assertEqualsCanonicalizing(
            ['signup', 'complete_profile', 'first_course', 'mentor'],
            PointsLog::where('user_id', $mentor->id)->pluck('action_key')->all()
        );

        Artisan::call('points:reconcile-legacy-members');

        $mentor->refresh();
        $partial->refresh();
        $existing->refresh();

        $this->assertSame(280, (int) $mentor->points);
        $this->assertSame(50, (int) $partial->points);
        $this->assertSame(50, (int) $existing->points);
        $this->assertSame(6, PointsLog::count());
    }

    public function test_command_supports_dry_run_without_changing_points(): void
    {
        $user = User::create([
            'name' => 'Usuário Dry Run',
            'email' => 'dry-run@example.com',
            'password' => Hash::make('password'),
            'phone' => '21999990001',
            'occupation' => 'Empresário',
            'company' => 'UNN',
            'bio' => 'Perfil completo para simulação.',
            'city' => 'Niterói',
            'state' => 'RJ',
            'photo' => 'uploads/avatar-dry.jpg',
        ]);

        Artisan::call('points:reconcile-legacy-members', ['--dry-run' => true]);

        $user->refresh();

        $this->assertSame(0, (int) $user->points);
        $this->assertSame(0, PointsLog::count());
    }
}
