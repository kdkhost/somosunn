<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\EventMediaController;
use App\Models\Event;
use App\Models\EventMedia;
use App\Models\User;
use App\Services\WatermarkService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventMediaUploadTest extends TestCase
{
    private string $sqlitePath;
    private string $publicRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-event-media-upload.sqlite');
        $this->publicRoot = storage_path('app/testing-event-media-public');

        if (file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        $this->deleteDirectory($this->publicRoot);
        @mkdir($this->publicRoot, 0775, true);

        touch($this->sqlitePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->sqlitePath);
        config()->set('filesystems.disks.public.root', $this->publicRoot);
        config()->set('filesystems.disks.public.url', '/storage');
        config()->set('uploads.effective_disk', 'public');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });

        Schema::create('event_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('file_path');
            $table->string('type');
            $table->boolean('watermarked')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (isset($this->sqlitePath) && file_exists($this->sqlitePath)) {
            @unlink($this->sqlitePath);
        }

        if (isset($this->publicRoot)) {
            $this->deleteDirectory($this->publicRoot);
        }

        parent::tearDown();
    }

    public function test_admin_event_media_store_accepts_image_and_video_in_batch(): void
    {
        $admin = User::create([
            'name' => 'Admin Upload',
            'email' => 'admin-upload@example.com',
            'role' => 'admin',
        ]);

        $eventId = DB::table('events')->insertGetId([
            'user_id' => $admin->id,
            'title' => 'Evento com Midia',
            'start_at' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $event = Event::query()->findOrFail($eventId);

        $this->app->instance(WatermarkService::class, new class extends WatermarkService
        {
            public function processEventImage($file, Event $event): string
            {
                return 'events/' . $event->id . '/gallery/foto-processada.jpg';
            }
        });

        $image = UploadedFile::fake()->image('foto.jpg', 1200, 800);
        $video = UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4');

        $this->actingAs($admin);

        $request = Request::create('/admin/events/' . $eventId . '/media', 'POST', [], [], [
            'files' => [$image, $video],
        ], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $response = app(EventMediaController::class)->store(
            $request,
            $event,
            app(WatermarkService::class)
        );

        $this->assertSame(200, $response->getStatusCode());
        $payload = $response->getData(true);

        $this->assertTrue((bool) ($payload['success'] ?? false));
        $this->assertSame(2, (int) ($payload['uploaded_count'] ?? 0));
        $this->assertSame(0, (int) ($payload['failed_count'] ?? 0));

        $this->assertSame(2, EventMedia::query()->count());
        $this->assertSame(1, EventMedia::query()->where('type', 'image')->count());
        $this->assertSame(1, EventMedia::query()->where('type', 'video')->count());
    }

    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = array_diff(scandir($directory) ?: [], ['.', '..']);
        foreach ($items as $item) {
            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
                continue;
            }

            @unlink($path);
        }

        @rmdir($directory);
    }
}
