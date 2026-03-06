<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FooterSiteNameTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-footer-site-name.sqlite');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        parent::tearDown();
    }

    public function test_footer_uses_configured_site_name_when_footer_text_is_empty(): void
    {
        DB::table('settings')->insert([
            ['key' => 'app_name', 'value' => 'Somos Beta', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_text', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
        ]);

        \App\Models\Setting::flushRuntimeCache();

        $html = view('partials.footer')->render();

        $this->assertStringContainsString('© ' . date('Y') . ' Somos Beta.', $html);
        $this->assertStringNotContainsString('© ' . date('Y') . ' UNN.', $html);
    }

    public function test_footer_replaces_legacy_unn_text_with_configured_site_name(): void
    {
        DB::table('settings')->insert([
            ['key' => 'app_name', 'value' => 'Somos Beta', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer_text', 'value' => '© ' . date('Y') . ' UNN.', 'created_at' => now(), 'updated_at' => now()],
        ]);

        \App\Models\Setting::flushRuntimeCache();

        $html = view('partials.footer')->render();

        $this->assertStringContainsString('© ' . date('Y') . ' Somos Beta.', $html);
        $this->assertStringNotContainsString('© ' . date('Y') . ' UNN.', $html);
    }
}
