<?php

namespace App\Support;

class ApiMedia
{
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return UploadStorage::url($path);
    }
}
