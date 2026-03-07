<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PanelDashboardMetricsTest extends TestCase
{
    private string $sqlitePath;

    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-panel-dashboard-metrics.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        $this->refreshApplication();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);
        config()->set('cache.default', 'file');
        config()->set('dashboard.cache_store', 'file');
        config()->set('dashboard.cache_ttl_seconds', 120);

        $this->setUpSchema();
        Artisan::call('cache:clear');
    }

    protected function tearDown(): void
    {
        Artisan::call('cache:clear');
        DB::disconnect('sqlite');
        DB::purge('sqlite');
        gc_collect_cycles();
        parent::tearDown();

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }
    }

    public function test_member_dashboard_respects_feature_visibility(): void
    {
        $planId = $this->createPlan(['courses_access']);
        $user = $this->createUser([
            'plan_id' => $planId,
            'plan_expires_at' => now()->addMonth(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('panel.dashboard'));

        $response->assertOk();
        $response->assertSee('Meus Cursos');
        $response->assertDontSee('Interações');
    }

    public function test_dashboard_stats_are_cached_and_warmed_again_by_command(): void
    {
        $planId = $this->createPlan(['courses_access', 'community']);
        $user = $this->createUser([
            'plan_id' => $planId,
            'plan_expires_at' => now()->addMonth(),
        ]);

        $this->insertCourse($user->id, 'Curso 1');
        $this->insertOrder($user->id, $user->id, 'paid', 100, 10);

        $this->actingAs($user);

        $first = $this->getJson(route('panel.dashboard.stats'))->assertOk()->json();
        $this->assertSame(1, $first['stats']['courses_count']);
        $this->assertSame(1, $first['stats']['orders_paid_count']);

        $this->insertCourse($user->id, 'Curso 2');
        $this->insertOrder($user->id, $user->id, 'paid', 200, 20);

        $cached = $this->getJson(route('panel.dashboard.stats'))->assertOk()->json();
        $this->assertSame(1, $cached['stats']['courses_count']);
        $this->assertSame(1, $cached['stats']['orders_paid_count']);

        Artisan::call('dashboard:warm-cache', ['--fresh' => true, '--user' => $user->id]);

        $refreshed = $this->getJson(route('panel.dashboard.stats'))->assertOk()->json();
        $this->assertSame(2, $refreshed['stats']['courses_count']);
        $this->assertSame(2, $refreshed['stats']['orders_paid_count']);
    }

    public function test_admin_stats_return_global_metrics(): void
    {
        $admin = $this->createUser([
            'role' => 'admin',
            'level' => 'admin',
        ]);

        $planId = $this->createPlan(['courses_access', 'community']);
        $memberA = $this->createUser(['plan_id' => $planId, 'plan_expires_at' => now()->addMonth()]);
        $memberB = $this->createUser(['plan_id' => $planId, 'plan_expires_at' => now()->addMonth()]);

        $this->insertCourse($memberA->id, 'Curso A');
        $this->insertCourse($memberB->id, 'Curso B');
        $this->insertOrder($memberA->id, $memberA->id, 'paid', 100, 10);
        $this->insertOrder($memberB->id, $memberB->id, 'paid', 300, 30);

        $this->actingAs($admin);

        $payload = $this->getJson(route('panel.dashboard.stats'))->assertOk()->json();

        $this->assertSame(2, $payload['stats']['courses_count']);
        $this->assertSame(2, $payload['stats']['orders_paid_count']);
        $this->assertSame(400.0, (float) $payload['stats']['orders_paid_total']);
        $this->assertCount(6, $payload['sales_chart']['labels']);
        $this->assertCount(6, $payload['sales_chart']['data']);
    }

    public function test_owner_dashboard_returns_segmented_service_visit_metrics(): void
    {
        $planId = $this->createPlan(['courses_access']);
        $user = $this->createUser([
            'plan_id' => $planId,
            'plan_expires_at' => now()->addMonth(),
        ]);
        $otherUser = $this->createUser();

        $courseId = $this->insertCourse($user->id, 'Curso Radar');
        $eventId = $this->insertEvent($user->id, 'Evento Radar');
        $mentorshipId = $this->insertMentorship($user->id, 'Mentoria Radar');
        $foreignCourseId = $this->insertCourse($otherUser->id, 'Curso Externo');

        $this->insertServiceVisit('curso', $courseId, 2);
        $this->insertServiceVisit('evento', $eventId, 1);
        $this->insertServiceVisit('mentoria', $mentorshipId, 1);
        $this->insertServiceVisit('curso', $foreignCourseId, 5);

        $this->actingAs($user);

        $payload = $this->getJson(route('panel.dashboard.stats'))->assertOk()->json();

        $this->assertTrue($payload['visit_metrics']['enabled']);
        $this->assertSame(3, $payload['visit_metrics']['owned_products_count']);
        $this->assertSame(4, $payload['visit_metrics']['total_visits']);
        $this->assertSame(2, $payload['visit_metrics']['by_type']['curso']);
        $this->assertSame(1, $payload['visit_metrics']['by_type']['evento']);
        $this->assertSame(1, $payload['visit_metrics']['by_type']['mentoria']);
        $this->assertSame('Curso Radar', $payload['visit_metrics']['top_items'][0]['label']);
    }

    public function test_admin_dashboard_stats_route_returns_global_service_visit_metrics(): void
    {
        $admin = $this->createUser([
            'role' => 'admin',
            'level' => 'admin',
        ]);
        $owner = $this->createUser();

        $courseId = $this->insertCourse($owner->id, 'Curso Global');
        $eventId = $this->insertEvent($owner->id, 'Evento Global');

        $this->insertServiceVisit('site', null, 3);
        $this->insertServiceVisit('curso', $courseId, 4);
        $this->insertServiceVisit('evento', $eventId, 2);

        $this->actingAs($admin);

        $panelPayload = $this->getJson(route('panel.admin.dashboard.stats'))->assertOk()->json();
        $legacyPayload = $this->getJson(route('admin.dashboard.stats'))->assertOk()->json();

        $this->assertTrue($panelPayload['serviceVisitsEnabled']);
        $this->assertSame(9, $panelPayload['serviceVisitSummary']['total']);
        $this->assertSame(3, $panelPayload['serviceVisitSummary']['site']);
        $this->assertSame(4, $panelPayload['serviceVisitSummary']['curso']);
        $this->assertNotEmpty($panelPayload['serviceVisitOwnerLeaders']);
        $this->assertSame($panelPayload['serviceVisitSummary']['total'], $legacyPayload['serviceVisitSummary']['total']);
    }

    private function setUpSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->default('member');
            $table->string('level')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->text('extra_features')->nullable();
            $table->string('phone')->nullable();
            $table->string('occupation')->nullable();
            $table->text('bio')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('photo')->nullable();
            $table->string('company')->nullable();
            $table->string('interests')->nullable();
            $table->integer('points')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id');
            $table->string('status')->default('active');
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('published')->default(true);
            $table->boolean('all_day')->default(false);
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentor_id')->nullable();
            $table->string('title');
            $table->json('schedule')->nullable();
            $table->timestamps();
        });

        Schema::create('service_visits', function (Blueprint $table) {
            $table->id();
            $table->string('service_type', 32);
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('visited_at')->useCurrent();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('platform_fee_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    private function createPlan(array $permissions): int
    {
        return (int) DB::table('plans')->insertGetId([
            'name' => 'Plano Teste',
            'permissions' => json_encode($permissions),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(array $overrides = []): User
    {
        $id = DB::table('users')->insertGetId(array_merge([
            'name' => 'Usuário Teste',
            'email' => uniqid('user_', true) . '@example.com',
            'password' => bcrypt('secret'),
            'role' => 'member',
            'level' => null,
            'plan_id' => null,
            'plan_expires_at' => null,
            'extra_features' => json_encode([]),
            'points' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return User::query()->findOrFail($id);
    }

    private function insertCourse(int $userId, string $title): int
    {
        return (int) DB::table('courses')->insertGetId([
            'user_id' => $userId,
            'title' => $title,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertEvent(int $userId, string $title): int
    {
        return (int) DB::table('events')->insertGetId([
            'user_id' => $userId,
            'title' => $title,
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
            'published' => 1,
            'all_day' => 0,
            'color' => '#1F5EDB',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertMentorship(int $userId, string $title): int
    {
        return (int) DB::table('mentorships')->insertGetId([
            'mentor_id' => $userId,
            'title' => $title,
            'schedule' => json_encode([
                ['start_at' => now()->addDay()->toDateTimeString()],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertServiceVisit(string $type, ?int $serviceId, int $times): void
    {
        foreach (range(1, $times) as $index) {
            DB::table('service_visits')->insert([
                'service_type' => $type,
                'service_id' => $serviceId,
                'user_id' => null,
                'visited_at' => now()->subMinutes($index),
            ]);
        }
    }

    private function insertOrder(int $userId, int $sellerId, string $status, float $total, float $fee): void
    {
        DB::table('orders')->insert([
            'user_id' => $userId,
            'seller_id' => $sellerId,
            'status' => $status,
            'total_amount' => $total,
            'platform_fee_amount' => $fee,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
