<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PublicStorageProxyTest extends TestCase
{
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    public function test_storage_route_serves_files_from_public_storage_directory(): void
    {
        $path = public_path('storage/testing/storage-proxy.txt');
        $this->putTestFile($path, 'storage-proxy');

        $response = $this->get('/storage/testing/storage-proxy.txt');

        $response->assertOk();
        $this->assertSame('storage-proxy', $response->streamedContent());
    }

    public function test_storage_route_serves_legacy_uploads_paths(): void
    {
        $path = public_path('uploads/testing/legacy-storage-proxy.txt');
        $this->putTestFile($path, 'legacy-storage-proxy');

        $response = $this->get('/storage/uploads/testing/legacy-storage-proxy.txt');

        $response->assertOk();
        $this->assertSame('legacy-storage-proxy', $response->streamedContent());
    }

    public function test_uploads_route_serves_legacy_uploads_paths(): void
    {
        $path = public_path('uploads/testing/uploads-proxy.txt');
        $this->putTestFile($path, 'uploads-proxy');

        $response = $this->get('/uploads/testing/uploads-proxy.txt');

        $response->assertOk();
        $this->assertSame('uploads-proxy', $response->streamedContent());
    }

    private function putTestFile(string $path, string $contents): void
    {
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, $contents);
        $this->createdFiles[] = $path;
    }
}
