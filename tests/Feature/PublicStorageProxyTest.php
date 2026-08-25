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
        $path = public_path('storage/testing/storage-proxy.pdf');
        $this->putTestFile($path, 'storage-proxy');

        $response = $this->get('/storage/testing/storage-proxy.pdf');

        $response->assertOk();
        $this->assertSame('storage-proxy', $response->streamedContent());
    }

    public function test_storage_route_serves_legacy_uploads_paths(): void
    {
        $path = public_path('uploads/testing/legacy-storage-proxy.pdf');
        $this->putTestFile($path, 'legacy-storage-proxy');

        $response = $this->get('/storage/uploads/testing/legacy-storage-proxy.pdf');

        $response->assertOk();
        $this->assertSame('legacy-storage-proxy', $response->streamedContent());
    }

    public function test_uploads_route_serves_legacy_uploads_paths(): void
    {
        $path = public_path('uploads/testing/uploads-proxy.pdf');
        $this->putTestFile($path, 'uploads-proxy');

        $response = $this->get('/uploads/testing/uploads-proxy.pdf');

        $response->assertOk();
        $this->assertSame('uploads-proxy', $response->streamedContent());
    }

    public function test_storage_route_supports_partial_content_for_fast_pdf_loading(): void
    {
        $path = public_path('storage/testing/range-proxy.pdf');
        $this->putTestFile($path, '0123456789');

        $response = $this->withHeader('Range', 'bytes=2-5')
            ->get('/storage/testing/range-proxy.pdf');

        $response->assertStatus(206);
        $response->assertHeader('Accept-Ranges', 'bytes');
        $response->assertHeader('Content-Range', 'bytes 2-5/10');
        $response->assertHeader('Content-Length', '4');
        $this->assertSame('2345', $response->streamedContent());
    }

    public function test_storage_route_rejects_invalid_byte_range(): void
    {
        $path = public_path('storage/testing/invalid-range-proxy.pdf');
        $this->putTestFile($path, '0123456789');

        $response = $this->withHeader('Range', 'bytes=20-30')
            ->get('/storage/testing/invalid-range-proxy.pdf');

        $response->assertStatus(416);
        $response->assertHeader('Content-Range', 'bytes */10');
    }

    public function test_storage_route_never_exposes_resumes(): void
    {
        $path = public_path('storage/resumes/private-candidate.pdf');
        $this->putTestFile($path, 'personal-data');

        $this->get('/storage/resumes/private-candidate.pdf')->assertNotFound();
    }

    private function putTestFile(string $path, string $contents): void
    {
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, $contents);
        $this->createdFiles[] = $path;
    }
}
