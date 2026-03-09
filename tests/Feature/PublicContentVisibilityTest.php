<?php

namespace Tests\Feature;

use App\Http\Controllers\MentorshipController;
use App\Models\Mentorship;
use App\Support\ContentVisibility;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicContentVisibilityTest extends TestCase
{
    private string $sqlitePath;
    private string $originalDefaultConnection;
    private string $originalSqliteDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        $this->originalSqliteDatabase = (string) config('database.connections.sqlite.database');
        $this->sqlitePath = database_path('testing-public-content-visibility.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('visibility')->nullable();
            $table->boolean('is_somos_unicas')->default(false);
            $table->integer('slots')->default(10);
            $table->json('schedule')->nullable();
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

    public function test_content_visibility_respects_legacy_flag_and_new_visibility_values(): void
    {
        Mentorship::create(['title' => 'Ambos', 'visibility' => 'ambos']);
        Mentorship::create(['title' => 'Somente UNN', 'visibility' => 'somos_unn']);
        Mentorship::create(['title' => 'Somente Unicas', 'visibility' => 'somos_unicas']);
        Mentorship::create(['title' => 'Legado Unicas', 'visibility' => null, 'is_somos_unicas' => true]);
        Mentorship::create(['title' => 'Legado Publico', 'visibility' => null, 'is_somos_unicas' => false]);

        $publicTitles = ContentVisibility::applyPublicFilter(Mentorship::query(), 'mentorships')
            ->orderBy('id')
            ->pluck('title')
            ->all();

        $unicasTitles = ContentVisibility::applySomosUnicasFilter(Mentorship::query(), 'mentorships')
            ->orderBy('id')
            ->pluck('title')
            ->all();

        $this->assertSame(['Ambos', 'Somente UNN', 'Legado Publico'], $publicTitles);
        $this->assertSame(['Ambos', 'Somente Unicas', 'Legado Unicas'], $unicasTitles);
    }

    public function test_public_mentorship_listing_uses_visibility_filter(): void
    {
        Mentorship::create(['title' => 'Mentoria UNN', 'visibility' => 'somos_unn']);
        Mentorship::create(['title' => 'Mentoria Ambos', 'visibility' => 'ambos']);
        Mentorship::create(['title' => 'Mentoria Unicas', 'visibility' => 'somos_unicas']);

        $view = app(MentorshipController::class)->index();
        $mentorships = $view->getData()['mentorships']->getCollection();
        $titles = $mentorships->pluck('title')->sort()->values()->all();

        $this->assertSame(['Mentoria Ambos', 'Mentoria UNN'], $titles);
        $this->assertNotContains('Mentoria Unicas', $titles);
    }
}
