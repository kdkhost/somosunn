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
        // Double-decode para prevenir bypass com %252e%252e etc.
        $decodedPath = urldecode(urldecode($path));

        // Bloquear padrões perigosos
        $blockedPatterns = [
            '..',
            '.env',
            'vendor/',
            'storage/logs',
            'config/',
            'database/',
            'app/',
            'bootstrap/',
        ];

        foreach ($blockedPatterns as $pattern) {
            if (str_contains($decodedPath, $pattern)) {
                \Illuminate\Support\Facades\Log::channel('security')->warning('Path traversal bloqueado em storage proxy', [
                    'ip'      => request()->ip(),
                    'path'    => $decodedPath,
                    'pattern' => $pattern,
                    'url'     => request()->fullUrl(),
                ]);
                abort(403);
            }
        }

        // Bloquear extensões perigosas (executáveis/scripts)
        $blockedExtensions = [
            'php', 'phtml', 'phar', 'cgi', 'exe', 'sh', 'bat', 'cmd', 'js',
        ];

        $extension = strtolower(pathinfo($decodedPath, PATHINFO_EXTENSION));

        if ($extension !== '' && in_array($extension, $blockedExtensions, true)) {
            \Illuminate\Support\Facades\Log::channel('security')->warning('Extensao perigosa bloqueada em storage proxy', [
                'ip'        => request()->ip(),
                'path'      => $decodedPath,
                'extension' => $extension,
            ]);
            abort(403);
        }

        // Validar extensão permitida (whitelist)
        $allowedExtensions = [
            'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg',
            'pdf',
            'mp4', 'webm', 'mp3', 'wav', 'ogg',
            'ttf', 'otf', 'woff', 'woff2', 'eot',
        ];

        if ($extension !== '' && !in_array($extension, $allowedExtensions, true)) {
            \Illuminate\Support\Facades\Log::channel('security')->warning('Extensao nao permitida em storage proxy', [
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
