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

namespace Tests\Unit\Services;

use App\Services\ImageProcessorService;
use App\Support\ImageProcessResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Unit tests focados nas operacoes individuais do ImageProcessorService.
 *
 * Diferente de Tests\Unit\ImageProcessorServiceTest (que cobre o pipeline
 * completo via process()), este arquivo valida cada metodo publico do
 * contrato ImageProcessorInterface de forma isolada:
 *
 *   - convertToWebP():     gera arquivo .webp valido (header RIFF/WEBP)
 *   - generateThumbnails(): cria thumbnails com dimensoes <= bounding box
 *   - stripExif():         remove metadados sensiveis (best-effort com GD)
 *   - optimize():          aplica qualidade configuravel reduzindo tamanho
 *   - fallback:            metodos retornam gracefully quando o input e invalido
 *
 * As fixtures de imagem sao geradas dinamicamente com GD (imagecreatetruecolor +
 * imagejpeg) em diretorio temporario, evitando dependencia de arquivos binarios
 * versionados. Todos os artefatos sao removidos no tearDown().
 *
 * Em ambientes sem extensao GD (ou sem suporte a webp), os testes que dependem
 * do decoder sao skipados, mantendo o suite verde em hospedagens onde a
 * extensao nao esta disponivel.
 *
 * Validates: Requirements 2.1, 2.2, 2.4, 2.7
 */
class ImageProcessorServiceTest extends TestCase
{
    /** Diretorio raiz para fixtures e disco "public" simulado. */
    private string $sandboxDir;

    /** Raiz do disco "public" durante o teste. */
    private string $diskRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sandboxDir = storage_path(
            'framework' . DIRECTORY_SEPARATOR . 'testing' . DIRECTORY_SEPARATOR . 'image-processor-unit'
        );
        $this->diskRoot = $this->sandboxDir . DIRECTORY_SEPARATOR . 'public-disk';

        foreach ([$this->sandboxDir, $this->diskRoot] as $dir) {
            if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
                $this->fail('Nao foi possivel criar diretorio de fixtures: ' . $dir);
            }
        }

        // Aponta o disco public para sandbox isolado. Mesmo que o service
        // resolva caminhos via UploadStorage, o teste opera longe de
        // storage/app/public real.
        Config::set('filesystems.disks.public.root', $this->diskRoot);
        Config::set('filesystems.disks.public.url', 'http://localhost/storage');
        Config::set('uploads.effective_disk', 'public');
    }

    protected function tearDown(): void
    {
        // Cleanup de fixtures e do diretorio temporario do servico.
        $this->removeDirectory($this->sandboxDir);
        $this->removeDirectory(storage_path('app' . DIRECTORY_SEPARATOR . 'tmp-image-processor'));

        parent::tearDown();
    }

    // -----------------------------------------------------------------
    // convertToWebP()
    // -----------------------------------------------------------------

    public function test_convert_to_webp_creates_valid_webp_file_from_jpeg(): void
    {
        $this->skipIfNoGd();
        if (!function_exists('imagewebp')) {
            $this->markTestSkipped('Funcao imagewebp() nao disponivel nesta build do GD.');
        }

        $sourcePath = $this->createJpegFixture(160, 120, 'webp-source.jpg');

        $service = new ImageProcessorService();
        $destination = $service->convertToWebP($sourcePath, 80);

        $this->assertNotNull($destination, 'convertToWebP deveria retornar um caminho.');
        $this->assertFileExists($destination);
        $this->assertSame(
            'webp',
            strtolower((string) pathinfo($destination, PATHINFO_EXTENSION)),
            'Arquivo resultante deve ter extensao .webp.'
        );

        // Header WebP: bytes 0-3 = 'RIFF', bytes 8-11 = 'WEBP'.
        $header = (string) @file_get_contents($destination, false, null, 0, 12);
        $this->assertSame('RIFF', substr($header, 0, 4), 'WebP deve iniciar com magic RIFF.');
        $this->assertSame('WEBP', substr($header, 8, 4), 'WebP deve conter assinatura WEBP no offset 8.');

        // Mime detectavel (quando finfo/mime_content_type disponivel).
        if (function_exists('mime_content_type')) {
            $mime = (string) @mime_content_type($destination);
            $this->assertSame('image/webp', $mime, 'mime_content_type deveria reportar image/webp.');
        }
    }

    public function test_convert_to_webp_clamps_quality_within_valid_range(): void
    {
        $this->skipIfNoGd();
        if (!function_exists('imagewebp')) {
            $this->markTestSkipped('Funcao imagewebp() nao disponivel nesta build do GD.');
        }

        $sourcePath = $this->createJpegFixture(120, 80, 'webp-quality.jpg');

        $service = new ImageProcessorService();

        // Qualidade fora dos limites deve ser clampada internamente, sem throw.
        $high = $service->convertToWebP($sourcePath, 9999);
        $low = $service->convertToWebP($sourcePath, -50);

        $this->assertNotNull($high, 'convertToWebP deve clampar quality > 100 e ainda gerar arquivo.');
        $this->assertNotNull($low, 'convertToWebP deve clampar quality < 1 e ainda gerar arquivo.');
        $this->assertFileExists((string) $high);
        $this->assertFileExists((string) $low);
    }

    public function test_convert_to_webp_returns_null_for_missing_file(): void
    {
        $service = new ImageProcessorService();
        $result = $service->convertToWebP($this->sandboxDir . DIRECTORY_SEPARATOR . 'nope.jpg');

        $this->assertNull($result, 'Caminho inexistente deve retornar null sem throw.');
    }

    public function test_convert_to_webp_returns_null_for_corrupt_file(): void
    {
        $this->skipIfNoGd();

        $corruptPath = $this->sandboxDir . DIRECTORY_SEPARATOR . 'corrupt.jpg';
        file_put_contents($corruptPath, 'this-is-not-an-image');

        $service = new ImageProcessorService();
        $result = $service->convertToWebP($corruptPath);

        $this->assertNull($result, 'Arquivo corrompido nao deve gerar WebP nem propagar exception.');
    }

    // -----------------------------------------------------------------
    // generateThumbnails()
    // -----------------------------------------------------------------

    public function test_generate_thumbnails_produces_files_within_bounding_box(): void
    {
        $this->skipIfNoGd();

        $sourcePath = $this->createJpegFixture(800, 600, 'thumb-source.jpg');
        $sizes = ['thumb' => 150, 'medium' => 300, 'large' => 600];

        $service = new ImageProcessorService();
        $thumbs = $service->generateThumbnails($sourcePath, $sizes);

        $this->assertSame(['thumb', 'medium', 'large'], array_keys($thumbs));

        foreach ($thumbs as $label => $path) {
            $this->assertFileExists($path, "Thumbnail '{$label}' nao foi gerado.");

            $info = @getimagesize($path);
            $this->assertIsArray($info, "Thumbnail '{$label}' nao retornou getimagesize valido.");

            $this->assertLessThanOrEqual(
                $sizes[$label],
                (int) $info[0],
                "Largura do thumbnail '{$label}' excede o limite configurado."
            );
            $this->assertLessThanOrEqual(
                $sizes[$label],
                (int) $info[1],
                "Altura do thumbnail '{$label}' excede o limite configurado."
            );
        }
    }

    public function test_generate_thumbnails_preserves_aspect_ratio(): void
    {
        $this->skipIfNoGd();

        // 1000x500 -> ratio 2:1. Para cada thumbnail, esperamos o mesmo ratio.
        $sourcePath = $this->createJpegFixture(1000, 500, 'thumb-aspect.jpg');
        $service = new ImageProcessorService();

        $thumbs = $service->generateThumbnails($sourcePath, ['thumb' => 200, 'medium' => 400]);

        foreach ($thumbs as $label => $path) {
            $info = @getimagesize($path);
            $this->assertIsArray($info);
            $w = (int) $info[0];
            $h = (int) $info[1];
            $this->assertGreaterThan(0, $w);
            $this->assertGreaterThan(0, $h);

            // Tolerancia de 1px (floor pode arredondar a altura para baixo).
            $expectedHeight = (int) round($w / 2);
            $this->assertLessThanOrEqual(
                1,
                abs($h - $expectedHeight),
                "Aspect ratio do thumbnail '{$label}' divergiu (esperado ~{$expectedHeight}, obtido {$h})."
            );
        }
    }

    public function test_generate_thumbnails_returns_empty_array_for_missing_source(): void
    {
        $service = new ImageProcessorService();
        $result = $service->generateThumbnails(
            $this->sandboxDir . DIRECTORY_SEPARATOR . 'absent.jpg',
            ['thumb' => 100]
        );

        $this->assertSame([], $result);
    }

    public function test_generate_thumbnails_falls_back_to_defaults_for_invalid_size_map(): void
    {
        $this->skipIfNoGd();

        $sourcePath = $this->createJpegFixture(400, 400, 'thumb-defaults.jpg');
        $service = new ImageProcessorService();

        // Sizes vazias e invalidas devem disparar os defaults internos.
        $thumbs = $service->generateThumbnails($sourcePath, []);

        $this->assertNotEmpty(
            $thumbs,
            'Mapa vazio deveria acionar fallback para tamanhos default (thumb/medium/large).'
        );
        foreach ($thumbs as $path) {
            $this->assertFileExists($path);
        }
    }

    // -----------------------------------------------------------------
    // stripExif()
    // -----------------------------------------------------------------

    public function test_strip_exif_returns_true_and_keeps_image_decodable(): void
    {
        $this->skipIfNoGd();

        $sourcePath = $this->createJpegFixture(120, 90, 'exif-source.jpg');
        $sizeBefore = (int) @filesize($sourcePath);
        $this->assertGreaterThan(0, $sizeBefore);

        $service = new ImageProcessorService();
        $result = $service->stripExif($sourcePath);

        $this->assertTrue($result, 'stripExif deveria retornar true para imagem valida.');
        $this->assertFileExists($sourcePath, 'Arquivo deve persistir apos strip.');

        $info = @getimagesize($sourcePath);
        $this->assertIsArray($info, 'Imagem deve continuar decodificavel apos strip.');

        // Quando exif_read_data esta disponivel, garante ausencia de metadados sensiveis.
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourcePath);
            if (is_array($exif)) {
                foreach (['GPSLatitude', 'GPSLongitude', 'Make', 'Model', 'DateTimeOriginal'] as $tag) {
                    $this->assertArrayNotHasKey(
                        $tag,
                        $exif,
                        "Metadado sensivel '{$tag}' nao deveria estar presente apos stripExif()."
                    );
                }
            }
        }
    }

    public function test_strip_exif_returns_false_for_missing_file(): void
    {
        $service = new ImageProcessorService();
        $result = $service->stripExif($this->sandboxDir . DIRECTORY_SEPARATOR . 'missing.jpg');

        $this->assertFalse($result);
    }

    public function test_strip_exif_returns_false_for_corrupt_file(): void
    {
        $corruptPath = $this->sandboxDir . DIRECTORY_SEPARATOR . 'exif-corrupt.jpg';
        file_put_contents($corruptPath, "\x00\x01\x02\x03not-a-jpeg");

        $service = new ImageProcessorService();
        $result = $service->stripExif($corruptPath);

        $this->assertFalse($result, 'Arquivo corrompido deve retornar false sem throw fatal.');
    }

    // -----------------------------------------------------------------
    // optimize()
    // -----------------------------------------------------------------

    public function test_optimize_keeps_image_readable_with_configurable_quality(): void
    {
        $this->skipIfNoGd();

        $sourcePath = $this->createJpegFixture(400, 300, 'opt-source.jpg');
        $this->assertFileExists($sourcePath);

        $service = new ImageProcessorService();
        $resultPath = $service->optimize($sourcePath, ['jpeg_quality' => 30]);

        $this->assertSame($sourcePath, $resultPath, 'optimize() deve operar in-place e retornar o mesmo path.');
        $this->assertFileExists($resultPath);

        $info = @getimagesize($resultPath);
        $this->assertIsArray($info, 'Imagem otimizada deve continuar decodificavel.');
        $this->assertSame(400, (int) $info[0]);
        $this->assertSame(300, (int) $info[1]);
    }

    public function test_optimize_with_lower_quality_does_not_increase_file_size(): void
    {
        $this->skipIfNoGd();

        // Imagem com gradiente para ter dados reais comprimieis.
        $sourcePath = $this->createGradientJpeg(600, 400, 'opt-gradient.jpg');
        $sizeBefore = (int) @filesize($sourcePath);
        $this->assertGreaterThan(0, $sizeBefore);

        $service = new ImageProcessorService();
        $service->optimize($sourcePath, ['jpeg_quality' => 25]);

        clearstatcache(true, $sourcePath);
        $sizeAfter = (int) @filesize($sourcePath);

        $this->assertGreaterThan(0, $sizeAfter, 'Arquivo otimizado deve continuar valido.');
        $this->assertLessThanOrEqual(
            $sizeBefore,
            $sizeAfter,
            'Otimizacao com qualidade mais baixa nao deveria aumentar o tamanho do arquivo.'
        );
    }

    public function test_optimize_returns_original_path_for_missing_file(): void
    {
        $service = new ImageProcessorService();
        $missing = $this->sandboxDir . DIRECTORY_SEPARATOR . 'opt-missing.jpg';

        $result = $service->optimize($missing);

        $this->assertSame($missing, $result, 'Caminho inexistente deve retornar o input original sem throw.');
        $this->assertFileDoesNotExist($result, 'optimize() nao deve criar arquivo a partir de path inexistente.');
    }

    public function test_optimize_returns_original_path_for_corrupt_file(): void
    {
        $corruptPath = $this->sandboxDir . DIRECTORY_SEPARATOR . 'opt-corrupt.jpg';
        file_put_contents($corruptPath, 'not-a-real-image-content');

        $service = new ImageProcessorService();
        $result = $service->optimize($corruptPath);

        $this->assertSame($corruptPath, $result, 'Arquivo corrompido deve retornar mesmo path (fail-safe).');
        // Conteudo original deve ser preservado em fallback.
        $this->assertSame('not-a-real-image-content', (string) @file_get_contents($corruptPath));
    }

    // -----------------------------------------------------------------
    // Fallback (Requirement 2.7)
    // -----------------------------------------------------------------

    public function test_process_falls_back_to_original_when_input_is_corrupt(): void
    {
        // Garante que process() nao falha de forma fatal quando o decoder GD
        // recusa o input e o pipeline cai no caminho de fallback fail-safe.
        $file = UploadedFile::fake()->create('fallback.jpg', 8, 'image/jpeg');

        $service = new ImageProcessorService();
        $result = $service->process($file, 'unit-test/fallback', [
            'generate_thumbnails' => false,
            'generate_webp' => false,
        ]);

        $this->assertInstanceOf(ImageProcessResult::class, $result);
        $this->assertNotSame('', $result->originalPath, 'Fallback deve preservar o arquivo original com path nao vazio.');
        $this->assertNull($result->webpPath, 'Fallback nao deve gerar variante WebP.');
        $this->assertSame([], $result->thumbnails, 'Fallback nao deve gerar thumbnails.');
        $this->assertFalse($result->wasResized, 'Fallback nao deve marcar wasResized.');
        $this->assertSame(
            $result->originalSize,
            $result->processedSize,
            'Em fallback processedSize deve igual originalSize (arquivo nao corrompido pelo service).'
        );
    }

    // -----------------------------------------------------------------
    // Max resolution (downscale only, never upscale) - Requirement 2.3, 2.5
    // -----------------------------------------------------------------

    public function test_process_downscales_image_that_exceeds_max_resolution(): void
    {
        $this->skipIfNoGd();

        // 5000x4000 com max=4096 -> deve reduzir para caber na bounding box.
        $file = $this->makeJpegUploadedFile(5000, 4000, 'oversized.jpg');
        $service = new ImageProcessorService();

        $result = $service->process($file, 'unit-test/max-res', [
            'generate_thumbnails' => false,
            'generate_webp' => false,
            'max_resolution' => 4096,
        ]);

        $this->assertInstanceOf(ImageProcessResult::class, $result);
        $this->assertTrue(
            $result->wasResized,
            'wasResized deveria ser true para imagem 5000x4000 com max=4096.'
        );

        $absolutePath = $this->diskAbsolutePath($result->originalPath);
        $this->assertFileExists($absolutePath);

        $info = @getimagesize($absolutePath);
        $this->assertIsArray($info);
        $this->assertLessThanOrEqual(4096, (int) $info[0], 'Largura processada deve respeitar max_resolution.');
        $this->assertLessThanOrEqual(4096, (int) $info[1], 'Altura processada deve respeitar max_resolution.');
    }

    public function test_process_does_not_upscale_image_smaller_than_max_resolution(): void
    {
        $this->skipIfNoGd();

        // 400x300 com max=2048 -> deve permanecer 400x300 (sem upscale).
        $file = $this->makeJpegUploadedFile(400, 300, 'small.jpg');
        $service = new ImageProcessorService();

        $result = $service->process($file, 'unit-test/no-upscale', [
            'generate_thumbnails' => false,
            'generate_webp' => false,
            'max_resolution' => 2048,
        ]);

        $this->assertInstanceOf(ImageProcessResult::class, $result);
        $this->assertFalse(
            $result->wasResized,
            'wasResized deveria ser false quando a imagem ja cabe em max_resolution.'
        );

        $absolutePath = $this->diskAbsolutePath($result->originalPath);
        $this->assertFileExists($absolutePath);

        $info = @getimagesize($absolutePath);
        $this->assertIsArray($info);
        $this->assertSame(400, (int) $info[0], 'Largura nao deve sofrer upscale.');
        $this->assertSame(300, (int) $info[1], 'Altura nao deve sofrer upscale.');
    }

    // -----------------------------------------------------------------
    // calculateResizeDimensions() - metodo estatico puro
    // -----------------------------------------------------------------

    public function test_calculate_resize_dimensions_returns_input_when_within_bounding_box(): void
    {
        // Caso 1: imagem ja cabe na bounding box -> retorna sem alteracoes.
        $this->assertSame([400, 300], ImageProcessorService::calculateResizeDimensions(400, 300, 2048, 2048));
        $this->assertSame([100, 100], ImageProcessorService::calculateResizeDimensions(100, 100, 100, 100));
        $this->assertSame([1, 1], ImageProcessorService::calculateResizeDimensions(1, 1, 10, 10));
    }

    public function test_calculate_resize_dimensions_scales_proportionally_to_fit_box(): void
    {
        // Caso 2: 4000x2000 (ratio 2:1) com box 2000x2000 -> 2000x1000.
        $this->assertSame(
            [2000, 1000],
            ImageProcessorService::calculateResizeDimensions(4000, 2000, 2000, 2000)
        );

        // Caso 3: 1000x4000 (ratio 1:4) com box 1000x1000 -> 250x1000 (largura limita).
        $this->assertSame(
            [250, 1000],
            ImageProcessorService::calculateResizeDimensions(1000, 4000, 1000, 1000)
        );

        // Caso 4: 5000x4000 com box 4096x4096 -> ratio min = 4096/5000 = 0.8192.
        // Esperado: floor(5000 * 0.8192) = 4096, floor(4000 * 0.8192) = 3276.
        $result = ImageProcessorService::calculateResizeDimensions(5000, 4000, 4096, 4096);
        $this->assertSame(4096, $result[0]);
        $this->assertSame(3276, $result[1]);
    }

    public function test_calculate_resize_dimensions_clamps_invalid_inputs_to_minimum(): void
    {
        // Inputs <= 0 sao saneados para 1, garantindo retorno valido.
        $this->assertSame([1, 1], ImageProcessorService::calculateResizeDimensions(0, 0, 0, 0));
        $this->assertSame([1, 1], ImageProcessorService::calculateResizeDimensions(-10, -5, 100, 100));
    }

    public function test_calculate_resize_dimensions_never_exceeds_bounding_box(): void
    {
        // Property pontual: para uma serie de inputs, saida deve respeitar a box e ter dimensoes >= 1.
        $cases = [
            [10000, 10000, 800, 600],
            [3840, 2160, 1920, 1080],
            [1, 9999, 100, 100],
            [9999, 1, 100, 100],
            [2049, 2049, 2048, 2048],
        ];

        foreach ($cases as [$w, $h, $maxW, $maxH]) {
            [$newW, $newH] = ImageProcessorService::calculateResizeDimensions($w, $h, $maxW, $maxH);

            $this->assertGreaterThanOrEqual(1, $newW, "newW deve ser >= 1 para input ({$w}, {$h}).");
            $this->assertGreaterThanOrEqual(1, $newH, "newH deve ser >= 1 para input ({$w}, {$h}).");
            $this->assertLessThanOrEqual($maxW, $newW, "newW deve ser <= maxW para input ({$w}, {$h}).");
            $this->assertLessThanOrEqual($maxH, $newH, "newH deve ser <= maxH para input ({$w}, {$h}).");
        }
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    private function skipIfNoGd(): void
    {
        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')) {
            $this->markTestSkipped('Extensao GD (com suporte a JPEG) nao disponivel neste ambiente.');
        }
    }

    /**
     * Cria um arquivo JPEG fixture solido com area de contraste e retorna seu path absoluto.
     */
    private function createJpegFixture(int $width, int $height, string $filename): string
    {
        $absolutePath = $this->sandboxDir . DIRECTORY_SEPARATOR . $filename;

        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            $this->fail('Nao foi possivel criar recurso GD para fixture.');
        }

        $background = imagecolorallocate($image, 30, 80, 200);
        imagefilledrectangle($image, 0, 0, $width, $height, $background);
        $accent = imagecolorallocate($image, 240, 200, 30);
        imagefilledrectangle(
            $image,
            (int) ($width * 0.25),
            (int) ($height * 0.25),
            (int) ($width * 0.75),
            (int) ($height * 0.75),
            $accent
        );

        if (!@imagejpeg($image, $absolutePath, 85)) {
            imagedestroy($image);
            $this->fail('Falha ao gravar fixture JPEG em: ' . $absolutePath);
        }
        imagedestroy($image);

        return $absolutePath;
    }

    /**
     * Cria um UploadedFile real apontando para um JPEG gerado dinamicamente.
     *
     * O flag test=true do UploadedFile e usado para evitar que o framework
     * interprete o arquivo como upload HTTP real durante move(), permitindo
     * que o service o processe normalmente em ambiente de teste.
     */
    private function makeJpegUploadedFile(int $width, int $height, string $clientName): UploadedFile
    {
        $absolutePath = $this->createJpegFixture($width, $height, 'upload-' . uniqid('', true) . '.jpg');

        return new UploadedFile($absolutePath, $clientName, 'image/jpeg', null, true);
    }

    /**
     * Resolve o caminho absoluto de um arquivo armazenado no disco "public" sandboxed.
     */
    private function diskAbsolutePath(string $relativePath): string
    {
        return $this->diskRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    /**
     * Cria um JPEG fixture com gradiente, util para testar compressao real.
     */
    private function createGradientJpeg(int $width, int $height, string $filename): string
    {
        $absolutePath = $this->sandboxDir . DIRECTORY_SEPARATOR . $filename;

        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            $this->fail('Nao foi possivel criar recurso GD para gradiente.');
        }

        for ($x = 0; $x < $width; $x++) {
            $r = (int) (($x / max(1, $width)) * 255);
            for ($y = 0; $y < $height; $y++) {
                $g = (int) (($y / max(1, $height)) * 255);
                $b = (int) ((($x + $y) / max(1, $width + $height)) * 255);
                $color = imagecolorallocate($image, $r, $g, $b);
                imagesetpixel($image, $x, $y, (int) $color);
            }
        }

        if (!@imagejpeg($image, $absolutePath, 95)) {
            imagedestroy($image);
            $this->fail('Falha ao gravar gradiente JPEG em: ' . $absolutePath);
        }
        imagedestroy($image);

        return $absolutePath;
    }

    /**
     * Remove um diretorio recursivamente, ignorando erros pontuais.
     */
    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        File::deleteDirectory($directory);
    }
}
