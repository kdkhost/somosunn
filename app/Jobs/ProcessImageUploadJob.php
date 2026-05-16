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

namespace App\Jobs;

use App\Contracts\ImageProcessorInterface;
use App\Models\Setting;
use App\Support\UploadStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Job de pos-processamento de imagens grandes (>= 2MB) na queue 'uploads'.
 *
 * Disparado por UploadStorage::storeUploadedFile() apos armazenar o arquivo
 * original. O job opera sobre o arquivo ja armazenado (via path relativo)
 * e gera variantes derivadas (WebP, thumbnails) e otimiza o original
 * in-place removendo metadados EXIF.
 *
 * Estrategia fail-safe:
 *   - Em caso de qualquer falha, registra no canal 'stack' e NAO faz throw
 *   - O arquivo original permanece preservado mesmo em falha catastrofica
 *   - Cada operacao do processor ja e independentemente fail-safe
 *
 * Spec: advanced-security-performance, Requirements 2.1, 2.7, 1.2-1.5
 */
class ProcessImageUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Queue dedicada (alinhada com QueueManagerService::QUEUE_UPLOADS).
     */
    public string $queue = 'uploads';

    /**
     * Numero maximo de tentativas em caso de falha de infraestrutura.
     */
    public int $tries = 3;

    /**
     * Timeout em segundos.
     */
    public int $timeout = 120;

    /**
     * Backoff progressivo entre tentativas (segundos).
     *
     * @var array<int,int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * @param string $relativePath Path relativo do arquivo ja armazenado (ex: 'uploads/posts/img_xxx.jpg')
     * @param string $directory    Diretorio de origem (informativo, usado em logs)
     * @param array  $options      Opcoes de processamento (generate_webp, generate_thumbnails, etc.)
     */
    public function __construct(
        public string $relativePath,
        public string $directory = '',
        public array $options = []
    ) {
    }

    /**
     * Executa o pos-processamento da imagem ja armazenada.
     *
     * Pipeline:
     *   1. Resolve o path absoluto local (ou baixa do S3 para temp)
     *   2. Strip EXIF + otimiza original in-place
     *   3. Gera variante WebP
     *   4. Gera thumbnails (thumb/medium/large)
     *
     * Em caso de falha, loga e preserva o original sem propagar excecao.
     */
    public function handle(ImageProcessorInterface $processor): void
    {
        try {
            $absolutePath = $this->resolveAbsolutePath($this->relativePath);

            if ($absolutePath === null || !is_file($absolutePath)) {
                Log::channel('stack')->warning('ProcessImageUploadJob: arquivo de origem nao encontrado, ignorando.', [
                    'relative_path' => $this->relativePath,
                    'directory' => $this->directory,
                ]);

                return;
            }

            // 1. Strip EXIF e otimizacao in-place (recria a imagem com GD).
            try {
                $processor->stripExif($absolutePath);
            } catch (Throwable $e) {
                Log::channel('stack')->warning('ProcessImageUploadJob: stripExif falhou, prosseguindo.', [
                    'relative_path' => $this->relativePath,
                    'exception' => $e->getMessage(),
                ]);
            }

            try {
                $processor->optimize($absolutePath, $this->options);
            } catch (Throwable $e) {
                Log::channel('stack')->warning('ProcessImageUploadJob: optimize falhou, prosseguindo.', [
                    'relative_path' => $this->relativePath,
                    'exception' => $e->getMessage(),
                ]);
            }

            // 2. Gera variante WebP (se a extensao nao for ja webp).
            $extension = strtolower((string) pathinfo($absolutePath, PATHINFO_EXTENSION));
            if ($extension !== 'webp' && ($this->options['generate_webp'] ?? true)) {
                try {
                    $quality = (int) ($this->options['webp_quality']
                        ?? Setting::get('image_webp_quality', 85));
                    $processor->convertToWebP($absolutePath, max(1, min(100, $quality)));
                } catch (Throwable $e) {
                    Log::channel('stack')->warning('ProcessImageUploadJob: convertToWebP falhou.', [
                        'relative_path' => $this->relativePath,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }

            // 3. Gera thumbnails.
            if ($this->options['generate_thumbnails'] ?? true) {
                try {
                    $sizes = $this->resolveThumbSizes();
                    $processor->generateThumbnails($absolutePath, $sizes);
                } catch (Throwable $e) {
                    Log::channel('stack')->warning('ProcessImageUploadJob: generateThumbnails falhou.', [
                        'relative_path' => $this->relativePath,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }
        } catch (Throwable $e) {
            // Fail-safe global: loga mas nao faz throw, preservando o original.
            Log::channel('stack')->error('ProcessImageUploadJob: falha no pos-processamento, original preservado.', [
                'relative_path' => $this->relativePath,
                'directory' => $this->directory,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Hook do Laravel chamado quando todas as tentativas falham.
     * Garante log no canal 'stack' sem propagar excecao para o supervisor.
     */
    public function failed(?Throwable $exception): void
    {
        Log::channel('stack')->error('ProcessImageUploadJob: esgotadas tentativas, original preservado.', [
            'relative_path' => $this->relativePath,
            'directory' => $this->directory,
            'exception' => $exception?->getMessage(),
        ]);
    }

    /**
     * Resolve o path absoluto local para o arquivo armazenado.
     *
     * Suporta apenas storage local (publico). Quando o disco efetivo e S3,
     * o pos-processamento in-place nao e suportado nesta versao e o job
     * apenas registra a situacao no log (original preservado no S3).
     */
    private function resolveAbsolutePath(string $relativePath): ?string
    {
        $relative = ltrim(str_replace('\\', '/', $relativePath), '/');
        if ($relative === '') {
            return null;
        }

        if (!UploadStorage::isLocal()) {
            Log::channel('stack')->info('ProcessImageUploadJob: storage nao-local, pos-processamento sera ignorado.', [
                'relative_path' => $relative,
                'effective_disk' => UploadStorage::effectiveDisk(),
            ]);

            return null;
        }

        $root = (string) config(
            'filesystems.disks.public.root',
            is_dir(public_path('storage')) ? public_path('storage') : storage_path('app/public')
        );

        $absolute = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relative);

        if (is_file($absolute)) {
            return $absolute;
        }

        // Fallbacks para layouts legados (public/storage/... ou public/uploads/...).
        $fallbacks = [
            public_path($relative),
            public_path('storage/' . $relative),
        ];

        foreach ($fallbacks as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Resolve a configuracao de tamanhos de thumbnail a partir de options ou Setting.
     *
     * @return array<string,int>
     */
    private function resolveThumbSizes(): array
    {
        $default = ['thumb' => 150, 'medium' => 600, 'large' => 1200];

        $override = $this->options['thumb_sizes'] ?? null;
        if (is_array($override) && $override !== []) {
            return $this->sanitizeSizes($override, $default);
        }

        try {
            $raw = Setting::get('image_thumb_sizes', null);
        } catch (Throwable $e) {
            return $default;
        }

        if (is_array($raw) && $raw !== []) {
            return $this->sanitizeSizes($raw, $default);
        }

        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && $decoded !== []) {
                return $this->sanitizeSizes($decoded, $default);
            }
        }

        return $default;
    }

    /**
     * Sanitiza um mapa label => max dimensao (px).
     *
     * @param array<mixed,mixed>  $sizes
     * @param array<string,int>   $default
     *
     * @return array<string,int>
     */
    private function sanitizeSizes(array $sizes, array $default): array
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

        return $sanitized !== [] ? $sanitized : $default;
    }
}
