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
 * Sistema UNN - PresignedUrlService
 *
 * Servico responsavel por gerar URLs assinadas temporarias para
 * arquivos hospedados em S3 (ou compativel S3, como IDrive E2),
 * com TTL configuravel por tipo de arquivo via tabela settings.
 *
 * Mapeamento de extensoes (via settings, com fallback):
 *   - presigned_url_docs_ttl    (default 30  min): pdf, doc, docx, xls, xlsx
 *   - presigned_url_media_ttl   (default 120 min): mp4, webm, mp3, wav
 *   - presigned_url_default_ttl (default 60  min): demais extensoes
 *
 * Logging:
 *   - Canal `security` registra cada geracao com user_id, file_path, expiration_minutes.
 *   - Falhas de S3 sao logadas como error sem expor o file_path original; o
 *     metodo generate() lanca \RuntimeException com mensagem generica.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 3.1, 3.2, 3.4, 3.5, 3.6
 */

namespace App\Services;

use App\Contracts\PresignedUrlInterface;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class PresignedUrlService implements PresignedUrlInterface
{
    /** Extensoes classificadas como documento (TTL curto). */
    public const DOC_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    /** Extensoes classificadas como midia (TTL longo). */
    public const MEDIA_EXTENSIONS = ['mp4', 'webm', 'mp3', 'wav'];

    /** TTLs padrao (em minutos), usados como fallback quando settings indisponiveis. */
    private const DEFAULT_DOCS_TTL = 30;
    private const DEFAULT_MEDIA_TTL = 120;
    private const DEFAULT_FALLBACK_TTL = 60;

    /**
     * Cache de TTLs resolvidos para o request atual.
     *
     * Mantem getExpirationForType() deterministico/puro durante o ciclo de vida
     * do request: a primeira chamada le da tabela settings e armazena o resultado;
     * chamadas subsequentes retornam o valor cacheado sem nova consulta.
     *
     * @var array{docs:int, media:int, default:int}|null
     */
    private ?array $ttlCache = null;

    public function generate(string $filePath, ?int $expirationMinutes = null): string
    {
        $minutes = $expirationMinutes ?? $this->getExpirationForType(
            $this->extractExtension($filePath)
        );

        // Sanity bound: TTL minimo de 1 minuto para evitar URLs ja expiradas.
        $minutes = max(1, (int) $minutes);

        try {
            $url = Storage::disk('s3')->temporaryUrl(
                $filePath,
                Carbon::now()->addMinutes($minutes)
            );

            Log::channel('security')->info('presigned_url.generated', [
                'user_id' => $this->currentUserId(),
                'file_path' => $filePath,
                'expiration_minutes' => $minutes,
            ]);

            return (string) $url;
        } catch (Throwable $e) {
            // Nao expor file_path interno na excecao retornada ao chamador.
            Log::channel('security')->error('presigned_url.failed', [
                'user_id' => $this->currentUserId(),
                'file_path' => $filePath,
                'expiration_minutes' => $minutes,
                'exception' => $e->getMessage(),
            ]);

            throw new RuntimeException('Nao foi possivel gerar a URL temporaria do arquivo solicitado.');
        }
    }

    public function getExpirationForType(string $fileExtension): int
    {
        $extension = strtolower(trim($fileExtension, ". \t\n\r\0\x0B"));

        $map = $this->loadTtlMap();

        if ($extension === '') {
            return $map['default'];
        }

        if (in_array($extension, self::DOC_EXTENSIONS, true)) {
            return $map['docs'];
        }

        if (in_array($extension, self::MEDIA_EXTENSIONS, true)) {
            return $map['media'];
        }

        return $map['default'];
    }

    public function isExpired(string $url): bool
    {
        $query = (string) parse_url($url, PHP_URL_QUERY);
        if ($query === '') {
            // Sem parametros assinados: trata como expirada (URL invalida).
            return true;
        }

        $params = [];
        parse_str($query, $params);

        $amzDate = (string) ($params['X-Amz-Date'] ?? '');
        $amzExpires = (string) ($params['X-Amz-Expires'] ?? '');

        if ($amzDate === '' || $amzExpires === '' || !ctype_digit($amzExpires)) {
            return true;
        }

        // Formato AWS basico ISO 8601: 20060102T150405Z
        $signedAt = \DateTimeImmutable::createFromFormat('Ymd\THis\Z', $amzDate, new \DateTimeZone('UTC'));
        if ($signedAt === false) {
            return true;
        }

        $expiresAt = $signedAt->modify('+' . (int) $amzExpires . ' seconds');
        if ($expiresAt === false) {
            return true;
        }

        return new \DateTimeImmutable('now', new \DateTimeZone('UTC')) >= $expiresAt;
    }

    /**
     * Le os TTLs configurados via settings, cacheando por request.
     *
     * @return array{docs:int, media:int, default:int}
     */
    private function loadTtlMap(): array
    {
        if ($this->ttlCache !== null) {
            return $this->ttlCache;
        }

        $docs = $this->resolveTtl('presigned_url_docs_ttl', self::DEFAULT_DOCS_TTL);
        $media = $this->resolveTtl('presigned_url_media_ttl', self::DEFAULT_MEDIA_TTL);
        $default = $this->resolveTtl('presigned_url_default_ttl', self::DEFAULT_FALLBACK_TTL);

        $this->ttlCache = [
            'docs' => $docs,
            'media' => $media,
            'default' => $default,
        ];

        return $this->ttlCache;
    }

    private function resolveTtl(string $key, int $fallback): int
    {
        try {
            $value = Setting::get($key, $fallback);
        } catch (Throwable $e) {
            $value = $fallback;
        }

        if (is_int($value)) {
            return $value > 0 ? $value : $fallback;
        }

        if (is_string($value) && ctype_digit(trim($value))) {
            $intVal = (int) trim($value);

            return $intVal > 0 ? $intVal : $fallback;
        }

        return $fallback;
    }

    private function extractExtension(string $filePath): string
    {
        $clean = (string) preg_replace('/[?#].*$/', '', $filePath);
        $extension = pathinfo($clean, PATHINFO_EXTENSION);

        return is_string($extension) ? $extension : '';
    }

    private function currentUserId(): ?int
    {
        try {
            $id = auth()->id();

            return $id !== null ? (int) $id : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}
