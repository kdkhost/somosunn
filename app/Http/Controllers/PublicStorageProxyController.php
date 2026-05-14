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
        // Proteção contra path traversal e acesso a arquivos sensíveis
        $decodedPath = urldecode($path);
        if (str_contains($decodedPath, '..') ||
            str_contains($decodedPath, '.env') ||
            str_contains($decodedPath, 'config/') ||
            str_contains($decodedPath, 'database/') ||
            str_contains($decodedPath, 'vendor/') ||
            str_contains($decodedPath, 'storage/logs') ||
            str_contains($decodedPath, 'app/') ||
            str_contains($decodedPath, 'bootstrap/')) {
            \Illuminate\Support\Facades\Log::warning('Tentativa de path traversal bloqueada', [
                'ip'   => request()->ip(),
                'path' => $decodedPath,
                'url'  => request()->fullUrl(),
            ]);
            abort(403);
        }

        // Validar extensão permitida
        $allowedExtensions = [
            'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico',
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'mp4', 'webm', 'mp3', 'ogg', 'wav',
            'zip', 'rar', 'csv', 'txt', 'json',
            'ttf', 'otf', 'woff', 'woff2', 'eot',
        ];
        $extension = strtolower(pathinfo($decodedPath, PATHINFO_EXTENSION));
        if ($extension !== '' && !in_array($extension, $allowedExtensions, true)) {
            \Illuminate\Support\Facades\Log::warning('Extensão de arquivo bloqueada em storage/uploads', [
                'ip'        => request()->ip(),
                'path'      => $decodedPath,
                'extension' => $extension,
            ]);
            abort(403);
        }

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
