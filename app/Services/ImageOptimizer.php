<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Converte e otimiza imagens antes do upload.
 * Suporta HEIC via Imagick (se disponivel) ou rejeita com mensagem clara.
 * Redimensiona imagens grandes e recomprime para JPEG/WEBP/PNG.
 */
class ImageOptimizer
{
    private const MAX_WIDTH  = 2400;
    private const MAX_HEIGHT = 2400;
    private const JPEG_QUALITY = 84;
    private const WEBP_QUALITY = 84;
    private const PNG_COMPRESSION = 7;

    private const HEIC_EXTENSIONS = ['heic', 'heif'];
    private const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif'];

    /**
     * Retorna true se a extensao e suportada (incluindo HEIC).
     */
    public static function isSupportedImage(UploadedFile $file): bool
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        return in_array($ext, self::SUPPORTED_EXTENSIONS, true);
    }

    /**
     * Processa o arquivo: converte HEIC para JPEG se necessario e otimiza.
     * Retorna um novo UploadedFile pronto para ser salvo.
     */
    public function process(UploadedFile $file): UploadedFile
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');

        if (in_array($ext, self::HEIC_EXTENSIONS, true)) {
            return $this->convertHeicToJpeg($file);
        }

        return $this->optimizeGd($file, $ext);
    }

    /**
     * Converte HEIC para JPEG usando Imagick.
     */
    private function convertHeicToJpeg(UploadedFile $file): UploadedFile
    {
        if (!extension_loaded('imagick')) {
            throw new RuntimeException(
                'O formato HEIC requer a extensao Imagick no servidor. ' .
                'Converta a imagem para JPG ou PNG antes de enviar.'
            );
        }

        $imagick = new \Imagick();
        $imagick->readImage($file->getRealPath());
        $imagick->setImageFormat('jpeg');
        $imagick->setImageCompressionQuality(self::JPEG_QUALITY);
        $imagick->stripImage();

        [$w, $h] = [$imagick->getImageWidth(), $imagick->getImageHeight()];
        if ($w > self::MAX_WIDTH || $h > self::MAX_HEIGHT) {
            $imagick->resizeImage(self::MAX_WIDTH, self::MAX_HEIGHT, \Imagick::FILTER_LANCZOS, 1, true);
        }

        $tmpPath = $this->tempPath('heic_', 'jpg');
        $imagick->writeImage($tmpPath);
        $imagick->clear();
        $imagick->destroy();

        return $this->wrapTempFile($tmpPath, $file->getClientOriginalName(), 'image/jpeg', 'jpg');
    }

    /**
     * Otimiza imagem usando GD: redimensiona se necessario e recomprime.
     */
    private function optimizeGd(UploadedFile $file, string $ext): UploadedFile
    {
        $sourcePath = $file->getRealPath();
        $image = $this->loadGd($sourcePath, $ext);

        if ($image === false) {
            // formato nao suportado pelo GD, retorna original sem modificar
            return $file;
        }

        $origW = imagesx($image);
        $origH = imagesy($image);

        [$image, $newW, $newH] = $this->resize($image, $origW, $origH, $ext);

        // se nao redimensionou e e gif, nao recomprime (preserva animacao)
        if ($ext === 'gif' && $newW === $origW && $newH === $origH) {
            imagedestroy($image);
            return $file;
        }

        $outputExt  = in_array($ext, ['png', 'gif'], true) ? $ext : 'jpg';
        $outputMime = $this->mimeFor($outputExt);
        $tmpPath    = $this->tempPath('opt_', $outputExt);

        $saved = $this->saveGd($image, $tmpPath, $outputExt);
        imagedestroy($image);

        if (!$saved) {
            return $file;
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $outputExt;

        return $this->wrapTempFile($tmpPath, $originalName, $outputMime, $outputExt);
    }

    private function loadGd(string $path, string $ext): \GdImage|false
    {
        return match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png'         => @imagecreatefrompng($path),
            'gif'         => @imagecreatefromgif($path),
            'webp'        => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default       => false,
        };
    }

    private function resize(\GdImage $image, int $w, int $h, string $ext): array
    {
        if ($w <= self::MAX_WIDTH && $h <= self::MAX_HEIGHT) {
            return [$image, $w, $h];
        }

        $scale = min(self::MAX_WIDTH / $w, self::MAX_HEIGHT / $h);
        $nw    = max(1, (int) round($w * $scale));
        $nh    = max(1, (int) round($h * $scale));

        $canvas = imagecreatetruecolor($nw, $nh);

        if (in_array($ext, ['png', 'gif', 'webp'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $nw, $nh, $transparent);
        } else {
            $bg = imagecolorallocate($canvas, 255, 255, 255);
            imagefilledrectangle($canvas, 0, 0, $nw, $nh, $bg);
        }

        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($image);

        return [$canvas, $nw, $nh];
    }

    private function saveGd(\GdImage $image, string $path, string $ext): bool
    {
        return match ($ext) {
            'jpg', 'jpeg' => tap($image, fn($r) => imageinterlace($r, true)) && imagejpeg($image, $path, self::JPEG_QUALITY),
            'png'         => imagepng($image, $path, self::PNG_COMPRESSION),
            'gif'         => imagegif($image, $path),
            'webp'        => function_exists('imagewebp') ? imagewebp($image, $path, self::WEBP_QUALITY) : false,
            default       => imagejpeg($image, $path, self::JPEG_QUALITY),
        };
    }

    private function mimeFor(string $ext): string
    {
        return match ($ext) {
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }

    private function tempPath(string $prefix, string $ext): string
    {
        $dir = storage_path('app/tmp-optimizer');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir . DIRECTORY_SEPARATOR . uniqid($prefix, true) . '.' . $ext;
    }

    private function wrapTempFile(string $tmpPath, string $originalName, string $mimeType, string $ext): UploadedFile
    {
        return new UploadedFile(
            $tmpPath,
            $originalName,
            $mimeType,
            null,
            true  // test mode: nao valida via is_uploaded_file()
        );
    }
}
