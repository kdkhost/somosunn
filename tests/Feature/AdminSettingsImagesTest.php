<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminSettingsImagesTest extends TestCase
{
    private string $sqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-admin-settings.sqlite');

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

    public function test_admin_settings_images_can_upload_and_remove_logo(): void
    {
        $this->withoutMiddleware();

        $file = UploadedFile::fake()->create('logo.png', 128, 'image/png');

        $this->post(route('admin.settings.update'), [
            'current_group' => 'images',
            'logo_image' => $file,
        ])->assertRedirect();

        $savedPath = (string) Setting::where('key', 'logo_image')->value('value');

        $this->assertNotSame('', $savedPath);
        $this->assertFileExists(public_path($savedPath));

        $this->post(route('admin.settings.update'), [
            'current_group' => 'images',
            'remove_logo_image' => '1',
        ])->assertRedirect();

        $this->assertSame('', (string) Setting::where('key', 'logo_image')->value('value'));
        $this->assertFileDoesNotExist(public_path($savedPath));
    }

    public function test_admin_settings_player_persists_image_watermark_controls(): void
    {
        $this->withoutMiddleware();

        $response = $this->post(route('admin.settings.update'), [
            'current_group' => 'player',
            'image_watermark_enabled' => '1',
            'image_watermark_position' => 'center',
            'image_watermark_opacity' => '26',
            'image_watermark_size_percent' => '11',
            'image_watermark_margin' => '18',
        ]);

        $response->assertRedirect();

        $this->assertSame('1', (string) Setting::where('key', 'image_watermark_enabled')->value('value'));
        $this->assertSame('center', (string) Setting::where('key', 'image_watermark_position')->value('value'));
        $this->assertSame('26', (string) Setting::where('key', 'image_watermark_opacity')->value('value'));
        $this->assertSame('11', (string) Setting::where('key', 'image_watermark_size_percent')->value('value'));
        $this->assertSame('18', (string) Setting::where('key', 'image_watermark_margin')->value('value'));
    }

    public function test_admin_settings_rejects_non_transparent_watermark_format(): void
    {
        $this->withoutMiddleware();

        $file = UploadedFile::fake()->create('watermark.jpg', 64, 'image/jpeg');

        $this->post(route('admin.settings.update'), [
            'current_group' => 'player',
            'watermark_image' => $file,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertNull(Setting::where('key', 'watermark_image')->value('value'));
    }
}
