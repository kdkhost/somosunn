<?php

namespace Tests\Unit;

use App\Support\UploadStorage;
use Tests\TestCase;

class UploadStorageUrlTest extends TestCase
{
    protected function tearDown(): void
    {
        config()->set('uploads.s3_driver_available', null);

        parent::tearDown();
    }

    public function test_it_uses_storage_proxy_for_relative_paths(): void
    {
        config()->set('uploads.effective_disk', 'public');
        config()->set('filesystems.disks.public.url', '/storage');

        $this->assertSame(asset('storage/plan-images/pro.png'), UploadStorage::url('plan-images/pro.png'));
        $this->assertSame(asset('uploads/imagens/logo.png'), UploadStorage::url('uploads/imagens/logo.png'));
    }

    public function test_it_keeps_same_host_s3_urls_on_application_proxy(): void
    {
        config()->set('app.url', 'https://somosunn.com.br');
        config()->set('uploads.effective_disk', 's3');
        config()->set('filesystems.disks.public.url', 'https://somosunn.com.br');

        $this->assertSame(asset('storage/certificates/file.pdf'), UploadStorage::url('certificates/file.pdf'));
    }

    public function test_it_uses_distinct_public_base_url_when_cdn_is_configured(): void
    {
        config()->set('app.url', 'https://somosunn.com.br');
        config()->set('uploads.effective_disk', 's3');
        config()->set('filesystems.disks.public.url', 'https://cdn.somosunn.com.br');

        $this->assertSame(
            'https://cdn.somosunn.com.br/uploads/imagens/logo.png',
            UploadStorage::url('uploads/imagens/logo.png')
        );
    }

    public function test_it_falls_back_to_local_when_s3_driver_is_not_available(): void
    {
        config()->set('uploads.s3_driver_available', false);

        UploadStorage::applyRuntimeConfig([
            'uploads_storage_disk' => 's3',
            's3_key' => 'test-key',
            's3_secret' => 'test-secret',
            's3_region' => 'ca-east-1',
            's3_bucket' => 'somosunn',
        ]);

        $this->assertSame('s3', config('uploads.selected_disk'));
        $this->assertSame('public', UploadStorage::effectiveDisk());
        $this->assertSame('local', config('filesystems.disks.public.driver'));
    }
}
