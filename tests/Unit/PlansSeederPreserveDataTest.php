<?php

namespace Tests\Unit;

use Database\Seeders\PlansSeederUtf8;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlansSeederPreserveDataTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-plans-seeder-preserve.sqlite');

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
            $table->string('name')->nullable();
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('period')->nullable();
            $table->integer('billing_cycle')->nullable();
            $table->boolean('prorata')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_featured')->nullable();
            $table->boolean('highlight_legacy')->nullable();
            $table->boolean('highlight')->nullable();
            $table->boolean('coupons_enabled')->nullable();
            $table->json('benefits')->nullable();
            $table->json('permissions')->nullable();
            $table->json('comparison')->nullable();
            $table->boolean('is_active')->nullable();
            $table->boolean('is_free')->nullable();
            $table->string('mp_plan_id')->nullable();
            $table->boolean('is_recurring')->nullable();
            $table->integer('sort_order')->nullable();
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

    public function test_plan_seeder_preserves_existing_custom_content(): void
    {
        $createdAt = now()->subDays(10);

        DB::table('plans')->insert([
            'name' => 'Pro Custom',
            'slug' => 'pro',
            'price' => 149.90,
            'description' => 'Descricao customizada',
            'period' => 'anual',
            'billing_cycle' => 12,
            'prorata' => false,
            'is_active' => true,
            'is_free' => false,
            'is_recurring' => true,
            'sort_order' => 8,
            'highlight' => false,
            'is_featured' => false,
            'benefits' => json_encode(['Beneficio customizado']),
            'permissions' => json_encode(['community', 'events']),
            'price_periods' => json_encode(['anual' => 149.90]),
            'period_settings' => json_encode(['anual' => ['enabled' => true]]),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        app(PlansSeederUtf8::class)->run();

        $plan = DB::table('plans')->where('slug', 'pro')->first();

        $this->assertSame('Pro Custom', $plan->name);
        $this->assertSame('Descricao customizada', $plan->description);
        $this->assertSame('anual', $plan->period);
        $this->assertSame($createdAt->format('Y-m-d H:i:s'), (string) $plan->created_at);
        $this->assertSame(['Beneficio customizado'], json_decode((string) $plan->benefits, true));
        $this->assertSame(['community', 'events'], json_decode((string) $plan->permissions, true));
        $this->assertTrue(DB::table('plans')->where('slug', 'elite')->exists());
    }
}
