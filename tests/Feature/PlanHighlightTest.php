<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlanHighlightTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-plan-highlight.sqlite');

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
            $table->string('slug')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('period')->default('mensal');
            $table->unsignedTinyInteger('billing_cycle')->default(1);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('highlight_legacy')->default(false);
            $table->boolean('highlight')->default(false);
            $table->boolean('coupons_enabled')->default(false);
            $table->json('benefits')->nullable();
            $table->json('permissions')->nullable();
            $table->json('comparison')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_free')->default(false);
            $table->string('mp_plan_id')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('price_periods')->nullable();
            $table->json('period_settings')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        config()->set('database.default', $this->originalDefaultConnection);
        config()->set('database.connections.sqlite.database', $this->originalSqliteDatabase);

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_only_one_plan_can_be_highlighted(): void
    {
        $this->withoutMiddleware();

        $this->post(route('admin.plans.store'), [
            'name' => 'Plano A',
            'slug' => '',
            'description' => '',
            'price' => 10,
            'period' => 'mensal',
            'highlight' => 1,
            'coupons_enabled' => 0,
            'benefits' => "Beneficio 1\nBeneficio 2",
            'permissions' => [],
            'is_active' => 1,
        ])->assertRedirect(route('admin.plans.index'));

        $planA = DB::table('plans')->where('name', 'Plano A')->first();
        $this->assertNotNull($planA);
        $this->assertTrue((bool) $planA->highlight);

        $this->post(route('admin.plans.store'), [
            'name' => 'Plano B',
            'slug' => '',
            'description' => '',
            'price' => 20,
            'period' => 'mensal',
            'highlight' => 1,
            'coupons_enabled' => 0,
            'benefits' => "Beneficio 1\nBeneficio 2",
            'permissions' => [],
            'is_active' => 1,
        ])->assertRedirect(route('admin.plans.index'));

        $planA = DB::table('plans')->where('name', 'Plano A')->first();
        $planB = DB::table('plans')->where('name', 'Plano B')->first();

        $this->assertNotNull($planB);
        $this->assertTrue((bool) $planB->highlight);
        $this->assertFalse((bool) $planA->highlight);
    }

    public function test_admin_plan_form_persists_enabled_periods_and_prices(): void
    {
        $this->withoutMiddleware();

        $this->post(route('admin.plans.store'), [
            'name' => 'Plano Pro Teste',
            'slug' => 'plano-pro-teste',
            'description' => 'Plano de teste',
            'price' => '97,00',
            'period' => 'trimestral',
            'highlight' => 0,
            'coupons_enabled' => 1,
            'is_active' => 1,
            'is_recurring' => 1,
            'billing_cycle' => 1,
            'benefits' => "Comunidade\nCursos\nMentorias",
            'permissions' => ['community', 'courses', 'mentorships', 'benefits.club.access'],
            'price_periods' => [
                'mensal' => '97,00',
                'trimestral' => '249,90',
                'semestral' => '479,90',
                'anual' => '899,90',
            ],
            'period_settings' => [
                'mensal' => ['enabled' => 0],
                'trimestral' => ['enabled' => 1],
                'semestral' => ['enabled' => 1],
                'anual' => ['enabled' => 1],
            ],
        ])->assertRedirect(route('admin.plans.index'));

        $plan = \App\Models\Plan::query()->where('slug', 'plano-pro-teste')->first();

        $this->assertNotNull($plan);
        $this->assertSame('trimestral', $plan->period);
        $this->assertSame(97.0, (float) $plan->price);
        $this->assertSame(
            [
                'trimestral' => 249.9,
                'semestral' => 479.9,
                'anual' => 899.9,
            ],
            $plan->getAvailablePeriods()
        );
        $this->assertFalse((bool) data_get($plan->resolvedPeriodSettings(), 'mensal.enabled'));
        $this->assertTrue((bool) data_get($plan->resolvedPeriodSettings(), 'trimestral.enabled'));
        $this->assertTrue((bool) $plan->is_recurring);
    }
}
