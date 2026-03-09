<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class UploadStorage
{
    public static function applyRuntimeConfig(array $settings = []): void
    {
        $localConfig = self::localPublicDiskConfig();
        $selectedDisk = self::selectedDisk($settings);
        $effectiveDisk = 'public';

        if ($selectedDisk === 's3' && self::s3Configured($settings)) {
            $s3Config = self::s3DiskConfig($settings);
            $effectiveDisk = 's3';

            config([
                'uploads.disk' => 's3',
                'filesystems.cloud' => 's3',
                'filesystems.disks.public' => $s3Config,
                'filesystems.disks.uploads' => $s3Config,
                'filesystems.disks.local_public' => $localConfig,
                'filesystems.disks.s3' => array_merge((array) config('filesystems.disks.s3', []), $s3Config),
            ]);
        } else {
            config([
                'uploads.disk' => 'public',
                'filesystems.cloud' => 'public',
                'filesystems.disks.public' => $localConfig,
                'filesystems.disks.uploads' => $localConfig,
                'filesystems.disks.local_public' => $localConfig,
            ]);
        }

        config([
            'uploads.selected_disk' => $selectedDisk,
            'uploads.effective_disk' => $effectiveDisk,
        ]);

        self::forgetDisks();
    }

    public static function selectedDisk(array $settings = []): string
    {
        $value = trim((string) ($settings['uploads_storage_disk'] ?? config('uploads.selected_disk', config('uploads.disk', 'public'))));

        return in_array($value, ['public', 's3'], true) ? $value : 'public';
    }

    public static function effectiveDisk(): string
    {
        $value = trim((string) config('uploads.effective_disk', config('uploads.disk', 'public')));

        return in_array($value, ['public', 's3'], true) ? $value : 'public';
    }

    public static function disk()
    {
        return Storage::disk('public');
    }

    public static function isLocal(): bool
    {
        return self::effectiveDisk() !== 's3';
    }

    public static function normalizePath(?string $path): ?string
    {
        $value = trim((string) $path);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            $value = (string) parse_url($value, PHP_URL_PATH);
        }

        $value = str_replace('\\', '/', $value);
        $value = preg_replace('/[?#].*$/', '', $value) ?? $value;
        $value = trim($value, '/');

        foreach (['storage/app/public/', 'public/'] as $prefix) {
            if (str_starts_with($value, $prefix)) {
                $value = substr($value, strlen($prefix));
            }
        }

        if (str_starts_with($value, 'storage/')) {
            $value = substr($value, strlen('storage/'));
        }

        return $value !== '' ? $value : null;
    }

    public static function url(?string $path, ?string $default = null): ?string
    {
        $raw = trim((string) $path);
        if ($raw === '') {
            return $default;
        }

        if (preg_match('/^https?:\/\//i', $raw)) {
            return $raw;
        }

        $normalized = self::normalizePath($raw);
        if ($normalized === null) {
            return $default;
        }

        $distinctPublicBaseUrl = self::distinctPublicBaseUrl();
        if ($distinctPublicBaseUrl !== null) {
            return self::joinUrl($distinctPublicBaseUrl, $normalized);
        }

        if (self::shouldUseUploadsRoute($raw, $normalized)) {
            return asset($normalized);
        }

        return asset('storage/' . ltrim($normalized, '/'));
    }

    public static function exists(?string $path): bool
    {
        $normalized = self::normalizePath($path);
        if ($normalized === null) {
            return false;
        }

        try {
            if (self::disk()->exists($normalized)) {
                return true;
            }
        } catch (\Throwable $e) {
            // noop
        }

        foreach (self::publicCandidates($normalized) as $candidate) {
            if (is_file($candidate)) {
                return true;
            }
        }

        return false;
    }

    public static function delete(?string $path): bool
    {
        $normalized = self::normalizePath($path);
        if ($normalized === null) {
            return false;
        }

        try {
            if (self::disk()->exists($normalized)) {
                return (bool) self::disk()->delete($normalized);
            }
        } catch (\Throwable $e) {
            // noop
        }

        $deleted = false;
        foreach (self::publicCandidates($normalized) as $candidate) {
            if (is_file($candidate)) {
                @unlink($candidate);
                $deleted = true;
            }
        }

        return $deleted;
    }

    public static function response(string $path)
    {
        $normalized = self::normalizePath($path);
        abort_if($normalized === null, 404);

        if (self::isLocal()) {
            foreach (self::publicCandidates($normalized) as $candidate) {
                if (is_file($candidate)) {
                    $headers = [
                        'Content-Type' => self::mimeTypeFromPath($candidate),
                        'Cache-Control' => 'public, max-age=31536000',
                    ];

                    $size = @filesize($candidate);
                    if (is_int($size) && $size > 0) {
                        $headers['Content-Length'] = (string) $size;
                    }

                    return response()->stream(function () use ($candidate) {
                        $handle = fopen($candidate, 'rb');
                        if ($handle === false) {
                            return;
                        }

                        fpassthru($handle);
                        fclose($handle);
                    }, 200, $headers);
                }
            }
        }

        $stream = self::disk()->readStream($normalized);
        if ($stream === false) {
            abort(404);
        }

        $headers = [
            'Content-Type' => self::mimeType($normalized),
            'Cache-Control' => 'public, max-age=31536000',
        ];

        try {
            $size = (int) self::disk()->size($normalized);
            if ($size > 0) {
                $headers['Content-Length'] = (string) $size;
            }
        } catch (\Throwable $e) {
            // noop
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $headers);
    }

    public static function localPublicDiskConfig(): array
    {
        return [
            'driver' => 'local',
            'root' => is_dir(public_path('storage'))
                ? public_path('storage')
                : storage_path('app/public'),
            'url' => '/storage',
            'visibility' => 'public',
            'throw' => false,
        ];
    }

    private static function s3Configured(array $settings): bool
    {
        return trim((string) ($settings['s3_key'] ?? '')) !== ''
            && trim((string) ($settings['s3_secret'] ?? '')) !== ''
            && trim((string) ($settings['s3_region'] ?? '')) !== ''
            && trim((string) ($settings['s3_bucket'] ?? '')) !== '';
    }

    private static function s3DiskConfig(array $settings): array
    {
        $config = array_merge((array) config('filesystems.disks.s3', []), [
            'driver' => 's3',
            'key' => trim((string) ($settings['s3_key'] ?? '')),
            'secret' => trim((string) ($settings['s3_secret'] ?? '')),
            'region' => trim((string) ($settings['s3_region'] ?? '')),
            'bucket' => trim((string) ($settings['s3_bucket'] ?? '')),
            'endpoint' => self::nullableString($settings['s3_endpoint'] ?? null),
            'url' => self::nullableString($settings['s3_url'] ?? null),
            'use_path_style_endpoint' => self::toBoolean($settings['s3_path_style'] ?? false),
            'throw' => false,
        ]);

        if (empty($config['endpoint'])) {
            unset($config['endpoint']);
        }

        if (empty($config['url'])) {
            unset($config['url']);
        } else {
            $config['url'] = rtrim((string) $config['url'], '/');
        }

        return $config;
    }

    private static function forgetDisks(): void
    {
        try {
            Storage::forgetDisk('public');
            Storage::forgetDisk('uploads');
            Storage::forgetDisk('s3');
            Storage::forgetDisk('local_public');
        } catch (\Throwable $e) {
            // noop
        }
    }

    private static function publicCandidates(string $normalized): array
    {
        $candidates = [
            public_path($normalized),
        ];

        if (!str_starts_with($normalized, 'uploads/')) {
            $candidates[] = public_path('uploads/' . $normalized);
        }

        if (!str_starts_with($normalized, 'storage/')) {
            $candidates[] = public_path('storage/' . $normalized);
        }

        return array_values(array_unique($candidates));
    }

    private static function mimeType(string $path): string
    {
        try {
            return (string) (self::disk()->mimeType($path) ?: self::mimeTypeFromPath($path));
        } catch (\Throwable $e) {
            return self::mimeTypeFromPath($path);
        }
    }

    private static function mimeTypeFromPath(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'txt', 'log', 'csv' => 'text/plain; charset=UTF-8',
            'json' => 'application/json',
            default => 'application/octet-stream',
        };
    }

    private static function shouldUseUploadsRoute(string $raw, string $normalized): bool
    {
        $raw = trim(str_replace('\\', '/', $raw), '/');

        return str_starts_with($raw, 'uploads/') || str_starts_with($normalized, 'uploads/');
    }

    private static function distinctPublicBaseUrl(): ?string
    {
        if (self::effectiveDisk() !== 's3') {
            return null;
        }

        $configured = trim((string) config('filesystems.disks.public.url', config('filesystems.disks.s3.url', '')));
        if ($configured === '' || !filter_var($configured, FILTER_VALIDATE_URL)) {
            return null;
        }

        $configured = rtrim($configured, '/');
        $appUrl = trim((string) config('app.url', ''));
        $configuredHost = strtolower((string) parse_url($configured, PHP_URL_HOST));
        $appHost = strtolower((string) parse_url($appUrl, PHP_URL_HOST));

        if ($configuredHost === '' || ($appHost !== '' && $configuredHost === $appHost)) {
            return null;
        }

        return $configured;
    }

    private static function joinUrl(string $baseUrl, string $path): string
    {
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private static function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }
}
