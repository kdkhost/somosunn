<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 */

namespace App\Services;

use App\Contracts\ImageProcessorInterface;
use App\Models\Setting;
use App\Support\ImageProcessResult;
use App\Support\UploadStorage;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Servico de processamento de imagens usando GD library nativa do PHP.
 *
 * NAO depende de Intervention Image. Usa apenas funcoes image* do PHP, no
 * mesmo padrao do WatermarkService existente, garantindo compatibilidade
 * total com hospedagem compartilhada cPanel/LiteSpeed.
 *
 * Pipeline de process():
 *   1. Carrega imagem com GD (a partir do UploadedFile)
 *   2. Redimensiona se exceder image_max_resolution (preservando aspect ratio)
 *   3. Strip EXIF (implicito ao recriar imagem com GD)
 *   4. Salva original otimizado no diretorio destino
 *   5. Gera thumbnails (thumb/medium/large) conforme image_thumb_sizes
 *   6. Gera variante WebP do principal
 *
 * Em caso de falha em qualquer etapa: loga erro e preserva o arquivo
 * original via UploadStorage::storeUploadedFile() sem modificacao.
 */
class ImageProcessorService implements ImageProcessorInterface
{
    /** Extensoes raster suportadas pelo GD */
    private const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** Defaults de configuracao caso a tabela settings esteja indisponivel */
    private const DEFAULT_MAX_RESOLUTION = 2048;
    private const DEFAULT_JPEG_QUALITY = 80;
    private const DEFAULT_WEBP_QUALITY = 85;
    private const DEFAULT_THUMB_SIZES = [
        'thumb' => 150,
        'medium' => 600,
        'large' => 1200,
    ];

    /**
     * {@inheritdoc}
     */
    public function process(UploadedFile $file, string $directory, array $options = []): ImageProcessResult
    {
        $directory = $this->normalizeDirectory($directory);
        $originalSize = (int) ($file->getSize() ?: 0);
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg'));

        // Se nao for um formato raster suportado, apenas armazena sem processar.
        if (!$this->isSupportedExtension($extension)) {
            $storedPath = UploadStorage::storeUploadedFile($file, $directory, null, [
                'watermark' => false,
                'process_image' => false,
            ]);

            return new ImageProcessResult(
                originalPath: $storedPath,
                webpPath: null,
                thumbnails: [],
                originalSize: $originalSize,
                processedSize: $originalSize,
                wasResized: false,
            );
        }

        $settings = $this->resolveSettings($options);
        $tempDir = $this->ensureTempDirectory();
        $tempOriginal = $tempDir . DIRECTORY_SEPARATOR . uniqid('img_', true) . '.' . $extension;
        $tempThumbnails = [];
        $tempWebp = null;
        $wasResized = false;

        try {
            // 1. Carrega imagem com GD (strip EXIF e implicito - GD nao preserva metadados).
            [$image, $width, $height, $imageType] = $this->loadImage($file->getRealPath());

            // 2. Redimensiona se exceder max resolution.
            $maxResolution = (int) $settings['max_resolution'];
            [$newWidth, $newHeight] = self::calculateResizeDimensions($width, $height, $maxResolution, $maxResolution);

            if ($newWidth !== $width || $newHeight !== $height) {
                $resized = $this->createResizedCanvas($image, $width, $height, $newWidth, $newHeight, $extension);
                imagedestroy($image);
                $image = $resized;
                $width = $newWidth;
                $height = $newHeight;
                $wasResized = true;
            }

            // 3. Salva original otimizado em arquivo temporario.
            if (!$this->saveImage($image, $tempOriginal, $extension, $settings)) {
                throw new \RuntimeException('Falha ao salvar imagem otimizada.');
            }

            // 4. Gera thumbnails se habilitado.
            $generateThumbnails = (bool) ($options['generate_thumbnails'] ?? true);
            if ($generateThumbnails) {
                $tempThumbnails = $this->generateThumbnailsFromImage(
                    $image,
                    $width,
                    $height,
                    $extension,
                    $settings['thumb_sizes'],
                    $settings,
                    $tempDir
                );
            }

            // 5. Gera variante WebP se habilitado e a extensao nao for ja webp.
            $generateWebp = (bool) ($options['generate_webp'] ?? true);
            if ($generateWebp && $extension !== 'webp' && function_exists('imagewebp')) {
                $tempWebp = $tempDir . DIRECTORY_SEPARATOR . uniqid('img_webp_', true) . '.webp';
                if (!@imagewebp($image, $tempWebp, (int) $settings['webp_quality'])) {
                    @unlink($tempWebp);
                    $tempWebp = null;
                }
            }

            imagedestroy($image);
            $image = null;

            // 6. Move arquivos temporarios para o destino final via UploadStorage/Storage.
            $originalFilename = $this->buildFilename($options['filename'] ?? null, $extension, 'image');
            $originalPath = $this->moveTempToFinal($tempOriginal, $directory, $originalFilename);
            $tempOriginal = null;

            $thumbnailPaths = [];
            foreach ($tempThumbnails as $label => $tempThumbPath) {
                $thumbFilename = $this->buildThumbnailFilename($originalFilename, (string) $label, $extension);
                $thumbnailPaths[$label] = $this->moveTempToFinal($tempThumbPath, $directory, $thumbFilename);
            }
            $tempThumbnails = [];

            $webpPath = null;
            if ($tempWebp !== null) {
                $webpFilename = $this->buildWebpFilename($originalFilename);
                $webpPath = $this->moveTempToFinal($tempWebp, $directory, $webpFilename);
                $tempWebp = null;
            }

            $processedSize = $this->resolveStoredSize($originalPath);

            return new ImageProcessResult(
                originalPath: $originalPath,
                webpPath: $webpPath,
                thumbnails: $thumbnailPaths,
                originalSize: $originalSize,
                processedSize: $processedSize,
                wasResized: $wasResized,
            );
        } catch (Throwable $exception) {
            // Cleanup de qualquer recurso temporario aberto.
            if (isset($image) && ($image instanceof GdImage)) {
                @imagedestroy($image);
            }
            $this->cleanupTempFiles($tempOriginal, $tempThumbnails, $tempWebp);

            Log::error('ImageProcessorService: falha ao processar imagem, preservando original.', [
                'exception' => $exception->getMessage(),
                'directory' => $directory,
                'extension' => $extension,
                'original_size' => $originalSize,
            ]);

            // Fallback fail-safe: armazena o original sem modificacao.
            try {
                $storedPath = UploadStorage::storeUploadedFile($file, $directory, null, [
                    'watermark' => false,
                    'process_image' => false,
                ]);
            } catch (Throwable $storeException) {
                Log::error('ImageProcessorService: fallback de armazenamento tambem falhou.', [
                    'exception' => $storeException->getMessage(),
                ]);

                return new ImageProcessResult(
                    originalPath: '',
                    webpPath: null,
                    thumbnails: [],
                    originalSize: $originalSize,
                    processedSize: $originalSize,
                    wasResized: false,
                );
            }

            return new ImageProcessResult(
                originalPath: $storedPath,
                webpPath: null,
                thumbnails: [],
                originalSize: $originalSize,
                processedSize: $originalSize,
                wasResized: false,
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateThumbnails(string $sourcePath, array $sizes): array
    {
        if (!is_file($sourcePath)) {
            return [];
        }

        try {
            [$image, $width, $height] = $this->loadImage($sourcePath);
            $extension = strtolower((string) pathinfo($sourcePath, PATHINFO_EXTENSION));
            $settings = $this->resolveSettings([]);
            $tempDir = $this->ensureTempDirectory();

            $thumbnails = $this->generateThumbnailsFromImage(
                $image,
                $width,
                $height,
                $extension,
                $sizes,
                $settings,
                $tempDir
            );

            imagedestroy($image);

            // Move thumbnails para o mesmo diretorio do source.
            $sourceDir = dirname($sourcePath);
            $sourceBase = pathinfo($sourcePath, PATHINFO_FILENAME);
            $finalThumbnails = [];

            foreach ($thumbnails as $label => $tempPath) {
                $finalPath = $sourceDir . DIRECTORY_SEPARATOR . $sourceBase . '_' . $label . '.' . $extension;
                if (@rename($tempPath, $finalPath) || (@copy($tempPath, $finalPath) && @unlink($tempPath))) {
                    $finalThumbnails[$label] = $finalPath;
                } else {
                    @unlink($tempPath);
                }
            }

            return $finalThumbnails;
        } catch (Throwable $exception) {
            Log::error('ImageProcessorService: falha ao gerar thumbnails.', [
                'exception' => $exception->getMessage(),
                'source' => $sourcePath,
            ]);

            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function convertToWebP(string $sourcePath, int $quality = 85): ?string
    {
        if (!is_file($sourcePath) || !function_exists('imagewebp')) {
            return null;
        }

        try {
            [$image] = $this->loadImage($sourcePath);
            $quality = max(1, min(100, $quality));
            $destination = preg_replace('/\.[^.]+$/', '', $sourcePath) . '.webp';

            $saved = @imagewebp($image, $destination, $quality);
            imagedestroy($image);

            return ($saved && is_file($destination)) ? $destination : null;
        } catch (Throwable $exception) {
            Log::error('ImageProcessorService: falha ao converter para WebP.', [
                'exception' => $exception->getMessage(),
                'source' => $sourcePath,
            ]);

            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stripExif(string $sourcePath): bool
    {
        if (!is_file($sourcePath)) {
            return false;
        }

        try {
            [$image, , , $imageType] = $this->loadImage($sourcePath);
            $extension = $this->extensionFromImageType($imageType, (string) pathinfo($sourcePath, PATHINFO_EXTENSION));
            $settings = $this->resolveSettings([]);

            $tempPath = $sourcePath . '.stripped.tmp';
            $saved = $this->saveImage($image, $tempPath, $extension, $settings);
            imagedestroy($image);

            if (!$saved || !is_file($tempPath)) {
                @unlink($tempPath);

                return false;
            }

            if (!@rename($tempPath, $sourcePath)) {
                if (!@copy($tempPath, $sourcePath)) {
                    @unlink($tempPath);

                    return false;
                }
                @unlink($tempPath);
            }

            return true;
        } catch (Throwable $exception) {
            Log::error('ImageProcessorService: falha ao remover EXIF.', [
                'exception' => $exception->getMessage(),
                'source' => $sourcePath,
            ]);

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function optimize(string $sourcePath, array $options = []): string
    {
        if (!is_file($sourcePath)) {
            return $sourcePath;
        }

        try {
            [$image, , , $imageType] = $this->loadImage($sourcePath);
            $extension = $this->extensionFromImageType($imageType, (string) pathinfo($sourcePath, PATHINFO_EXTENSION));
            $settings = $this->resolveSettings($options);

            $tempPath = $sourcePath . '.opt.tmp';
            $saved = $this->saveImage($image, $tempPath, $extension, $settings);
            imagedestroy($image);

            if (!$saved || !is_file($tempPath)) {
                @unlink($tempPath);

                return $sourcePath;
            }

            if (!@rename($tempPath, $sourcePath)) {
                if (!@copy($tempPath, $sourcePath)) {
                    @unlink($tempPath);

                    return $sourcePath;
                }
                @unlink($tempPath);
            }

            return $sourcePath;
        } catch (Throwable $exception) {
            Log::error('ImageProcessorService: falha ao otimizar imagem.', [
                'exception' => $exception->getMessage(),
                'source' => $sourcePath,
            ]);

            return $sourcePath;
        }
    }

    /**
     * Calcula novas dimensoes para caber em uma bounding box, preservando aspect ratio.
     *
     * Metodo PURO e DETERMINISTICO (sem side effects), projetado para ser
     * verificado por property-based testing (Property 1: Aspect Ratio Preservation).
     *
     * Regras:
     *   - Se a imagem ja cabe em (maxW, maxH), retorna as dimensoes originais.
     *   - Caso contrario, escala proporcionalmente pelo menor fator que faz caber na box.
     *   - Dimensoes de saida sao garantidas >= 1 e <= max correspondente.
     *
     * @param int $w    largura original (>= 1)
     * @param int $h    altura original (>= 1)
     * @param int $maxW largura maxima da bounding box (>= 1)
     * @param int $maxH altura maxima da bounding box (>= 1)
     *
     * @return array{0:int,1:int} [novaLargura, novaAltura]
     */
    public static function calculateResizeDimensions(int $w, int $h, int $maxW, int $maxH): array
    {
        $w = max(1, $w);
        $h = max(1, $h);
        $maxW = max(1, $maxW);
        $maxH = max(1, $maxH);

        if ($w <= $maxW && $h <= $maxH) {
            return [$w, $h];
        }

        $scale = min($maxW / $w, $maxH / $h);

        $newW = (int) max(1, floor($w * $scale));
        $newH = (int) max(1, floor($h * $scale));

        // Garantir que nao excede a bounding box (floor pode produzir um valor 1px maior em casos limite).
        if ($newW > $maxW) {
            $newW = $maxW;
        }
        if ($newH > $maxH) {
            $newH = $maxH;
        }

        return [$newW, $newH];
    }

    // -----------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------

    /**
     * Resolve as configuracoes finais a partir de Setting + overrides em $options.
     *
     * @return array{max_resolution:int,jpeg_quality:int,webp_quality:int,png_compression:int,thumb_sizes:array<string,int>}
     */
    private function resolveSettings(array $options): array
    {
        $maxResolution = (int) ($options['max_resolution']
            ?? Setting::get('image_max_resolution', self::DEFAULT_MAX_RESOLUTION));
        $maxResolution = max(64, min(8000, $maxResolution));

        $jpegQuality = (int) ($options['jpeg_quality']
            ?? Setting::get('image_jpeg_quality', self::DEFAULT_JPEG_QUALITY));
        $jpegQuality = max(1, min(100, $jpegQuality));

        $webpQuality = (int) ($options['webp_quality']
            ?? Setting::get('image_webp_quality', self::DEFAULT_WEBP_QUALITY));
        $webpQuality = max(1, min(100, $webpQuality));

        $pngCompression = (int) ($options['png_compression']
            ?? Setting::get('image_png_compression', 7));
        $pngCompression = max(0, min(9, $pngCompression));

        $thumbSizes = $this->resolveThumbSizes($options['thumb_sizes'] ?? null);

        return [
            'max_resolution' => $maxResolution,
            'jpeg_quality' => $jpegQuality,
            'webp_quality' => $webpQuality,
            'png_compression' => $pngCompression,
            'thumb_sizes' => $thumbSizes,
        ];
    }

    /**
     * Resolve a configuracao de tamanhos de thumbnail.
     *
     * @param array<string,int>|null $override
     *
     * @return array<string,int>
     */
    private function resolveThumbSizes(?array $override): array
    {
        if (is_array($override) && $override !== []) {
            return $this->sanitizeThumbSizes($override);
        }

        $raw = Setting::get('image_thumb_sizes', null);

        if (is_array($raw)) {
            return $this->sanitizeThumbSizes($raw);
        }

        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && $decoded !== []) {
                return $this->sanitizeThumbSizes($decoded);
            }
        }

        return self::DEFAULT_THUMB_SIZES;
    }

    /**
     * Sanitiza um mapa de tamanhos de thumbnail (label => max dimensao em px).
     *
     * @param array<mixed,mixed> $sizes
     *
     * @return array<string,int>
     */
    private function sanitizeThumbSizes(array $sizes): array
    {
        $sanitized = [];

        foreach ($sizes as $label => $value) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }
            $px = (int) $value;
            if ($px < 16) {
                continue;
            }
            $sanitized[$label] = min(8000, $px);
        }

        return $sanitized !== [] ? $sanitized : self::DEFAULT_THUMB_SIZES;
    }

    /**
     * Carrega uma imagem do disco usando o decoder GD apropriado.
     *
     * @return array{0:GdImage,1:int,2:int,3:int} [recurso GD, width, height, imagetype]
     */
    private function loadImage(string $absolutePath): array
    {
        if (!is_file($absolutePath)) {
            throw new \RuntimeException('Arquivo de imagem nao encontrado: ' . $absolutePath);
        }

        $info = @getimagesize($absolutePath);
        if (!is_array($info) || !isset($info[0], $info[1], $info[2])) {
            throw new \RuntimeException('Metadados da imagem ilegiveis.');
        }

        $imageType = (int) $info[2];
        $image = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($absolutePath),
            IMAGETYPE_PNG => @imagecreatefrompng($absolutePath),
            IMAGETYPE_GIF => @imagecreatefromgif($absolutePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
            default => false,
        };

        if (!($image instanceof GdImage)) {
            throw new \RuntimeException('Formato de imagem nao suportado pelo GD.');
        }

        if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_WEBP) {
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        return [$image, (int) $info[0], (int) $info[1], $imageType];
    }

    /**
     * Cria um canvas com a imagem redimensionada via imagecopyresampled.
     */
    private function createResizedCanvas(
        GdImage $image,
        int $sourceWidth,
        int $sourceHeight,
        int $targetWidth,
        int $targetHeight,
        string $extension
    ): GdImage {
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

        imagecopyresampled(
            $canvas,
            $image,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        return $canvas;
    }

    /**
     * Salva uma imagem GD em disco no formato indicado pela extensao.
     */
    private function saveImage(GdImage $image, string $destinationPath, string $extension, array $settings): bool
    {
        $directory = dirname($destinationPath);
        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            return false;
        }

        $extension = strtolower(trim($extension));

        return match ($extension) {
            'jpg', 'jpeg' => @imagejpeg($image, $destinationPath, (int) $settings['jpeg_quality']),
            'png' => @imagepng($image, $destinationPath, (int) $settings['png_compression']),
            'gif' => @imagegif($image, $destinationPath),
            'webp' => function_exists('imagewebp')
                ? @imagewebp($image, $destinationPath, (int) $settings['webp_quality'])
                : @imagejpeg($image, $destinationPath, (int) $settings['jpeg_quality']),
            default => @imagejpeg($image, $destinationPath, (int) $settings['jpeg_quality']),
        };
    }

    /**
     * Gera thumbnails a partir de um recurso GD ja carregado em memoria.
     *
     * @param array<string,int> $sizes
     *
     * @return array<string,string> Mapa label => path absoluto do thumbnail temporario
     */
    private function generateThumbnailsFromImage(
        GdImage $image,
        int $width,
        int $height,
        string $extension,
        array $sizes,
        array $settings,
        string $tempDir
    ): array {
        $sanitized = $this->sanitizeThumbSizes($sizes);
        $thumbnails = [];

        foreach ($sanitized as $label => $maxPx) {
            try {
                [$thumbW, $thumbH] = self::calculateResizeDimensions($width, $height, $maxPx, $maxPx);
                $thumbCanvas = $this->createResizedCanvas($image, $width, $height, $thumbW, $thumbH, $extension);
                $thumbPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('img_thumb_' . $label . '_', true) . '.' . $extension;

                if ($this->saveImage($thumbCanvas, $thumbPath, $extension, $settings)) {
                    $thumbnails[$label] = $thumbPath;
                } else {
                    @unlink($thumbPath);
                }

                imagedestroy($thumbCanvas);
            } catch (Throwable $exception) {
                Log::warning('ImageProcessorService: falha ao gerar thumbnail individual.', [
                    'exception' => $exception->getMessage(),
                    'label' => $label,
                ]);
            }
        }

        return $thumbnails;
    }

    /**
     * Move um arquivo temporario para o destino final usando UploadStorage.
     *
     * Suporta tanto disco local quanto S3 (via Storage::disk('public')).
     */
    private function moveTempToFinal(string $tempPath, string $directory, string $filename): string
    {
        $relativePath = ltrim(($directory !== '' ? $directory . '/' : '') . $filename, '/');

        if (UploadStorage::isLocal()) {
            $root = (string) config(
                'filesystems.disks.public.root',
                is_dir(public_path('storage')) ? public_path('storage') : storage_path('app/public')
            );

            $targetDir = $directory !== ''
                ? rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory)
                : rtrim($root, DIRECTORY_SEPARATOR);

            if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new \RuntimeException('Nao foi possivel preparar o diretorio de destino: ' . $targetDir);
            }

            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

            if (!@rename($tempPath, $targetPath)) {
                if (!@copy($tempPath, $targetPath)) {
                    throw new \RuntimeException('Falha ao mover imagem para o destino final.');
                }
                @unlink($tempPath);
            }

            return $relativePath;
        }

        $stream = fopen($tempPath, 'rb');
        if ($stream === false) {
            throw new \RuntimeException('Falha ao abrir imagem temporaria para upload.');
        }

        try {
            Storage::disk('public')->put($relativePath, $stream, ['visibility' => 'public']);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            @unlink($tempPath);
        }

        return $relativePath;
    }

    /**
     * Constroi o nome final do arquivo principal.
     */
    private function buildFilename(?string $custom, string $extension, string $prefix): string
    {
        $custom = trim((string) $custom);
        if ($custom !== '') {
            $base = pathinfo($custom, PATHINFO_FILENAME);

            return $base . '.' . $extension;
        }

        return trim($prefix, '-_ ') . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    }

    /**
     * Constroi o nome de um thumbnail a partir do nome do original.
     */
    private function buildThumbnailFilename(string $originalFilename, string $label, string $extension): string
    {
        $base = pathinfo($originalFilename, PATHINFO_FILENAME);

        return $base . '_' . $label . '.' . $extension;
    }

    /**
     * Constroi o nome da variante WebP a partir do nome do original.
     */
    private function buildWebpFilename(string $originalFilename): string
    {
        $base = pathinfo($originalFilename, PATHINFO_FILENAME);

        return $base . '.webp';
    }

    /**
     * Resolve o tamanho real do arquivo armazenado no disco ativo.
     */
    private function resolveStoredSize(string $relativePath): int
    {
        try {
            if (UploadStorage::isLocal()) {
                $root = (string) config(
                    'filesystems.disks.public.root',
                    is_dir(public_path('storage')) ? public_path('storage') : storage_path('app/public')
                );
                $absolute = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                    . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/'));

                return is_file($absolute) ? (int) @filesize($absolute) : 0;
            }

            return (int) (Storage::disk('public')->size($relativePath) ?: 0);
        } catch (Throwable $exception) {
            return 0;
        }
    }

    /**
     * Garante que o diretorio temporario para processamento existe.
     */
    private function ensureTempDirectory(): string
    {
        $directory = storage_path('app/tmp-image-processor');

        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Nao foi possivel preparar o diretorio temporario de processamento.');
        }

        return $directory;
    }

    /**
     * Remove arquivos temporarios criados durante o pipeline.
     *
     * @param array<string,string> $thumbnails
     */
    private function cleanupTempFiles(?string $original, array $thumbnails, ?string $webp): void
    {
        if ($original !== null && is_file($original)) {
            @unlink($original);
        }

        foreach ($thumbnails as $path) {
            if (is_string($path) && is_file($path)) {
                @unlink($path);
            }
        }

        if ($webp !== null && is_file($webp)) {
            @unlink($webp);
        }
    }

    /**
     * Mapeia IMAGETYPE_* para extensao de arquivo.
     */
    private function extensionFromImageType(int $imageType, string $fallback): string
    {
        return match ($imageType) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_WEBP => 'webp',
            default => strtolower($fallback) ?: 'jpg',
        };
    }

    /**
     * Verifica se a extensao informada e suportada pelo GD.
     */
    private function isSupportedExtension(string $extension): bool
    {
        return in_array(strtolower(trim($extension)), self::SUPPORTED_EXTENSIONS, true);
    }

    /**
     * Normaliza um path de diretorio (separadores e trailing slash).
     */
    private function normalizeDirectory(string $directory): string
    {
        return trim(str_replace('\\', '/', $directory), '/');
    }
}
