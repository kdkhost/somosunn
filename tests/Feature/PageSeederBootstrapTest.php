<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PageSeederBootstrapTest extends TestCase
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

        $this->sqlitePath = database_path('testing-page-seeder-bootstrap.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        $this->refreshApplication();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Page::resetTableAvailabilityCache();
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        DB::purge('sqlite');
        gc_collect_cycles();

        parent::tearDown();

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }
    }

    public function test_page_seeder_creates_table_when_it_is_missing(): void
    {
        $this->assertFalse(Schema::hasTable('pages'));

        app(PageSeeder::class)->run();

        $this->assertTrue(Schema::hasTable('pages'));
        $this->assertNotNull(Page::query()->where('slug', 'home')->first());
        $this->assertNotNull(Page::query()->where('slug', 'sobre')->first());

        $migration = require database_path('migrations/2026_03_02_000003_create_pages_table.php');
        $migration->up();

        $this->assertTrue(Schema::hasTable('pages'));
        $this->assertSame(6, Page::query()->count());
    }
}
