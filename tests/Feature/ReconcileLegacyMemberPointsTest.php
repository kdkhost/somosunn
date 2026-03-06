<?php

namespace Tests\Feature;

use App\Models\PointsLog;
use App\Models\PointsRule;
use App\Models\User;
use Carbon\Carbon;
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
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentor_id')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('mentorship_id')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('cert_hash')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->integer('workload')->nullable();
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('status')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamps();
        });

        Schema::create('item_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('reviewable_type');
            $table->unsignedBigInteger('reviewable_id');
            $table->integer('rating')->nullable();
            $table->text('comment')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('moderated_by')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->text('moderation_notes')->nullable();
            $table->timestamps();
        });

        foreach ([
            ['key' => 'signup', 'label' => 'Cadastro', 'points' => 50, 'repeatable' => false],
            ['key' => 'complete_profile', 'label' => 'Perfil completo', 'points' => 30, 'repeatable' => false],
            ['key' => 'first_course', 'label' => 'Primeiro curso', 'points' => 100, 'repeatable' => false],
            ['key' => 'mentor', 'label' => 'Mentor', 'points' => 100, 'repeatable' => false],
            ['key' => 'complete_course', 'label' => 'Curso concluído', 'points' => 100, 'repeatable' => true],
            ['key' => 'earn_certificate', 'label' => 'Certificado emitido', 'points' => 50, 'repeatable' => true],
            ['key' => 'attend_event', 'label' => 'Evento confirmado', 'points' => 30, 'repeatable' => true],
            ['key' => 'attend_mentorship', 'label' => 'Mentoria registrada', 'points' => 40, 'repeatable' => true],
            ['key' => 'review', 'label' => 'Avaliação enviada', 'points' => 10, 'repeatable' => true],
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
                'repeatable' => $rule['repeatable'],
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

    public function test_command_awards_missing_historical_points_from_real_records_without_duplication(): void
    {
        $baseTime = Carbon::parse('2026-03-01 10:00:00');

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
            [
                'user_id' => $mentor->id,
                'enrollable_id' => 10,
                'enrollable_type' => 'App\\Models\\Course',
                'status' => 'completed',
                'started_at' => $baseTime->copy()->subDays(5),
                'completed_at' => $baseTime->copy()->subDays(1),
                'created_at' => $baseTime->copy()->subDays(6),
                'updated_at' => $baseTime->copy()->subDays(1),
            ],
            [
                'user_id' => $mentor->id,
                'enrollable_id' => 20,
                'enrollable_type' => 'App\\Models\\Mentorship',
                'status' => 'active',
                'started_at' => $baseTime->copy()->subDays(2),
                'completed_at' => null,
                'created_at' => $baseTime->copy()->subDays(2),
                'updated_at' => $baseTime->copy()->subDays(2),
            ],
        ]);

        DB::table('mentorships')->insert([
            'mentor_id' => $mentor->id,
            'title' => 'Mentoria Antiga',
            'created_at' => $baseTime->copy()->subDays(10),
            'updated_at' => $baseTime->copy()->subDays(10),
        ]);

        DB::table('certificates')->insert([
            'user_id' => $mentor->id,
            'course_id' => 10,
            'mentorship_id' => null,
            'event_id' => null,
            'cert_hash' => 'CERT-10',
            'pdf_path' => 'certificados/cert-10.pdf',
            'issued_at' => $baseTime,
            'workload' => 12,
            'created_at' => $baseTime,
            'updated_at' => $baseTime,
        ]);

        DB::table('event_registrations')->insert([
            'event_id' => 30,
            'user_id' => $mentor->id,
            'order_id' => null,
            'status' => 'confirmed',
            'price' => 0,
            'quantity' => 1,
            'created_at' => $baseTime->copy()->subDays(3),
            'updated_at' => $baseTime->copy()->subDays(3),
        ]);

        DB::table('item_reviews')->insert([
            'user_id' => $mentor->id,
            'reviewable_type' => 'App\\Models\\Course',
            'reviewable_id' => 10,
            'rating' => 5,
            'comment' => 'Avaliação antiga',
            'status' => 'approved',
            'moderated_by' => null,
            'moderated_at' => null,
            'moderation_notes' => null,
            'created_at' => $baseTime->copy()->subHours(5),
            'updated_at' => $baseTime->copy()->subHours(5),
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
            'meta' => json_encode(['source' => 'original'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        Artisan::call('points:reconcile-legacy-members');

        $mentor->refresh();
        $partial->refresh();
        $existing->refresh();

        $this->assertSame(510, (int) $mentor->points);
        $this->assertSame(50, (int) $partial->points);
        $this->assertSame(50, (int) $existing->points);

        $this->assertEqualsCanonicalizing(
            [
                'signup',
                'complete_profile',
                'first_course',
                'mentor',
                'complete_course',
                'earn_certificate',
                'attend_event',
                'attend_mentorship',
                'review',
            ],
            PointsLog::where('user_id', $mentor->id)->pluck('action_key')->all()
        );

        $mentorMetaByAction = PointsLog::where('user_id', $mentor->id)
            ->pluck('meta', 'action_key')
            ->map(fn ($meta) => json_decode($meta, true))
            ->all();

        $this->assertSame(10, $mentorMetaByAction['complete_course']['course_id']);
        $this->assertSame(10, $mentorMetaByAction['earn_certificate']['course_id']);
        $this->assertSame(30, $mentorMetaByAction['attend_event']['event_id']);
        $this->assertSame(20, $mentorMetaByAction['attend_mentorship']['mentorship_id']);
        $this->assertSame('App\\Models\\Course', $mentorMetaByAction['review']['reviewable_type']);
        $this->assertSame(10, $mentorMetaByAction['review']['reviewable_id']);

        Artisan::call('points:reconcile-legacy-members');

        $mentor->refresh();
        $partial->refresh();
        $existing->refresh();

        $this->assertSame(510, (int) $mentor->points);
        $this->assertSame(50, (int) $partial->points);
        $this->assertSame(50, (int) $existing->points);
        $this->assertSame(11, PointsLog::count());
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
