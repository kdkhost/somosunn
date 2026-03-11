<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Support\CmsPageCatalog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CmsPageCatalogPreserveDataTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-cms-page-catalog-preserve.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->json('data')->nullable();
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

    public function test_upsert_defaults_preserves_existing_custom_page_data(): void
    {
        Page::query()->create([
            'slug' => 'premium',
            'title' => 'Titulo Customizado',
            'data' => [
                'hero_title' => 'Hero customizado',
                'plans_title' => 'Planos customizados',
            ],
        ]);

        CmsPageCatalog::upsertDefaults();

        $page = Page::query()->where('slug', 'premium')->firstOrFail();

        $this->assertSame('Titulo Customizado', $page->title);
        $this->assertSame('Hero customizado', $page->data['hero_title'] ?? null);
        $this->assertSame('Planos customizados', $page->data['plans_title'] ?? null);
        $this->assertArrayHasKey('seo_title', $page->data);
        $this->assertArrayHasKey('plans_subtitle', $page->data);
    }

    public function test_upsert_defaults_backfills_missing_portal_editor_fields(): void
    {
        Page::query()->create([
            'slug' => 'portal',
            'title' => 'Portal Customizado',
            'data' => [
                'hero_title' => 'Portal Customizado',
            ],
        ]);

        CmsPageCatalog::upsertDefaults();

        $page = Page::query()->where('slug', 'portal')->firstOrFail();

        $this->assertSame('Portal Customizado', $page->title);
        $this->assertSame('Portal Customizado', $page->data['hero_title'] ?? null);
        $this->assertSame('120+', $page->data['stat_1_value'] ?? null);
        $this->assertSame('Palestras', $page->data['stat_1_label'] ?? null);
        $this->assertSame('Niveis da Comunidade', $page->data['community_title'] ?? null);
        $this->assertSame('Iniciante', $page->data['community_level_1_name'] ?? null);
        $this->assertSame('1.200', $page->data['community_level_1_count'] ?? null);
        $this->assertSame('Top Networkers', $page->data['ranking_title'] ?? null);
        $this->assertSame('Ranking baseado em conexoes', $page->data['ranking_subtitle'] ?? null);
        $this->assertSame('Desbloqueie todos os recursos', $page->data['cta_title'] ?? null);
        $this->assertSame('Conhecer planos Premium', $page->data['cta_btn'] ?? null);
    }
}
