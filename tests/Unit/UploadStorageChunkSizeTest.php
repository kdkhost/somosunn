<?php

namespace Tests\Unit;

use App\Support\UploadStorage;
use PHPUnit\Framework\TestCase;

class UploadStorageChunkSizeTest extends TestCase
{
    public function test_recommended_chunk_size_stays_below_server_limit_with_margin(): void
    {
        $chunkSize = UploadStorage::recommendedChunkSizeBytes(6 * 1024 * 1024);
        $serverLimit = UploadStorage::effectiveUploadLimitBytes(null, '2M', '8M');

        $this->assertNotNull($serverLimit);
        $this->assertLessThan($serverLimit, $chunkSize);
        $this->assertSame(1572864, $chunkSize);
    }

    public function test_recommended_chunk_size_respects_application_limit_when_smaller(): void
    {
        $chunkSize = UploadStorage::recommendedChunkSizeBytes(700 * 1024);

        $this->assertSame(256 * 1024, $chunkSize);
        $this->assertLessThanOrEqual(700 * 1024, $chunkSize);
    }
}
