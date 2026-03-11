<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PageController;
use App\Models\Page;
use App\Support\CmsPageCatalog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;

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
        clearstatcache(true, $this->sqlitePath);

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

        $expectedCount = count(CmsPageCatalog::definitions());

        $this->assertTrue($data['pageTableAvailable']);
        $this->assertSame($expectedCount, Page::query()->count());
        $this->assertNotNull(Page::query()->where('slug', 'premium')->first());
        $this->assertNotNull(Page::query()->where('slug', 'eventos')->first());
        $this->assertCount($expectedCount, $data['pages']);
    }

    public function test_premium_editor_uses_top_level_field_names_expected_by_controller(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title', 255)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        $page = Page::query()->create([
            'slug' => 'premium',
            'title' => 'Planos Premium',
            'data' => [
                'hero_image' => 'pages/premium/hero-atual.jpg',
            ],
        ]);

        $html = view('admin.pages.partials.premium', [
            'data' => $page->data ?? [],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('name="hero_badge"', $html);
        $this->assertStringContainsString('name="hero_title"', $html);
        $this->assertStringContainsString('name="hero_subtitle"', $html);
        $this->assertStringContainsString('name="hero_trust_1"', $html);
        $this->assertStringContainsString('name="hero_trust_2"', $html);
        $this->assertStringContainsString('name="hero_image"', $html);
        $this->assertStringContainsString('name="remove_hero_image"', $html);
        $this->assertStringContainsString('name="plans_title"', $html);
        $this->assertStringContainsString('name="plans_subtitle"', $html);

        $this->assertStringNotContainsString('name="data[hero_title]"', $html);
        $this->assertStringNotContainsString("name='data[hero_title]'", $html);
        $this->assertStringNotContainsString('name="images[hero_image]"', $html);
        $this->assertStringNotContainsString('name="remove_image[hero_image]"', $html);
    }

    public function test_somos_unicas_about_networking_image_is_owned_only_by_about_editor(): void
    {
        $homeHtml = view('admin.pages.partials.somos-unicas', [
            'data' => [],
            'errors' => new ViewErrorBag(),
        ])->render();

        $aboutHtml = view('admin.pages.partials.somos-unicas-sobre', [
            'data' => [
                'networking_image' => 'pages/somos-unicas-sobre/networking.jpg',
            ],
            'errors' => new ViewErrorBag(),
        ])->render();

        $controllerConstants = (new \ReflectionClass(PageController::class))->getConstant('SLUG_IMAGE_FIELDS');

        $this->assertStringNotContainsString('name="networking_image"', $homeHtml);
        $this->assertStringContainsString('/somos-unicas/sobre', $homeHtml);
        $this->assertStringContainsString('name="networking_image"', $aboutHtml);
        $this->assertSame(['hero_image'], $controllerConstants['somos-unicas']);
        $this->assertSame(['hero_image', 'networking_image'], $controllerConstants['somos-unicas-sobre']);
    }

    public function test_somos_unicas_editor_renders_expected_sections_and_fields(): void
    {
        $html = view('admin.pages.partials.somos-unicas', [
            'data' => [
                'hero_image' => 'pages/somos-unicas/hero.jpg',
            ],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('id="sec-identity"', $html);
        $this->assertStringContainsString('id="sec-hero"', $html);
        $this->assertStringContainsString('id="sec-headers"', $html);
        $this->assertStringContainsString('id="sec-empty"', $html);
        $this->assertStringContainsString('name="theme_color"', $html);
        $this->assertStringContainsString('name="hero_title"', $html);
        $this->assertStringContainsString('name="hero_subtitle"', $html);
        $this->assertStringContainsString('name="hero_image"', $html);
        $this->assertStringContainsString('name="remove_hero_image"', $html);
        $this->assertStringContainsString('name="courses_title"', $html);
        $this->assertStringContainsString('name="events_title"', $html);
        $this->assertStringContainsString('name="mentorships_title"', $html);
        $this->assertStringContainsString('name="empty_title"', $html);
        $this->assertStringContainsString('name="empty_description"', $html);
        $this->assertStringNotContainsString('value="Em breve!">', $html);
    }

    public function test_somos_unicas_about_editor_renders_expected_fields(): void
    {
        $html = view('admin.pages.partials.somos-unicas-sobre', [
            'data' => [
                'hero_image' => 'pages/somos-unicas-sobre/hero.jpg',
                'networking_image' => 'pages/somos-unicas-sobre/networking.jpg',
            ],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('name="theme_color"', $html);
        $this->assertStringContainsString('name="networking_image"', $html);
        $this->assertStringContainsString('name="remove_networking_image"', $html);
        $this->assertStringContainsString('name="hero_title"', $html);
        $this->assertStringContainsString('name="hero_subtitle"', $html);
        $this->assertStringContainsString('name="hero_image"', $html);
        $this->assertStringContainsString('name="remove_hero_image"', $html);
        $this->assertStringContainsString('name="content_title"', $html);
        $this->assertStringContainsString('name="content_body"', $html);
        $this->assertStringContainsString('data-upload-global-instance', $html);
        $this->assertStringContainsString('data-upload-path-input', $html);
        $this->assertStringContainsString('data-upload-remove-input', $html);
    }

    public function test_portal_editor_uses_top_level_field_names_expected_by_controller(): void
    {
        $html = view('admin.pages.partials.portal', [
            'data' => [
                'hero_image' => 'pages/portal/hero.jpg',
            ],
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('name="hero_title"', $html);
        $this->assertStringContainsString('name="hero_subtitle"', $html);
        $this->assertStringContainsString('name="hero_image"', $html);
        $this->assertStringContainsString('name="remove_hero_image"', $html);
        $this->assertStringContainsString('name="stat_1_value"', $html);
        $this->assertStringContainsString('name="stat_4_label"', $html);
        $this->assertStringContainsString('name="cta_title"', $html);
        $this->assertStringContainsString('name="cta_subtitle"', $html);
        $this->assertStringContainsString('name="cta_btn"', $html);

        $this->assertStringNotContainsString('name="data[hero_title]"', $html);
        $this->assertStringNotContainsString('name="data[cta_title]"', $html);
        $this->assertStringNotContainsString('name="images[hero_image]"', $html);
        $this->assertStringNotContainsString('name="remove_image[hero_image]"', $html);
    }

    public function test_update_persists_premium_scalar_fields(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title', 255)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        $page = Page::query()->create([
            'slug' => 'premium',
            'title' => 'Planos Premium',
            'data' => [],
        ]);

        $request = Request::create('/admin/pages/' . $page->id, 'PUT', [
            'title' => 'Planos Premium Atualizados',
            'hero_badge' => 'Associacao Premium',
            'hero_title' => 'Novo titulo',
            'hero_subtitle' => 'Novo subtitulo',
            'hero_trust_1' => 'Sem fidelidade',
            'hero_trust_2' => 'Cancele quando quiser',
            'plans_title' => 'Escolha seu plano',
            'plans_subtitle' => 'Invista no seu crescimento',
            'seo_title' => 'SEO premium',
            'seo_description' => 'Descricao premium',
        ]);

        $request->setLaravelSession(app('session.store'));

        $response = app(PageController::class)->update($request, $page);

        $page->refresh();

        $this->assertSame('Planos Premium Atualizados', $page->title);
        $this->assertSame('Associacao Premium', $page->data['hero_badge'] ?? null);
        $this->assertSame('Novo titulo', $page->data['hero_title'] ?? null);
        $this->assertSame('Novo subtitulo', $page->data['hero_subtitle'] ?? null);
        $this->assertSame('Sem fidelidade', $page->data['hero_trust_1'] ?? null);
        $this->assertSame('Cancele quando quiser', $page->data['hero_trust_2'] ?? null);
        $this->assertSame('Escolha seu plano', $page->data['plans_title'] ?? null);
        $this->assertSame('Invista no seu crescimento', $page->data['plans_subtitle'] ?? null);
        $this->assertSame('SEO premium', $page->data['seo_title'] ?? null);
        $this->assertSame('Descricao premium', $page->data['seo_description'] ?? null);
        $this->assertSame(302, $response->getStatusCode());
    }

    public function test_update_persists_somos_unicas_about_uploaded_images(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title', 255)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        $fakeDiskRoot = storage_path('framework/testing/admin-pages-upload-' . uniqid());
        if (!is_dir($fakeDiskRoot)) {
            mkdir($fakeDiskRoot, 0777, true);
        }
        config()->set('filesystems.disks.public.root', $fakeDiskRoot);

        $page = Page::query()->create([
            'slug' => 'somos-unicas-sobre',
            'title' => 'Somos Unicas Sobre',
            'data' => [],
        ]);

        $heroImage = UploadedFile::fake()->image('hero.jpg', 1200, 630);
        $networkingImage = UploadedFile::fake()->image('networking.jpg', 1200, 630);

        $request = Request::create('/admin/pages/' . $page->id, 'PUT', [
            'title' => 'Somos Unicas Sobre',
            'theme_color' => '#6d28d9',
            'hero_title' => 'Sobre a Somos Unicas',
            'hero_subtitle' => 'Uma introducao institucional.',
            'content_title' => 'Nossa jornada',
            'content_body' => '<p>Conteudo completo.</p>',
        ], [], [
            'hero_image' => $heroImage,
            'networking_image' => $networkingImage,
        ]);

        $request->setLaravelSession(app('session.store'));

        $response = app(PageController::class)->update($request, $page);

        $page->refresh();

        $this->assertSame('#6d28d9', $page->data['theme_color'] ?? null);
        $this->assertSame('Sobre a Somos Unicas', $page->data['hero_title'] ?? null);
        $this->assertSame('Uma introducao institucional.', $page->data['hero_subtitle'] ?? null);
        $this->assertSame('Nossa jornada', $page->data['content_title'] ?? null);
        $this->assertSame('<p>Conteudo completo.</p>', $page->data['content_body'] ?? null);
        $this->assertNotEmpty($page->data['hero_image'] ?? null);
        $this->assertNotEmpty($page->data['networking_image'] ?? null);
        $this->assertFileExists($fakeDiskRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $page->data['hero_image']));
        $this->assertFileExists($fakeDiskRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $page->data['networking_image']));
        $this->assertSame(302, $response->getStatusCode());
    }

    public function test_update_accepts_chunked_uploaded_image_paths_for_pages(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title', 255)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        $fakeDiskRoot = storage_path('framework/testing/admin-pages-chunked-' . uniqid());
        if (!is_dir($fakeDiskRoot)) {
            mkdir($fakeDiskRoot, 0777, true);
        }
        config()->set('filesystems.disks.public.root', $fakeDiskRoot);

        $page = Page::query()->create([
            'slug' => 'somos-unicas-sobre',
            'title' => 'Somos Unicas Sobre',
            'data' => [
                'hero_image' => 'pages/somos-unicas-sobre/old-hero.jpg',
            ],
        ]);

        $networkingPath = $fakeDiskRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'networking-final.png';
        $heroPath = $fakeDiskRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'hero-final.png';
        if (!is_dir(dirname($networkingPath))) {
            mkdir(dirname($networkingPath), 0777, true);
        }

        file_put_contents($networkingPath, str_repeat('n', 1024));
        file_put_contents($heroPath, str_repeat('h', 2048));

        $request = Request::create('/admin/pages/' . $page->id, 'PUT', [
            'title' => 'Somos Unicas Sobre',
            'theme_color' => '#6d28d9',
            'hero_title' => 'Sobre a Somos Unicas',
            'hero_subtitle' => 'Uma introducao institucional.',
            'content_title' => 'Nossa jornada',
            'content_body' => '<p>Conteudo completo.</p>',
            'hero_image' => 'uploads/hero-final.png',
            'networking_image' => 'uploads/networking-final.png',
        ]);

        $request->setLaravelSession(app('session.store'));

        $response = app(PageController::class)->update($request, $page);

        $page->refresh();

        $this->assertSame('uploads/hero-final.png', $page->data['hero_image'] ?? null);
        $this->assertSame('uploads/networking-final.png', $page->data['networking_image'] ?? null);
        $this->assertSame(302, $response->getStatusCode());
    }
}
