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
 *
 * Sistema UNN - Unit tests para PresignedUrlService
 *
 * Spec: .kiro/specs/advanced-security-performance (task 5.3)
 *
 * Cobre:
 *   1. generate($filePath) com Storage::fake('s3') - URL nao vazia
 *   2. getExpirationForType($extension) - exemplos doc/media/default
 *   3. isExpired($url) - timestamps passado vs futuro (formato AWS)
 *   4. Erro de S3 indisponivel - excecao generica nao expoe path interno
 *   5. Logging com user_id, file_path, expiration sem credenciais
 *
 * Validates: Requirements 3.1, 3.2, 3.6
 */

namespace Tests\Unit\Services;

use App\Services\PresignedUrlService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PresignedUrlServiceTest extends TestCase
{
    private PresignedUrlService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PresignedUrlService();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    // -----------------------------------------------------------------
    // generate(): URL gerada via Storage::fake('s3') nao deve ser vazia
    // -----------------------------------------------------------------

    public function test_generate_returns_non_empty_url_for_existing_file_on_fake_s3(): void
    {
        Storage::fake('s3');
        Storage::disk('s3')->put('uploads/sample.pdf', 'conteudo-fake');

        Log::shouldReceive('channel')->with('security')->andReturnSelf();
        Log::shouldReceive('info')->andReturnNull();

        $url = $this->service->generate('uploads/sample.pdf');

        $this->assertNotSame('', $url, 'URL gerada nao pode ser vazia.');
        $this->assertStringContainsString('uploads/sample.pdf', $url);
    }

    // -----------------------------------------------------------------
    // getExpirationForType(): exemplos doc / media / default
    // -----------------------------------------------------------------

    public function test_get_expiration_for_doc_extensions_returns_thirty_minutes(): void
    {
        foreach (['pdf', 'doc', 'docx', 'xls', 'xlsx'] as $ext) {
            $this->assertSame(
                30,
                $this->service->getExpirationForType($ext),
                "Extensao de documento '{$ext}' deveria retornar 30 minutos."
            );
        }
    }

    public function test_get_expiration_for_media_extensions_returns_one_hundred_twenty_minutes(): void
    {
        foreach (['mp4', 'webm', 'mp3', 'wav'] as $ext) {
            $this->assertSame(
                120,
                $this->service->getExpirationForType($ext),
                "Extensao de midia '{$ext}' deveria retornar 120 minutos."
            );
        }
    }

    public function test_get_expiration_for_unknown_extension_returns_default_sixty_minutes(): void
    {
        foreach (['txt', 'zip', 'png', 'jpg', 'unknownext', ''] as $ext) {
            $this->assertSame(
                60,
                $this->service->getExpirationForType($ext),
                "Extensao desconhecida '{$ext}' deveria retornar 60 minutos (default)."
            );
        }
    }

    public function test_get_expiration_for_type_is_case_insensitive(): void
    {
        $this->assertSame(30, $this->service->getExpirationForType('PDF'));
        $this->assertSame(120, $this->service->getExpirationForType('Mp4'));
        $this->assertSame(30, $this->service->getExpirationForType('.DOCX'));
    }

    // -----------------------------------------------------------------
    // isExpired(): URL com X-Amz-Date / X-Amz-Expires
    // -----------------------------------------------------------------

    public function test_is_expired_returns_true_for_url_signed_in_the_past(): void
    {
        // Assinada ha 2 horas, expira em 60s -> ja expirou.
        $signedAt = (new \DateTimeImmutable('-2 hours', new \DateTimeZone('UTC')))->format('Ymd\THis\Z');
        $url = "https://bucket.s3.amazonaws.com/file.txt?X-Amz-Date={$signedAt}&X-Amz-Expires=60&X-Amz-Signature=abc";

        $this->assertTrue($this->service->isExpired($url));
    }

    public function test_is_expired_returns_false_for_url_with_future_expiration(): void
    {
        // Assinada agora, expira daqui a 1 hora.
        $signedAt = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Ymd\THis\Z');
        $url = "https://bucket.s3.amazonaws.com/file.txt?X-Amz-Date={$signedAt}&X-Amz-Expires=3600&X-Amz-Signature=abc";

        $this->assertFalse($this->service->isExpired($url));
    }

    public function test_is_expired_returns_true_for_url_without_signature_params(): void
    {
        $this->assertTrue($this->service->isExpired('https://bucket.s3.amazonaws.com/file.txt'));
        $this->assertTrue($this->service->isExpired('https://bucket.s3.amazonaws.com/file.txt?foo=bar'));
    }

    // -----------------------------------------------------------------
    // Erro de S3: excecao generica que NAO expoe path interno
    // -----------------------------------------------------------------

    public function test_generate_throws_generic_exception_without_leaking_internal_path(): void
    {
        $internalPath = 'super-secret/internal/area/passwords.pdf';

        $disk = Mockery::mock();
        $disk->shouldReceive('temporaryUrl')
            ->andThrow(new \RuntimeException('S3 secret credentials at ' . $internalPath));

        Storage::shouldReceive('disk')->with('s3')->andReturn($disk);

        Log::shouldReceive('channel')->with('security')->andReturnSelf();
        Log::shouldReceive('error')->andReturnNull();
        Log::shouldReceive('info')->andReturnNull();

        $caught = null;
        try {
            $this->service->generate($internalPath);
        } catch (RuntimeException $e) {
            $caught = $e;
        }

        $this->assertNotNull($caught, 'Service deveria lancar RuntimeException quando S3 falha.');
        $this->assertStringNotContainsString(
            $internalPath,
            $caught->getMessage(),
            'Mensagem da excecao publica nao pode expor o path interno do arquivo.'
        );
        $this->assertStringNotContainsString(
            'super-secret',
            $caught->getMessage(),
            'Mensagem da excecao publica nao pode vazar fragmento do path interno.'
        );
        $this->assertStringNotContainsString(
            'credentials',
            $caught->getMessage(),
            'Mensagem da excecao publica nao pode vazar termos sensiveis do erro original.'
        );
    }

    // -----------------------------------------------------------------
    // Log: contem user_id, file_path, expiration_minutes; sem credenciais
    // -----------------------------------------------------------------

    public function test_generate_logs_user_id_file_path_and_expiration_without_credentials(): void
    {
        Storage::fake('s3');
        Storage::disk('s3')->put('docs/relatorio.pdf', 'conteudo-teste');

        $captured = [];

        Log::shouldReceive('channel')
            ->with('security')
            ->andReturnSelf();
        Log::shouldReceive('info')
            ->andReturnUsing(function (string $message, array $context = []) use (&$captured): void {
                $captured[] = ['message' => $message, 'context' => $context];
            });

        $this->service->generate('docs/relatorio.pdf');

        $this->assertNotEmpty($captured, 'Geracao deveria ter produzido pelo menos uma entrada de log.');

        $entry = $captured[0];
        $this->assertSame('presigned_url.generated', $entry['message']);
        $this->assertArrayHasKey('user_id', $entry['context']);
        $this->assertArrayHasKey('file_path', $entry['context']);
        $this->assertArrayHasKey('expiration_minutes', $entry['context']);

        $this->assertSame('docs/relatorio.pdf', $entry['context']['file_path']);
        $this->assertIsInt($entry['context']['expiration_minutes']);
        $this->assertGreaterThanOrEqual(1, $entry['context']['expiration_minutes']);

        // Nenhuma credencial pode aparecer no contexto do log.
        $forbidden = ['secret', 'access_key', 'aws_secret', 'password', 'token', 'X-Amz-Signature'];
        $serialized = json_encode($entry['context']) ?: '';
        foreach ($forbidden as $needle) {
            $this->assertStringNotContainsStringIgnoringCase(
                $needle,
                $serialized,
                "Log de geracao nao pode conter '{$needle}'."
            );
        }
    }

    public function test_generate_logs_error_without_leaking_credentials_when_s3_fails(): void
    {
        $disk = Mockery::mock();
        $disk->shouldReceive('temporaryUrl')
            ->andThrow(new \RuntimeException('AWS Signature failure (debug only).'));

        Storage::shouldReceive('disk')->with('s3')->andReturn($disk);

        $captured = [];

        Log::shouldReceive('channel')->with('security')->andReturnSelf();
        Log::shouldReceive('error')
            ->andReturnUsing(function (string $message, array $context = []) use (&$captured): void {
                $captured[] = ['message' => $message, 'context' => $context];
            });
        Log::shouldReceive('info')->andReturnNull();

        try {
            $this->service->generate('docs/relatorio.pdf');
        } catch (RuntimeException $e) {
            // esperado
        }

        $this->assertNotEmpty($captured, 'Falha deveria ter sido logada.');
        $errorEntry = $captured[0];
        $this->assertSame('presigned_url.failed', $errorEntry['message']);

        $serialized = json_encode($errorEntry['context']) ?: '';
        foreach (['access_key', 'secret_key', 'aws_secret', 'password', 'X-Amz-Signature'] as $needle) {
            $this->assertStringNotContainsStringIgnoringCase(
                $needle,
                $serialized,
                "Log de erro nao pode conter '{$needle}'."
            );
        }
    }
}
