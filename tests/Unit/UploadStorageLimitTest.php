<?php

namespace Tests\Unit;

use App\Support\UploadStorage;
use PHPUnit\Framework\TestCase;

class UploadStorageLimitTest extends TestCase
{
    public function test_parse_ini_size_to_bytes_supports_common_units(): void
    {
        $this->assertSame(512, UploadStorage::parseIniSizeToBytes('512'));
        $this->assertSame(2 * 1024 * 1024, UploadStorage::parseIniSizeToBytes('2M'));
        $this->assertSame(8 * 1024 * 1024, UploadStorage::parseIniSizeToBytes('8MB'));
        $this->assertSame(1024, UploadStorage::parseIniSizeToBytes('1K'));
        $this->assertNull(UploadStorage::parseIniSizeToBytes(''));
    }

    public function test_effective_upload_limit_bytes_uses_smallest_limit(): void
    {
        $this->assertSame(
            2 * 1024 * 1024,
            UploadStorage::effectiveUploadLimitBytes(6 * 1024 * 1024, '2M', '8M')
        );

        $this->assertSame(
            6 * 1024 * 1024,
            UploadStorage::effectiveUploadLimitBytes(6 * 1024 * 1024, '12M', '16M')
        );

        $this->assertSame(
            8 * 1024 * 1024,
            UploadStorage::effectiveUploadLimitBytes(null, '8M', '16M')
        );
    }
}
