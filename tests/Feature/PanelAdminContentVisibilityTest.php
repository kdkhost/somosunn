<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserHasActivePlan;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PanelAdminContentVisibilityTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-panel-admin-content-visibility.sqlite');

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
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('flash_sale_price', 10, 2)->nullable();
            $table->dateTime('flash_sale_ends_at')->nullable();
            $table->string('author_name')->nullable();
            $table->string('status')->default('draft');
            $table->string('thumbnail')->nullable();
            $table->boolean('published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_certificate_enabled')->default(false);
            $table->boolean('video_block_download')->default(false);
            $table->boolean('video_floating_enabled')->default(false);
            $table->integer('video_floating_width')->nullable();
            $table->integer('video_floating_height')->nullable();
            $table->boolean('is_somos_unicas')->default(false);
            $table->string('visibility')->default('ambos');
            $table->json('certificate_settings')->nullable();
            $table->string('certificate_bg')->nullable();
            $table->string('instructor_signature')->nullable();
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
            $table->boolean('is_somos_unicas')->default(false);
            $table->string('visibility')->default('ambos');
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
            $table->integer('slots')->nullable();
            $table->json('schedule')->nullable();
            $table->string('type')->default('online');
            $table->string('video_platform')->nullable();
            $table->string('video_link')->nullable();
            $table->string('demo_link')->nullable();
            $table->boolean('is_certificate_enabled')->default(false);
            $table->string('certificate_bg')->nullable();
            $table->string('instructor_signature')->nullable();
            $table->json('certificate_settings')->nullable();
            $table->boolean('is_somos_unicas')->default(false);
            $table->string('visibility')->default('ambos');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        config()->set('database.default', $this->originalDefaultConnection);
        config()->set('database.connections.sqlite.database', $this->originalSqliteDatabase);

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_panel_admin_courses_update_persists_visibility_selection(): void
    {
        $user = $this->createAdminUser('curso-admin@example.com');

        $course = Course::create([
            'user_id' => $user->id,
            'created_by' => $user->id,
            'title' => 'Curso Base',
            'status' => 'draft',
            'thumbnail' => 'uploads/course-thumbs/existente.png',
            'visibility' => 'ambos',
            'is_somos_unicas' => false,
        ]);

        $response = $this->withoutMiddleware([
                EnsureUserHasActivePlan::class,
                EnsureUserIsAdmin::class,
                VerifyCsrfToken::class,
            ])
            ->actingAs($user)
            ->put(route('panel.admin.courses.update', $course), [
                'title' => 'Curso Atualizado',
                'status' => 'published',
                'price' => '249,90',
                'visibility' => 'somos_unicas',
            ]);

        $response->assertRedirect(route('panel.admin.courses.index'));

        $course = $course->fresh();

        $this->assertSame('somos_unicas', $course->visibility);
        $this->assertTrue((bool) $course->is_somos_unicas);
        $this->assertTrue((bool) $course->published);
    }

    public function test_panel_admin_events_update_persists_visibility_selection(): void
    {
        $user = $this->createAdminUser('evento-admin@example.com');

        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Evento Base',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'image' => 'event-images/existente.png',
            'visibility' => 'ambos',
            'is_somos_unicas' => false,
        ]);

        $response = $this->withoutMiddleware([
                EnsureUserHasActivePlan::class,
                EnsureUserIsAdmin::class,
                VerifyCsrfToken::class,
            ])
            ->actingAs($user)
            ->put(route('panel.admin.events.update', $event), [
                'title' => 'Evento Atualizado',
                'start_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'end_at' => now()->addDays(2)->addHour()->format('Y-m-d\TH:i'),
                'price' => '150,00',
                'visibility' => 'somos_unicas',
                'published' => '1',
            ]);

        $response->assertRedirect(route('panel.admin.events.index'));

        $event = $event->fresh();

        $this->assertSame('somos_unicas', $event->visibility);
        $this->assertTrue((bool) $event->is_somos_unicas);
        $this->assertTrue((bool) $event->published);
    }

    public function test_panel_admin_mentorships_update_persists_visibility_selection(): void
    {
        $user = $this->createAdminUser('mentoria-admin@example.com');
        $flashSaleEndsAt = now()->addDays(3)->setSecond(0);

        $mentorship = Mentorship::create([
            'mentor_id' => $user->id,
            'title' => 'Mentoria Base',
            'image' => 'uploads/mentorship-images/existente.png',
            'type' => 'online',
            'visibility' => 'ambos',
            'is_somos_unicas' => false,
        ]);

        $response = $this->withoutMiddleware([
                EnsureUserHasActivePlan::class,
                EnsureUserIsAdmin::class,
                VerifyCsrfToken::class,
            ])
            ->actingAs($user)
            ->put(route('panel.admin.mentorships.update', $mentorship), [
                'title' => 'Mentoria Atualizada',
                'mentor_id' => $user->id,
                'type' => 'online',
                'price' => '1.999,90',
                'flash_sale_price' => '999,50',
                'flash_sale_ends_at' => $flashSaleEndsAt->format('Y-m-d\TH:i'),
                'visibility' => 'somos_unn',
            ]);

        $response->assertRedirect(route('panel.admin.mentorships.index'));

        $mentorship = $mentorship->fresh();

        $this->assertSame('somos_unn', $mentorship->visibility);
        $this->assertFalse((bool) $mentorship->is_somos_unicas);
        $this->assertSame('1999.90', $mentorship->price);
        $this->assertSame('999.50', $mentorship->flash_sale_price);
        $this->assertSame($flashSaleEndsAt->format('Y-m-d H:i:s'), $mentorship->flash_sale_ends_at?->format('Y-m-d H:i:s'));
    }

    private function createAdminUser(string $email): User
    {
        return User::create([
            'name' => 'Admin do Painel',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }
}
