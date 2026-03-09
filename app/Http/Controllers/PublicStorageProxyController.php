<?php

namespace App\Http\Controllers;

use App\Support\UploadStorage;

class PublicStorageProxyController extends Controller
{
    public function storage(string $path)
    {
        return $this->servePath($path, false);
    }

    public function uploads(string $path)
    {
        return $this->servePath($path, true);
    }

    private function servePath(string $path, bool $uploadsRoute)
    {
        foreach ($this->candidatePaths($path, $uploadsRoute) as $candidate) {
            if (UploadStorage::exists($candidate)) {
                return UploadStorage::response($candidate);
            }
        }

        abort(404);
    }

    private function candidatePaths(string $path, bool $uploadsRoute): array
    {
        $raw = trim(str_replace('\\', '/', $path), '/');
        $normalized = UploadStorage::normalizePath($raw);
        $candidates = [];

        if ($uploadsRoute) {
            $candidates[] = str_starts_with($raw, 'uploads/') ? $raw : 'uploads/' . $raw;
            if ($normalized !== null) {
                $candidates[] = str_starts_with($normalized, 'uploads/') ? $normalized : 'uploads/' . $normalized;
            }
        } else {
            if ($raw !== '') {
                $candidates[] = $raw;
            }
            if ($normalized !== null) {
                $candidates[] = $normalized;
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }
}
