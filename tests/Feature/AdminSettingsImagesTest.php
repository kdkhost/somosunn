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
    /** @var array<int, string> */
    private array $tempFiles = [];

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

        foreach ($this->tempFiles as $tempFile) {
            if (is_file($tempFile)) {
                @unlink($tempFile);
            }
        }

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

    public function test_admin_settings_rejects_opaque_png_watermark(): void
    {
        $this->withoutMiddleware();

        $file = $this->makeWatermarkUpload(false);

        $this->post(route('admin.settings.update'), [
            'current_group' => 'player',
            'watermark_image' => $file,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertNull(Setting::where('key', 'watermark_image')->value('value'));
    }

    public function test_admin_settings_accepts_transparent_png_watermark(): void
    {
        $this->withoutMiddleware();

        $file = $this->makeWatermarkUpload(true);

        $this->post(route('admin.settings.update'), [
            'current_group' => 'player',
            'watermark_image' => $file,
        ])->assertRedirect();

        $savedPath = (string) Setting::where('key', 'watermark_image')->value('value');

        $this->assertNotSame('', $savedPath);
        $this->assertFileExists(public_path($savedPath));
    }

    public function test_admin_settings_ads_accepts_encoded_html_payloads(): void
    {
        $this->withoutMiddleware();

        $globalCode = <<<'HTML'
<script async src="https://example.com/ad.js"></script>
<div class="ad-slot">Banner</div>
HTML;

        $feedCode = <<<'HTML'
<script>console.log("feed-ad");</script>
HTML;

        $response = $this->post(route('admin.settings.update'), [
            'current_group' => 'ads',
            'ads_enabled' => '1',
            'ads_code_html_encoded' => rtrim(strtr(base64_encode($globalCode), '+/', '-_'), '='),
            'ads_inter_feed_code_encoded' => rtrim(strtr(base64_encode($feedCode), '+/', '-_'), '='),
        ]);

        $response->assertRedirect();

        $this->assertSame($globalCode, (string) Setting::where('key', 'ads_code_html')->value('value'));
        $this->assertSame($feedCode, (string) Setting::where('key', 'ads_inter_feed_code')->value('value'));
        $this->assertSame('1', (string) Setting::where('key', 'ads_enabled')->value('value'));
    }

    private function makeWatermarkUpload(bool $transparent): UploadedFile
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wm_' . bin2hex(random_bytes(6)) . '.png';
        $this->tempFiles[] = $path;

        $image = imagecreatetruecolor(180, 90);

        if ($transparent) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $background = imagecolorallocatealpha($image, 0, 0, 0, 127);
            imagefilledrectangle($image, 0, 0, 179, 89, $background);
            $accent = imagecolorallocatealpha($image, 21, 101, 255, 0);
        } else {
            $background = imagecolorallocate($image, 12, 18, 40);
            imagefilledrectangle($image, 0, 0, 179, 89, $background);
            $accent = imagecolorallocate($image, 21, 101, 255);
        }

        imagefilledellipse($image, 90, 45, 120, 48, $accent);
        imagepng($image, $path);
        imagedestroy($image);

        return new UploadedFile($path, 'watermark.png', 'image/png', null, true);
    }
}
