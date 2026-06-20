<?php

namespace App\Support;

use App\Models\Setting;

class PdfBranding
{
    public static function injectDefaultLogoWatermark(string $html): string
    {
        $logoSource = self::resolveLogoSource();
        if (!$logoSource) {
            return $html;
        }

        $style = <<<'HTML'
<style>
    .pdf-brand-watermark {
        position: fixed;
        inset: 0;
        opacity: 0.15;
        z-index: 0;
        pointer-events: none;
    }

    .pdf-brand-watermark-inner {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 240px;
        margin-top: -52px;
        margin-left: -120px;
        text-align: center;
    }

    .pdf-brand-watermark img {
        display: block;
        width: 100%;
        height: auto;
        max-height: 104px;
        object-fit: contain;
    }

    .pdf-brand-content {
        position: relative;
        z-index: 1;
    }
</style>
HTML;

        if (stripos($html, '</head>') !== false) {
            $html = preg_replace('/<\/head>/i', $style . "\n</head>", $html, 1) ?? $html;
        } else {
            $html = $style . "\n" . $html;
        }

        $watermark = '<div class="pdf-brand-watermark"><div class="pdf-brand-watermark-inner"><img src="' . htmlspecialchars($logoSource, ENT_QUOTES, 'UTF-8') . '" alt=""></div></div><div class="pdf-brand-content">';

        if (stripos($html, '<body') !== false) {
            $html = preg_replace('/(<body\b[^>]*>)/i', '$1' . $watermark, $html, 1) ?? $html;
            $html = preg_replace('/<\/body>/i', '</div></body>', $html, 1) ?? $html;

            return $html;
        }

        return $watermark . $html . '</div>';
    }

    private static function resolveLogoSource(): ?string
    {
        $logoPath = self::resolveLogoPath();
        if (!$logoPath) {
            return null;
        }

        try {
            $contents = file_get_contents($logoPath);
            if ($contents === false || $contents === '') {
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        $extension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => null,
        };

        if ($mimeType === null) {
            return null;
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }

    private static function resolveLogoPath(): ?string
    {
        foreach (['logo_admin', 'logo_front', 'logo_image', 'logo_auth'] as $key) {
            $path = self::normalizeLocalPath((string) Setting::get($key, ''));
            if ($path) {
                return $path;
            }
        }

        return self::normalizeLocalPath('img/logo.svg');
    }

    private static function normalizeLocalPath(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || preg_match('#^https?://#i', $value)) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $value), '/');
        foreach (['public/', 'storage/app/public/'] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = substr($normalized, strlen($prefix));
                break;
            }
        }

        foreach ([public_path($normalized), storage_path('app/public/' . $normalized), $value] as $candidate) {
            if (is_string($candidate) && is_file($candidate)) {
                $extension = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
                if (in_array($extension, ['svg', 'png', 'jpg', 'jpeg'], true)) {
                    return $candidate;
                }
            }
        }

        return null;
    }
}
