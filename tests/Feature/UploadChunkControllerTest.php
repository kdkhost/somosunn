<?php

namespace Tests\Feature;

use App\Http\Controllers\UploadChunkController;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;

class UploadChunkControllerTest extends TestCase
{
    private string $publicRoot;

    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->publicRoot = storage_path('framework/testing/upload-chunk-public-' . uniqid());
        if (!is_dir($this->publicRoot)) {
            mkdir($this->publicRoot, 0777, true);
        }

        config()->set('filesystems.disks.public.root', $this->publicRoot);
    }

    protected function tearDown(): void
    {
        $tmpRoot = storage_path('app/uploads/tmp');
        if (is_dir($tmpRoot)) {
            foreach (glob($tmpRoot . '/*') ?: [] as $dir) {
                if (is_dir($dir)) {
                    foreach (glob($dir . '/*') ?: [] as $file) {
                        @unlink($file);
                    }
                    @rmdir($dir);
                }
            }
        }

        if (is_dir($this->publicRoot)) {
            foreach (glob($this->publicRoot . '/*') ?: [] as $fileOrDir) {
                if (is_dir($fileOrDir)) {
                    foreach (glob($fileOrDir . '/*') ?: [] as $file) {
                        @unlink($file);
                    }
                    @rmdir($fileOrDir);
                } else {
                    @unlink($fileOrDir);
                }
            }
            @rmdir($this->publicRoot);
        }

        parent::tearDown();
    }

    public function test_chunked_image_can_be_assembled_from_local_disk(): void
    {
        $uploadId = 'test_' . uniqid();
        $chunkDir = storage_path('app/uploads/tmp/' . $uploadId);
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0777, true);
        }

        file_put_contents($chunkDir . '/chunk_0', 'abc');
        file_put_contents($chunkDir . '/chunk_1', 'def');

        $request = Request::create('/upload/assemble', 'POST', [
            'upload_id' => $uploadId,
            'filename' => 'banner.png',
            'total_chunks' => 2,
        ]);

        $response = app(UploadChunkController::class)->assemble($request);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['ok'] ?? false);
        $this->assertNotEmpty($payload['path'] ?? null);
        $this->assertFileExists($this->publicRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $payload['path']));
    }
}
