<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Setting;
use App\Support\UploadStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class WatermarkService
{
    private const SUPPORTED_RASTER_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const EXCLUDED_DIRECTORIES = [
        'uploads/certificates',
        'uploads/signatures',
        'uploads/imagens/watermark',
        'uploads/imagens/administrativo',
        'uploads/imagens/logins',
        'uploads/imagens/frontend',
        'uploads/imagens/pwa',
        'uploads/imagens/preloader',
        'uploads/imagens/geral',
        'uploads/imagens/seo',
        'uploads/imagens/marketplace',
    ];

    public function processEventImage(UploadedFile $file, Event $event): string
    {
        return $this->processStorageImage(
            $file,
            'events/' . $event->id . '/gallery',
            null,
            ['prefix' => 'event-media']
        );
    }

    public function processStorageImage(
        UploadedFile $file,
        string $directory,
        ?string $filename = null,
        array $options = []
    ): string {
        $directory = $this->normalizeDirectory($directory);

        if (!$file->isValid()) {
            throw new RuntimeException('Arquivo de imagem invalido ou corrompido.');
        }

        $extension = $this->resolveExtensionFromUpload($file);
        $filename = $this->resolveFilename($filename, $extension, (string) ($options['prefix'] ?? 'image'));

        if (!$this->isRasterExtension($extension) || !$this->shouldWatermarkUpload($directory)) {
            return UploadStorage::storeUploadedFile($file, $directory, $filename, ['watermark' => false]);
        }

        $tempOutput = $this->makeTempFile($extension);

        try {
            $this->applyWatermarkToAbsolutePath($file->getRealPath(), $tempOutput);

            if (UploadStorage::isLocal()) {
                $root = (string) config(
                    'filesystems.disks.public.root',
                    is_dir(public_path('storage')) ? public_path('storage') : storage_path('app/public')
                );

                $targetDirectory = $directory !== ''
                    ? rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory)
                    : rtrim($root, DIRECTORY_SEPARATOR);

                if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
                    throw new RuntimeException('Nao foi possivel preparar o diretorio para salvar a imagem com marca d\'agua.');
                }

                $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;

                if (!@rename($tempOutput, $targetPath)) {
                    if (!@copy($tempOutput, $targetPath)) {
                        throw new RuntimeException('Nao foi possivel mover a imagem processada para o destino final.');
                    }

                    @unlink($tempOutput);
                }
            } else {
                $targetPath = ltrim(($directory !== '' ? $directory . '/' : '') . $filename, '/');
                $stream = fopen($tempOutput, 'rb');

                if ($stream === false) {
                    throw new RuntimeException('Nao foi possivel abrir a imagem processada para envio ao storage.');
                }

                try {
                    Storage::disk('public')->put($targetPath, $stream, ['visibility' => 'public']);
                } finally {
                    fclose($stream);
                    @unlink($tempOutput);
                }
            }
        } catch (Throwable $exception) {
            @unlink($tempOutput);
            throw $exception;
        }

        return ltrim(($directory !== '' ? $directory . '/' : '') . $filename, '/');
    }

    public function processPublicImage(
        UploadedFile $file,
        string $relativeDirectory,
        ?string $filename = null,
        array $options = []
    ): string {
        $relativeDirectory = $this->normalizeDirectory($relativeDirectory);

        if (!$file->isValid()) {
            throw new RuntimeException('Arquivo de imagem invalido ou corrompido.');
        }

        $extension = $this->resolveExtensionFromUpload($file);
        $filename = $this->resolveFilename($filename, $extension, (string) ($options['prefix'] ?? 'image'));
        $targetDirectory = public_path($relativeDirectory);

        if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException('Nao foi possivel preparar o diretorio publico para salvar a imagem.');
        }

        $targetPath = rtrim($targetDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (!$this->isRasterExtension($extension) || !$this->shouldWatermarkUpload($relativeDirectory)) {
            $file->move($targetDirectory, $filename);

            return ltrim($relativeDirectory . '/' . $filename, '/');
        }

        $tempOutput = $this->makeTempFile($extension);

        try {
            $this->applyWatermarkToAbsolutePath($file->getRealPath(), $tempOutput);

            if (!@rename($tempOutput, $targetPath)) {
                if (!@copy($tempOutput, $targetPath)) {
                    throw new RuntimeException('Nao foi possivel salvar a imagem com marca d\'agua no diretorio publico.');
                }

                @unlink($tempOutput);
            }
        } catch (Throwable $exception) {
            @unlink($tempOutput);
            throw $exception;
        }

        return ltrim($relativeDirectory . '/' . $filename, '/');
    }

    public function applyWatermarkToAbsolutePath(string $sourcePath, string $destinationPath): void
    {
        [$image, $width, $height] = $this->loadRasterImage($sourcePath);
        $destinationExtension = strtolower((string) pathinfo($destinationPath, PATHINFO_EXTENSION));
        [$image, $width, $height] = $this->optimizeRasterImage($image, $width, $height, $destinationExtension);
        $logoPath = $this->resolveWatermarkLogoPath();
        $settings = $this->imageWatermarkSettings();
        $logo = null;
        $resizedLogo = null;

        if ($logoPath !== null) {
            [$logo, $logoWidth, $logoHeight] = $this->loadRasterImage($logoPath);
            $sizePercent = max(1, min(60, (int) $settings['size_percent']));
            $margin = max(0, min(300, (int) $settings['margin']));
            $opacity = max(5, min(100, (int) $settings['opacity']));

            $targetLogoWidth = max(24, (int) round($width * ($sizePercent / 100)));
            $targetLogoHeight = max(24, (int) round(($logoHeight / max(1, $logoWidth)) * $targetLogoWidth));

            if ($targetLogoHeight > $height * 0.6) {
                $targetLogoHeight = (int) round($height * 0.6);
                $targetLogoWidth = max(24, (int) round(($logoWidth / max(1, $logoHeight)) * $targetLogoHeight));
            }

            $resizedLogo = imagecreatetruecolor($targetLogoWidth, $targetLogoHeight);
            imagealphablending($resizedLogo, false);
            imagesavealpha($resizedLogo, true);
            $transparent = imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127);
            imagefilledrectangle($resizedLogo, 0, 0, $targetLogoWidth, $targetLogoHeight, $transparent);
            imagecopyresampled(
                $resizedLogo,
                $logo,
                0,
                0,
                0,
                0,
                $targetLogoWidth,
                $targetLogoHeight,
                $logoWidth,
                $logoHeight
            );

            [$destX, $destY] = $this->resolvePosition(
                (string) $settings['position'],
                $width,
                $height,
                $targetLogoWidth,
                $targetLogoHeight,
                $margin
            );

            imagealphablending($image, true);
            $this->imagecopymergeAlpha(
                $image,
                $resizedLogo,
                $destX,
                $destY,
                0,
                0,
                $targetLogoWidth,
                $targetLogoHeight,
                $opacity
            );
        }

        $destinationDirectory = dirname($destinationPath);
        if (!is_dir($destinationDirectory) && !@mkdir($destinationDirectory, 0755, true) && !is_dir($destinationDirectory)) {
            imagedestroy($image);
            imagedestroy($logo);
            imagedestroy($resizedLogo);
            throw new RuntimeException('Nao foi possivel preparar o destino da imagem com marca d\'agua.');
        }

        $quality = $this->imageOptimizationSettings();
        $saved = match ($destinationExtension) {
            'jpg', 'jpeg' => tap($image, static fn ($resource) => imageinterlace($resource, true))
                && imagejpeg($image, $destinationPath, $quality['jpeg_quality']),
            'png' => imagepng($image, $destinationPath, $quality['png_compression']),
            'gif' => imagegif($image, $destinationPath),
            'webp' => function_exists('imagewebp') ? imagewebp($image, $destinationPath, $quality['webp_quality']) : false,
            default => tap($image, static fn ($resource) => imageinterlace($resource, true))
                && imagejpeg($image, $destinationPath, $quality['jpeg_quality']),
        };

        imagedestroy($image);
        if (is_resource($logo) || $logo instanceof \GdImage) {
            imagedestroy($logo);
        }
        if (is_resource($resizedLogo) || $resizedLogo instanceof \GdImage) {
            imagedestroy($resizedLogo);
        }

        if (!$saved || !is_file($destinationPath)) {
            throw new RuntimeException('Nao foi possivel gravar a imagem final com marca d\'agua.');
        }
    }

    public function shouldWatermarkUpload(?string $directory = null): bool
    {
        $enabled = $this->toBoolean(
            Setting::get('image_watermark_enabled', Setting::get('video_watermark_enabled', '1'))
        );

        if (!$enabled) {
            return false;
        }

        $directory = $this->normalizeDirectory((string) $directory);
        if ($directory === '') {
            return true;
        }

        foreach (self::EXCLUDED_DIRECTORIES as $excluded) {
            if ($directory === $excluded || str_starts_with($directory, $excluded . '/')) {
                return false;
            }
        }

        return $this->hasTransparentImageWatermark();
    }

    public function isWatermarkableImage(UploadedFile|string $file): bool
    {
        if ($file instanceof UploadedFile) {
            return $this->isRasterExtension($this->resolveExtensionFromUpload($file));
        }

        return $this->isRasterExtension(strtolower((string) pathinfo($file, PATHINFO_EXTENSION)));
    }

    public function hasTransparentImageWatermark(): bool
    {
        return $this->resolveWatermarkLogoPath() !== null;
    }

    public function isTransparentWatermarkFile(UploadedFile|string $file): bool
    {
        if ($file instanceof UploadedFile) {
            $path = $file->getRealPath();
        } else {
            $value = trim((string) $file);
            $path = is_file($value) ? $value : $this->resolvePhysicalPath($value);
        }

        if (!$path || !is_file($path)) {
            return false;
        }

        return $this->imageHasTransparency($path);
    }

    private function loadRasterImage(string $absolutePath): array
    {
        if (!is_file($absolutePath)) {
            throw new RuntimeException('Arquivo de imagem nao encontrado para aplicacao da marca d\'agua.');
        }

        $imageInfo = @getimagesize($absolutePath);
        if (!is_array($imageInfo) || !isset($imageInfo[0], $imageInfo[1], $imageInfo[2])) {
            throw new RuntimeException('Nao foi possivel ler os metadados da imagem enviada.');
        }

        $image = match ((int) $imageInfo[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($absolutePath),
            IMAGETYPE_PNG => @imagecreatefrompng($absolutePath),
            IMAGETYPE_GIF => @imagecreatefromgif($absolutePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
            default => false,
        };

        if (!$image) {
            throw new RuntimeException('Formato de imagem nao suportado para marca d\'agua.');
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        return [$image, (int) $imageInfo[0], (int) $imageInfo[1]];
    }

    private function resolveWatermarkLogoPath(): ?string
    {
        $candidates = [
            Setting::get('watermark_image'),
        ];

        foreach ($candidates as $candidate) {
            $path = $this->resolvePhysicalPath((string) $candidate);
            if (
                $path !== null
                && $this->isRasterExtension((string) pathinfo($path, PATHINFO_EXTENSION))
                && $this->imageHasTransparency($path)
            ) {
                return $path;
            }
        }

        return null;
    }

    private function applyTextWatermark($image, int $width, int $height, array $settings): void
    {
        $text = trim((string) (
            Setting::get('company_name')
            ?: Setting::get('app_name')
            ?: config('app.name', 'SOMOS UNN')
        ));

        if ($text === '') {
            $text = 'SOMOS UNN';
        }

        $font = match (true) {
            $width >= 1600 => 5,
            $width >= 1000 => 4,
            default => 3,
        };

        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $margin = max(0, min(300, (int) $settings['margin']));
        $opacity = max(5, min(100, (int) $settings['opacity']));
        [$x, $y] = $this->resolvePosition(
            (string) $settings['position'],
            $width,
            $height,
            $textWidth,
            $textHeight,
            $margin
        );

        $alpha = 127 - (int) round(($opacity / 100) * 127);
        $shadow = imagecolorallocatealpha($image, 0, 0, 0, min(127, $alpha + 20));
        $color = imagecolorallocatealpha($image, 255, 255, 255, max(0, min(127, $alpha)));

        imagestring($image, $font, $x + 1, $y + 1, $text, $shadow);
        imagestring($image, $font, $x, $y, $text, $color);
    }

    private function resolvePhysicalPath(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $normalized = UploadStorage::normalizePath($value);
        $candidates = array_filter(array_unique([
            public_path(ltrim($value, '/')),
            $normalized ? public_path($normalized) : null,
            $normalized ? public_path('storage/' . $normalized) : null,
            $normalized ? storage_path('app/public/' . $normalized) : null,
            $normalized ? storage_path('app/' . $normalized) : null,
        ]));

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function imageWatermarkSettings(): array
    {
        $position = trim((string) Setting::get('image_watermark_position', Setting::get('watermark_position', 'bottom-right')));
        $allowedPositions = ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'];
        if (!in_array($position, $allowedPositions, true)) {
            $position = 'bottom-right';
        }

        $opacity = (int) Setting::get('image_watermark_opacity', Setting::get('watermark_opacity', 30));
        $sizePercent = (int) Setting::get('image_watermark_size_percent', 12);
        $margin = (int) Setting::get('image_watermark_margin', 20);

        return [
            'position' => $position,
            'opacity' => max(5, min(100, $opacity)),
            'size_percent' => max(1, min(60, $sizePercent)),
            'margin' => max(0, min(300, $margin)),
        ];
    }

    private function imageOptimizationSettings(): array
    {
        return [
            'max_width' => max(1200, min(5000, (int) Setting::get('image_upload_optimize_max_width', 2400))),
            'max_height' => max(1200, min(5000, (int) Setting::get('image_upload_optimize_max_height', 2400))),
            'jpeg_quality' => max(65, min(92, (int) Setting::get('image_upload_jpeg_quality', 84))),
            'webp_quality' => max(65, min(92, (int) Setting::get('image_upload_webp_quality', 84))),
            'png_compression' => max(0, min(9, (int) Setting::get('image_upload_png_compression', 7))),
        ];
    }

    private function resolvePosition(
        string $position,
        int $imageWidth,
        int $imageHeight,
        int $logoWidth,
        int $logoHeight,
        int $margin
    ): array {
        return match ($position) {
            'top-left' => [$margin, $margin],
            'top-right' => [$imageWidth - $logoWidth - $margin, $margin],
            'bottom-left' => [$margin, $imageHeight - $logoHeight - $margin],
            'center' => [
                (int) round(($imageWidth - $logoWidth) / 2),
                (int) round(($imageHeight - $logoHeight) / 2),
            ],
            default => [$imageWidth - $logoWidth - $margin, $imageHeight - $logoHeight - $margin],
        };
    }

    private function optimizeRasterImage($image, int $width, int $height, string $extension): array
    {
        $settings = $this->imageOptimizationSettings();
        $maxWidth = max(1, (int) $settings['max_width']);
        $maxHeight = max(1, (int) $settings['max_height']);

        if ($width <= $maxWidth && $height <= $maxHeight) {
            return [$image, $width, $height];
        }

        $scale = min($maxWidth / max(1, $width), $maxHeight / max(1, $height));
        if ($scale >= 1) {
            return [$image, $width, $height];
        }

        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if (in_array($extension, ['png', 'gif', 'webp'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        } else {
            $background = imagecolorallocate($canvas, 255, 255, 255);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $background);
        }

        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);

        return [$canvas, $targetWidth, $targetHeight];
    }

    private function imageHasTransparency(string $absolutePath): bool
    {
        $imageInfo = @getimagesize($absolutePath);
        if (!is_array($imageInfo) || !isset($imageInfo[2])) {
            return false;
        }

        $image = match ((int) $imageInfo[2]) {
            IMAGETYPE_PNG => @imagecreatefrompng($absolutePath),
            IMAGETYPE_GIF => @imagecreatefromgif($absolutePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
            default => false,
        };

        if (!$image) {
            return false;
        }

        try {
            $width = imagesx($image);
            $height = imagesy($image);

            if ($width <= 0 || $height <= 0) {
                return false;
            }

            $points = [
                [0, 0],
                [$width - 1, 0],
                [0, $height - 1],
                [$width - 1, $height - 1],
                [(int) floor($width / 2), (int) floor($height / 2)],
            ];

            $stepX = max(1, (int) floor($width / 24));
            $stepY = max(1, (int) floor($height / 24));

            for ($x = 0; $x < $width; $x += $stepX) {
                for ($y = 0; $y < $height; $y += $stepY) {
                    $points[] = [$x, $y];
                }
            }

            foreach ($points as [$x, $y]) {
                $rgba = imagecolorat($image, max(0, $x), max(0, $y));
                $alpha = ($rgba >> 24) & 0x7F;

                if ($alpha > 0) {
                    return true;
                }
            }

            return false;
        } finally {
            imagedestroy($image);
        }
    }

    private function imagecopymergeAlpha(
        $destinationImage,
        $sourceImage,
        int $destinationX,
        int $destinationY,
        int $sourceX,
        int $sourceY,
        int $sourceWidth,
        int $sourceHeight,
        int $opacityPercent
    ): void {
        if ($opacityPercent >= 100) {
            imagecopy(
                $destinationImage,
                $sourceImage,
                $destinationX,
                $destinationY,
                $sourceX,
                $sourceY,
                $sourceWidth,
                $sourceHeight
            );

            return;
        }

        $cut = imagecreatetruecolor($sourceWidth, $sourceHeight);
        imagealphablending($cut, false);
        imagesavealpha($cut, true);
        $transparent = imagecolorallocatealpha($cut, 0, 0, 0, 127);
        imagefilledrectangle($cut, 0, 0, $sourceWidth, $sourceHeight, $transparent);

        imagecopy($cut, $destinationImage, 0, 0, $destinationX, $destinationY, $sourceWidth, $sourceHeight);
        imagecopy($cut, $sourceImage, 0, 0, $sourceX, $sourceY, $sourceWidth, $sourceHeight);
        imagecopymerge($destinationImage, $cut, $destinationX, $destinationY, 0, 0, $sourceWidth, $sourceHeight, $opacityPercent);
        imagedestroy($cut);
    }

    private function normalizeDirectory(string $directory): string
    {
        return trim(str_replace('\\', '/', $directory), '/');
    }

    private function resolveFilename(?string $filename, string $extension, string $prefix): string
    {
        $filename = trim((string) $filename);
        if ($filename !== '') {
            $base = pathinfo($filename, PATHINFO_FILENAME);
            $currentExtension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
            $resolvedExtension = $this->isRasterExtension($currentExtension) ? $currentExtension : $extension;

            return $base . '.' . $resolvedExtension;
        }

        return trim($prefix, '-_ ') . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    }

    private function resolveExtensionFromUpload(UploadedFile $file): string
    {
        return strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin'));
    }

    private function isRasterExtension(string $extension): bool
    {
        return in_array(strtolower(trim($extension)), self::SUPPORTED_RASTER_EXTENSIONS, true);
    }

    private function makeTempFile(string $extension): string
    {
        $extension = $this->isRasterExtension($extension) ? $extension : 'jpg';
        $directory = storage_path('app/tmp-watermarks');

        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Nao foi possivel preparar o diretorio temporario da marca d\'agua.');
        }

        return $directory . DIRECTORY_SEPARATOR . uniqid('wm_', true) . '.' . $extension;
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }
}
