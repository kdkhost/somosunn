<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Panel\GalleryController as PanelGalleryController;
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

class EventGalleryManagementTest extends TestCase
{
    private string $sqlitePath;
    private string $publicRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlitePath = database_path('testing-event-gallery-management.sqlite');
        $this->publicRoot = storage_path('app/testing-event-gallery-management-public');

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
            $table->boolean('published')->default(true);
            $table->timestamp('start_at')->nullable();
            $table->string('gallery_cover_image')->nullable();
            $table->unsignedBigInteger('gallery_cover_media_id')->nullable();
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

    public function test_admin_gallery_upload_returns_json_for_ajax_requests(): void
    {
        $admin = User::create([
            'name' => 'Admin Galeria',
            'email' => 'admin-galeria@example.com',
            'role' => 'admin',
        ]);

        $event = Event::query()->create([
            'user_id' => $admin->id,
            'title' => 'Evento com Album',
            'published' => true,
            'start_at' => now()->addDay(),
        ]);

        $this->app->instance(WatermarkService::class, new class extends WatermarkService
        {
            public function processEventImage($file, Event $event): string
            {
                return 'events/' . $event->id . '/gallery/capa-processada.jpg';
            }
        });

        $this->actingAs($admin);

        $request = Request::create('/admin/galeria/upload', 'POST', [
            'event_id' => $event->id,
        ], [], [
            'files' => [
                UploadedFile::fake()->image('album.jpg', 1200, 800),
            ],
        ], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $response = app(AdminGalleryController::class)->upload($request, app(WatermarkService::class));

        $this->assertSame(200, $response->getStatusCode());
        $payload = $response->getData(true);

        $this->assertTrue((bool) ($payload['success'] ?? false));
        $this->assertSame(1, (int) ($payload['uploaded_count'] ?? 0));
        $this->assertCount(1, $payload['media'] ?? []);
        $this->assertSame(1, EventMedia::query()->count());
    }

    public function test_panel_organizer_can_define_album_cover_from_existing_media(): void
    {
        $organizer = User::create([
            'name' => 'Organizador',
            'email' => 'organizador@example.com',
            'role' => 'member',
        ]);

        $member = User::create([
            'name' => 'Convidado',
            'email' => 'convidado@example.com',
            'role' => 'member',
        ]);

        $event = Event::query()->create([
            'user_id' => $organizer->id,
            'title' => 'Evento Organizado',
            'published' => true,
            'start_at' => now()->addDay(),
        ]);

        $media = EventMedia::query()->create([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'file_path' => 'events/' . $event->id . '/gallery/foto.jpg',
            'type' => 'image',
            'watermarked' => true,
        ]);

        $this->actingAs($organizer);

        $request = Request::create('/painel/galeria/media/' . $media->id . '/capa', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $response = app(PanelGalleryController::class)->setCoverFromMedia($request, $media);

        $this->assertSame(200, $response->getStatusCode());
        $payload = $response->getData(true);

        $this->assertTrue((bool) ($payload['success'] ?? false));
        $this->assertSame($event->id, (int) ($payload['event_id'] ?? 0));
        $this->assertSame($media->id, (int) $event->fresh()->gallery_cover_media_id);
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
