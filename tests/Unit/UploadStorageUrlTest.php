<?php

namespace Tests\Unit;

use App\Support\UploadStorage;
use Tests\TestCase;

class UploadStorageUrlTest extends TestCase
{
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
}
