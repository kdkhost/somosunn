<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PageController;
use App\Models\Page;
use App\Support\CmsPageCatalog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminPageControllerFallbackTest extends TestCase
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

        $this->sqlitePath = database_path('testing-admin-pages-fallback.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        $this->refreshApplication();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropAllTables();
        Page::resetTableAvailabilityCache();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
        });
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

    public function test_index_does_not_break_when_pages_table_is_missing(): void
    {
        $view = app(PageController::class)->index();
        $data = $view->getData();

        $this->assertSame('admin.pages.index', $view->name());
        $this->assertTrue(isset($data['pages']));
        $this->assertCount(0, $data['pages']);
        $this->assertFalse($data['pageTableAvailable']);
    }

    public function test_index_recreates_missing_default_pages_when_table_exists(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title', 255)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        foreach (array_slice(CmsPageCatalog::definitions(), 0, 2) as $page) {
            Page::query()->create($page);
        }

        Page::resetTableAvailabilityCache();

        $view = app(PageController::class)->index();
        $data = $view->getData();

        $this->assertTrue($data['pageTableAvailable']);
        $this->assertSame(13, Page::query()->count());
        $this->assertNotNull(Page::query()->where('slug', 'premium')->first());
        $this->assertNotNull(Page::query()->where('slug', 'eventos')->first());
        $this->assertCount(13, $data['pages']);
    }
}
