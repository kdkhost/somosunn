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

namespace Tests\Unit;

use App\Services\ImageProcessorService;
use App\Support\ImageProcessResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Unit tests para App\Services\ImageProcessorService.
 *
 * Cobre:
 *   1. process() com imagem real pequena → ImageProcessResult valido
 *   2. convertToWebP() → arquivo .webp com header RIFF/WEBP
 *   3. generateThumbnails() → cria thumbs nos tamanhos configurados
 *   4. stripExif() → metadata EXIF removido (best-effort, GD nao preserva metadados)
 *   5. Fallback em input invalido → preserva original sem corromper
 *   6. Respeito a max_resolution → nao ultrapassa bounding box mesmo com input maior
 *
 * Testes que dependem da extensao GD sao automaticamente skipados se a
 * extensao nao estiver disponivel, mantendo compatibilidade com hospedagem
 * compartilhada cPanel/LiteSpeed onde GD pode estar desabilitada.
 *
 * Validates: Requirements 2.1, 2.2, 2.4, 2.7
 */
class ImageProcessorServiceTest extends TestCase
{
    private string $diskRoot;
    private string $uploadsTestDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Diretorio dedicado para fixtures temporarias - isolado por execucao.
        $this->uploadsTestDir = storage_path('framework' . DIRECTORY_SEPARATOR . 'testing' . DIRECTORY_SEPARATOR . 'uploads-test');
        if (!is_dir($this->uploadsTestDir) && !@mkdir($this->uploadsTestDir, 0755, true) && !is_dir($this->uploadsTestDir)) {
            $this->fail('Nao foi possivel criar diretorio de fixtures: ' . $this->uploadsTestDir);
        }

        // Sandbox para o disco "public" durante o teste.
        $this->diskRoot = $this->uploadsTestDir . DIRECTORY_SEPARATOR . 'public-disk';
        if (!is_dir($this->diskRoot) && !@mkdir($this->diskRoot, 0755, true) && !is_dir($this->diskRoot)) {
            $this->fail('Nao foi possivel criar diretorio de disk: ' . $this->diskRoot);
        }

        // Garante que o servico operara em modo "local" e em uma raiz isolada.
        Config::set('filesystems.disks.public.root', $this->diskRoot);
        Config::set('filesystems.disks.public.url', 'http://localhost/storage');
        Config::set('uploads.effective_disk', 'public');
    }

    protected function tearDown(): void
    {
        // Cleanup de fixtures e diretorios temporarios usados durante o teste.
        $this->removeDirectory($this->uploadsTestDir);
        $this->removeDirectory(storage_path('app' . DIRECTORY_SEPARATOR . 'tmp-image-processor'));

        parent::tearDown();
    }

    // -----------------------------------------------------------------
    // Tests
    // -----------------------------------------------------------------

    public function test_process_returns_valid_image_process_result_for_small_image(): void
    {
        $this->skipIfNoGd();

        $file = $this->makeJpegUploadedFile(300, 200, 'test-input.jpg');
        $service = new ImageProcessorService();

        $result = $service->process($file, 'unit-test/process', [
            'generate_thumbnails' => true,
            'generate_webp' => false,
            'max_resolution' => 2048,
        ]);

        $this->assertInstanceOf(ImageProcessResult::class, $result);
        $this->assertNotSame('', $result->originalPath, 'originalPath nao deveria estar vazio.');
        $this->assertTrue(
            is_file($this->diskRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $result->originalPath)),
            'O arquivo principal nao foi gravado no disco public.'
        );
        $this->assertGreaterThan(0, $result->originalSize);
        $this->assertGreaterThan(0, $result->processedSize);
        $this->assertFalse($result->wasResized, 'Imagem 300x200 nao deveria ser redimensionada para max=2048.');
        $this->assertNotEmpty($result->thumbnails, 'Thumbnails deveriam ter sido geradas.');
    }

    public function test_convert_to_webp_creates_webp_file_with_riff_header(): void
    {
        $this->skipIfNoGd();
        if (!function_exists('imagewebp')) {
            $this->markTestSkipped('Funcao imagewebp() nao disponivel nesta build do GD.');
        }

        $sourcePath = $this->createJpegFixtureOnDisk(150, 150, 'webp-input.jpg');

        $service = new ImageProcessorService();
        $destination = $service->convertToWebP($sourcePath, 80);

        $this->assertNotNull($destination, 'convertToWebP deveria retornar um caminho de destino.');
        $this->assertSame('webp', strtolower((string) pathinfo($destination, PATHINFO_EXTENSION)));
        $this->assertFileExists($destination);

        // Header WebP: bytes 0-3 = "RIFF", bytes 8-11 = "WEBP".
        $header = (string) @file_get_contents($destination, false, null, 0, 12);
        $this->assertSame('RIFF', substr($header, 0, 4), 'Arquivo WebP deve comecar com magic RIFF.');
        $this->assertSame('WEBP', substr($header, 8, 4), 'Arquivo WebP deve conter assinatura WEBP no offset 8.');
    }

    public function test_generate_thumbnails_creates_files_for_each_configured_size(): void
    {
        $this->skipIfNoGd();

        $sourcePath = $this->createJpegFixtureOnDisk(800, 600, 'thumb-input.jpg');
        $sizes = ['thumb' => 150, 'medium' => 300, 'large' => 600];

        $service = new ImageProcessorService();
        $thumbnails = $service->generateThumbnails($sourcePath, $sizes);

        $this->assertSame(['thumb', 'medium', 'large'], array_keys($thumbnails));

        foreach ($thumbnails as $label => $path) {
            $this->assertFileExists($path, "Thumbnail '{$label}' nao foi criado em '{$path}'.");

            $info = @getimagesize($path);
            $this->assertIsArray($info, "Thumbnail '{$label}' nao retornou metadados validos.");
            $this->assertLessThanOrEqual($sizes[$label], (int) $info[0], "Largura de '{$label}' excede o limite configurado.");
            $this->assertLessThanOrEqual($sizes[$label], (int) $info[1], "Altura de '{$label}' excede o limite configurado.");
        }
    }

    public function test_strip_exif_returns_true_and_keeps_image_readable(): void
    {
        $this->skipIfNoGd();

        $sourcePath = $this->createJpegFixtureOnDisk(120, 90, 'exif-input.jpg');
        $sizeBefore = (int) @filesize($sourcePath);
        $this->assertGreaterThan(0, $sizeBefore);

        $service = new ImageProcessorService();
        $result = $service->stripExif($sourcePath);

        $this->assertTrue($result, 'stripExif deveria retornar true para imagem valida.');
        $this->assertFileExists($sourcePath, 'Arquivo deve continuar existindo apos strip.');

        // Imagem permanece legivel pelo GD.
        $info = @getimagesize($sourcePath);
        $this->assertIsArray($info, 'Imagem deve continuar decodificavel apos strip.');

        // Quando exif_read_data esta disponivel, garante que nao ha metadados sensiveis remanescentes.
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourcePath);
            if (is_array($exif)) {
                foreach (['GPSLatitude', 'GPSLongitude', 'Make', 'Model', 'DateTimeOriginal'] as $sensitive) {
                    $this->assertArrayNotHasKey(
                        $sensitive,
                        $exif,
                        "Metadado sensivel '{$sensitive}' nao deveria estar presente apos stripExif()."
                    );
                }
            }
        }
    }

    public function test_strip_exif_returns_false_for_missing_file(): void
    {
        $service = new ImageProcessorService();
        $result = $service->stripExif($this->uploadsTestDir . DIRECTORY_SEPARATOR . 'does-not-exist.jpg');
        $this->assertFalse($result);
    }

    public function test_process_falls_back_to_original_when_input_is_corrupt(): void
    {
        // Este teste nao depende de GD: ele exercita justamente o caminho de
        // fallback quando o decoder GD recusa a imagem.
        $file = UploadedFile::fake()->create('corrupt.jpg', 8, 'image/jpeg'); // bytes aleatorios em formato invalido
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
            'Em fallback processedSize deve ser igual a originalSize (arquivo nao corrompido).'
        );
    }

    public function test_process_respects_max_resolution_for_oversized_input(): void
    {
        $this->skipIfNoGd();

        // Imagem 5000x4000 deve ser reduzida para caber em 4096x4096.
        $file = $this->makeJpegUploadedFile(5000, 4000, 'oversized.jpg');
        $service = new ImageProcessorService();

        $result = $service->process($file, 'unit-test/max-res', [
            'generate_thumbnails' => false,
            'generate_webp' => false,
            'max_resolution' => 4096,
        ]);

        $this->assertInstanceOf(ImageProcessResult::class, $result);
        $this->assertTrue($result->wasResized, 'wasResized deveria ser true para imagem 5000x4000 com max=4096.');

        $absolutePath = $this->diskRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $result->originalPath);
        $this->assertFileExists($absolutePath);

        $info = @getimagesize($absolutePath);
        $this->assertIsArray($info);
        $this->assertLessThanOrEqual(4096, (int) $info[0], 'Largura processada deve respeitar max_resolution.');
        $this->assertLessThanOrEqual(4096, (int) $info[1], 'Altura processada deve respeitar max_resolution.');
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
     * Cria um arquivo JPEG fixture no diretorio de testes e retorna seu path absoluto.
     */
    private function createJpegFixtureOnDisk(int $width, int $height, string $filename): string
    {
        $this->skipIfNoGd();

        $absolutePath = $this->uploadsTestDir . DIRECTORY_SEPARATOR . $filename;

        $image = imagecreatetruecolor($width, $height);
        if (!$image) {
            $this->fail('Nao foi possivel criar imagem GD para fixture.');
        }

        // Preenche com cor solida + alguns pixels aleatorios para evitar JPEG vazio.
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
     */
    private function makeJpegUploadedFile(int $width, int $height, string $clientName): UploadedFile
    {
        $absolutePath = $this->createJpegFixtureOnDisk($width, $height, 'upload-' . uniqid('', true) . '.jpg');

        // test=true para impedir que o move() do framework valide o arquivo como
        // upload HTTP real, permitindo que o service o processe normalmente.
        return new UploadedFile($absolutePath, $clientName, 'image/jpeg', null, true);
    }

    /**
     * Remove um diretorio recursivamente, ignorando erros pontuais.
     */
    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        // File::deleteDirectory() lida com Windows e simbolismos sem nos preocuparmos.
        File::deleteDirectory($directory);
    }
}
