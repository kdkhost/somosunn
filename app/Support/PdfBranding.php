<?php

namespace App\Support;

use App\Models\Setting;
use Dompdf\Dompdf;

class PdfBranding
{
    public static function applyDefaultLogoWatermark(Dompdf $dompdf): void
    {
        $logoPath = self::resolveLogoPath();
        if (!$logoPath) {
            return;
        }

        $logoPath = str_replace('\\', '/', $logoPath);
        [$ratioWidth, $ratioHeight] = self::imageRatio($logoPath);

        try {
            $canvas = $dompdf->getCanvas();
            $canvas->page_script(function ($pageNumber, $pageCount, $canvas) use ($logoPath, $ratioWidth, $ratioHeight) {
                $pageWidth = (float) $canvas->get_width();
                $pageHeight = (float) $canvas->get_height();

                $maxWidth = $pageWidth * 0.46;
                $maxHeight = $pageHeight * 0.22;
                $logoWidth = $maxWidth;
                $logoHeight = $logoWidth * ($ratioHeight / max(1.0, $ratioWidth));

                if ($logoHeight > $maxHeight) {
                    $logoHeight = $maxHeight;
                    $logoWidth = $logoHeight * ($ratioWidth / max(1.0, $ratioHeight));
                }

                $x = ($pageWidth - $logoWidth) / 2;
                $y = ($pageHeight - $logoHeight) / 2;

                $canvas->save();
                $canvas->set_opacity(0.065);
                $canvas->image($logoPath, $x, $y, $logoWidth, $logoHeight);
                $canvas->restore();
            });
        } catch (\Throwable $e) {
            report($e);
        }
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
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array{0:float,1:float}
     */
    private static function imageRatio(string $path): array
    {
        $size = @getimagesize($path);
        if (is_array($size) && (float) ($size[0] ?? 0) > 0 && (float) ($size[1] ?? 0) > 0) {
            return [(float) $size[0], (float) $size[1]];
        }

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'svg') {
            $svg = @file_get_contents($path);
            if (is_string($svg) && preg_match('/viewBox=["\']\s*[-\d.]+\s+[-\d.]+\s+([\d.]+)\s+([\d.]+)\s*["\']/i', $svg, $matches)) {
                return [(float) $matches[1], (float) $matches[2]];
            }
        }

        return [3.0, 1.0];
    }
}
