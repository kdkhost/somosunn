<?php

namespace App\Support;

use App\Jobs\ProcessImageUploadJob;
use App\Services\ImageProcessorService;
use App\Services\WatermarkService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class UploadStorage
{
    /** TTL do cache de localizacao S3 (em segundos = 7 dias) */
    private const S3_LOCATION_CACHE_TTL = 604800;

    /**
     * Limiar (bytes) acima do qual o pos-processamento de imagem e
     * delegado a um job assincrono na queue 'uploads'. Abaixo disso, o
     * processamento e sincrono dentro do request.
     *
     * Spec: advanced-security-performance, Requirements 2.1, 2.7
     */
    private const ASYNC_PROCESS_THRESHOLD_BYTES = 2097152; // 2 MiB

    /** Extensoes raster consideradas "imagens processaveis" */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public static function applyRuntimeConfig(array $settings = []): void
    {
        $localConfig = self::localPublicDiskConfig();

        // Configurar discos locais sempre (compatibilidade legacy)
        config([
            'filesystems.disks.public' => $localConfig,
            'filesystems.disks.uploads' => $localConfig,
            'filesystems.disks.local_public' => $localConfig,
        ]);

        // Tentar ler configuracoes do banco com fallback seguro
        $dbSettings = self::readStorageSettingsFromDb();
        $merged = array_merge($dbSettings, $settings);

        // Resolver disco selecionado: db -> env -> public
        $selectedDisk = $merged['storage_driver'] ?? env('FILESYSTEM_DISK', 'public');
        $selectedDisk = in_array($selectedDisk, ['s3', 'public'], true) ? $selectedDisk : 'public';

        // Se S3 selecionado, aplicar credenciais (multi-provider -> legacy -> env)
        // Spec: multi-provider-s3-storage (Req 1.4, 7.4, 7.6)
        $effectiveDisk = $selectedDisk;
        if ($selectedDisk === 's3') {
            // 1) Tentar resolver via StorageProviderRegistry (multi-provider).
            //    Se o provedor ativo possui credenciais validas, prevalece.
            $providerConfig = self::resolveActiveProviderConfig();

            if ($providerConfig !== null) {
                config([
                    'filesystems.disks.s3.key' => $providerConfig['key'],
                    'filesystems.disks.s3.secret' => $providerConfig['secret'],
                    'filesystems.disks.s3.region' => $providerConfig['region'],
                    'filesystems.disks.s3.bucket' => $providerConfig['bucket'],
                    'filesystems.disks.s3.url' => $providerConfig['url'],
                    'filesystems.disks.s3.endpoint' => $providerConfig['endpoint'],
                    'filesystems.disks.s3.use_path_style_endpoint' => $providerConfig['use_path_style_endpoint'],
                ]);

                $s3Key = $providerConfig['key'];
                $s3Secret = $providerConfig['secret'];
                $s3Bucket = $providerConfig['bucket'];
            } else {
                // 2) Fallback: schema legado `storage_*` (compat retroativa - Req 7.6).
                $s3Key = $merged['storage_access_key'] ?? env('AWS_ACCESS_KEY_ID');
                $s3Secret = $merged['storage_secret_key'] ?? env('AWS_SECRET_ACCESS_KEY');
                $s3Bucket = $merged['storage_bucket'] ?? env('AWS_BUCKET');

                config([
                    'filesystems.disks.s3.key' => $s3Key,
                    'filesystems.disks.s3.secret' => $s3Secret,
                    'filesystems.disks.s3.region' => $merged['storage_region'] ?? env('AWS_DEFAULT_REGION', 'us-east-1'),
                    'filesystems.disks.s3.bucket' => $s3Bucket,
                    'filesystems.disks.s3.url' => $merged['storage_url'] ?? env('AWS_URL'),
                    'filesystems.disks.s3.endpoint' => $merged['storage_endpoint'] ?? env('AWS_ENDPOINT'),
                    'filesystems.disks.s3.use_path_style_endpoint' => (bool) ($merged['storage_path_style'] ?? env('AWS_USE_PATH_STYLE_ENDPOINT', true)),
                ]);
            }

            // Fallback final: se credenciais incompletas, voltar ao public
            if (empty($s3Key) || empty($s3Secret) || empty($s3Bucket)) {
                $effectiveDisk = 'public';
            }
        }

        config([
            'uploads.disk' => $effectiveDisk,
            'uploads.selected_disk' => $selectedDisk,
            'uploads.effective_disk' => $effectiveDisk,
            'filesystems.cloud' => $effectiveDisk,
        ]);

        self::forgetDisks();
    }

    /**
     * Resolve o disco do provedor ativo via StorageProviderRegistry.
     *
     * Retorna null nos seguintes casos (e o caller cai no schema legado):
     *   - Registry indisponivel (boot inicial sem DB)
     *   - Provedor ativo == 'local'
     *   - Provedor ativo sem credenciais validas (Req 1.4)
     *
     * Em caso de qualquer excecao (DB indisponivel, classe inexistente),
     * retorna null silenciosamente para preservar o comportamento legado.
     *
     * @return array{
     *     key: string,
     *     secret: string,
     *     region: string,
     *     bucket: string,
     *     url: ?string,
     *     endpoint: ?string,
     *     use_path_style_endpoint: bool
     * }|null
     */
    private static function resolveActiveProviderConfig(): ?array
    {
        try {
            if (!class_exists(\App\Support\StorageProviderRegistry::class)) {
                return null;
            }

            /** @var \App\Support\StorageProviderRegistry $registry */
            $registry = app(\App\Support\StorageProviderRegistry::class);
            $active = $registry->activeProvider();

            // Provedor ativo == local: nao usa S3 (Req 2.3 - tratado no caller).
            if ($active === \App\Support\StorageProviderRegistry::PROVIDER_LOCAL) {
                return null;
            }

            // Req 1.4: creds incompletas -> fallback (caller decide qual).
            if (!$registry->isConfigured($active)) {
                return null;
            }

            $disk = $registry->diskConfigArray($active);

            return [
                'key' => (string) $disk['key'],
                'secret' => (string) $disk['secret'],
                'region' => (string) $disk['region'],
                'bucket' => (string) $disk['bucket'],
                'url' => $disk['url'] ?? null,
                'endpoint' => $disk['endpoint'] ?? null,
                'use_path_style_endpoint' => (bool) ($disk['use_path_style_endpoint'] ?? true),
            ];
        } catch (\Throwable $e) {
            // Boot resiliente: nunca falhar por causa do registry.
            return null;
        }
    }

    /**
     * Le configuracoes de armazenamento do banco com fallback seguro.
     * Retorna array vazio se a tabela nao existe ou ocorre erro.
     */
    private static function readStorageSettingsFromDb(): array
    {
        try {
            if (!class_exists(\App\Models\Setting::class)) {
                return [];
            }

            $keys = [
                'storage_driver',
                'storage_bucket',
                'storage_endpoint',
                'storage_region',
                'storage_access_key',
                'storage_secret_key',
                'storage_url',
                'storage_path_style',
            ];

            $result = [];
            foreach ($keys as $key) {
                $value = \App\Models\Setting::get($key, null);
                if ($value !== null && $value !== '') {
                    $result[$key] = $value;
                }
            }
            return $result;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function selectedDisk(array $settings = []): string
    {
        return (string) (config('uploads.selected_disk', 'public') ?: 'public');
    }

    public static function effectiveDisk(): string
    {
        return (string) (config('uploads.effective_disk', 'public') ?: 'public');
    }

    public static function disk()
    {
        return Storage::disk(self::effectiveDisk());
    }

    public static function storeUploadedFile(
        UploadedFile $file,
        string $directory,
        ?string $filename = null,
        array $options = []
    ): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        if (!$file->isValid()) {
            throw new RuntimeException('Arquivo enviado invalido ou corrompido.');
        }

        $shouldWatermark = array_key_exists('watermark', $options)
            ? (bool) $options['watermark']
            : (
                app(WatermarkService::class)->isWatermarkableImage($file)
                && app(WatermarkService::class)->shouldWatermarkUpload($directory)
            );

        if ($shouldWatermark) {
            $storedPath = app(WatermarkService::class)->processStorageImage(
                $file,
                $directory,
                $filename,
                ['prefix' => (string) ($options['prefix'] ?? 'image')]
            );
            self::recordUploadAnomaly();
            return $storedPath;
        }

        // Image processing pipeline: para imagens raster, delega ao
        // ImageProcessorService (sync se < 2MB, async via queue 'uploads' se >= 2MB).
        // Em caso de falha, cai no armazenamento padrao preservando o original.
        if (self::shouldProcessAsImage($file, $options)) {
            try {
                $storedPath = self::processImageInline($file, $directory, $filename, $options);
                self::recordUploadAnomaly();
                return $storedPath;
            } catch (Throwable $imageException) {
                Log::warning('UploadStorage: image processing falhou, armazenando original sem modificacao.', [
                    'exception' => $imageException->getMessage(),
                    'directory' => $directory,
                ]);
                // Continua para o caminho padrao abaixo (fail-safe).
            }
        }

        if (self::isLocal()) {
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
            $name = ($filename !== null && $filename !== '') ? $filename : uniqid('', true) . '.' . $extension;
            $targetDirectory = self::localTargetDirectory($directory);

            if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
                throw new RuntimeException('Nao foi possivel preparar o diretorio de upload: ' . $directory);
            }

            try {
                $file->move($targetDirectory, $name);
            } catch (Throwable $exception) {
                throw new RuntimeException('Nao foi possivel salvar o arquivo enviado.', 0, $exception);
            }

            $storedPath = ltrim(($directory !== '' ? $directory . '/' : '') . $name, '/');
            self::recordUploadAnomaly();
            return $storedPath;
        }

        $options = ['visibility' => 'public'];
        if ($filename !== null && $filename !== '') {
            $storedPath = (string) self::disk()->putFileAs($directory, $file, $filename, $options);
            self::recordUploadAnomaly();
            return $storedPath;
        }

        $storedPath = (string) self::disk()->putFile($directory, $file, $options);
        self::recordUploadAnomaly();
        return $storedPath;
    }

    /**
     * Registra o upload no AnomalyDetectorService para deteccao de
     * upload flood por usuario autenticado. Falhas sao silenciadas
     * para nao interromper o fluxo do upload (Requirement 11.7).
     *
     * Spec: advanced-security-performance, Requirement 11.2
     */
    private static function recordUploadAnomaly(): void
    {
        try {
            if (function_exists('auth') && auth()->check()) {
                app(\App\Services\AnomalyDetectorService::class)->recordUpload((int) auth()->id());
            }
        } catch (\Throwable $e) { /* swallow - nao bloqueia o fluxo de upload */ }
    }

    /**
     * Decide se o arquivo deve ser processado pelo ImageProcessorService.
     *
     * Critérios:
     *   - opção `process_image` desligada explicitamente => false (recursion guard)
     *   - extensão raster suportada (jpg, jpeg, png, gif, webp)
     *   - quando `process_image` nao informado, defaultamos para true se for imagem raster
     *
     * Spec: advanced-security-performance, Requirements 2.1, 2.7
     */
    private static function shouldProcessAsImage(UploadedFile $file, array $options): bool
    {
        // Recursion guard: se a opcao foi desligada explicitamente, nao processa.
        if (array_key_exists('process_image', $options) && $options['process_image'] === false) {
            return false;
        }

        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension() ?: ''));
        $isImage = in_array($extension, self::IMAGE_EXTENSIONS, true);

        if (!$isImage) {
            return false;
        }

        // Quando explicitamente true, processa. Quando ausente, default = true para imagens.
        return array_key_exists('process_image', $options)
            ? (bool) $options['process_image']
            : true;
    }

    /**
     * Roteia o processamento de imagem entre sincrono e assincrono baseado no tamanho.
     *
     * - Arquivos < 2MB: ImageProcessorService::process() inline, retorna originalPath
     * - Arquivos >= 2MB: armazena original imediatamente e despacha ProcessImageUploadJob
     *   na queue 'uploads' para gerar variantes em background
     *
     * Em caso de falha, propaga a excecao para o caller (storeUploadedFile),
     * que tem fallback para armazenamento sem processamento.
     */
    private static function processImageInline(
        UploadedFile $file,
        string $directory,
        ?string $filename,
        array $options
    ): string {
        $size = (int) ($file->getSize() ?: 0);

        if ($size > 0 && $size < self::ASYNC_PROCESS_THRESHOLD_BYTES) {
            // SINCRONO: processa imediatamente e retorna o originalPath.
            $processorOptions = $options;
            unset($processorOptions['process_image'], $processorOptions['watermark']);
            if ($filename !== null && $filename !== '') {
                $processorOptions['filename'] = $filename;
            }

            $result = app(ImageProcessorService::class)->process($file, $directory, $processorOptions);

            // process() ja faz fallback fail-safe interno. Se result.originalPath estiver
            // vazio, significa que ate o fallback falhou - propaga excecao para o caller.
            if ($result->originalPath === '') {
                throw new RuntimeException('ImageProcessorService nao conseguiu armazenar a imagem.');
            }

            return $result->originalPath;
        }

        // ASSINCRONO: armazena o original imediatamente e enfileira o pos-processamento.
        $storedPath = self::storeOriginalWithoutProcessing($file, $directory, $filename);
        self::dispatchImageProcessing($storedPath, $directory, $options);

        return $storedPath;
    }

    /**
     * Armazena o arquivo original sem qualquer processamento de imagem.
     * Usado pela rota assincrona e em casos onde o pos-processamento e adiado.
     */
    private static function storeOriginalWithoutProcessing(
        UploadedFile $file,
        string $directory,
        ?string $filename
    ): string {
        if (self::isLocal()) {
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
            $name = ($filename !== null && $filename !== '') ? $filename : uniqid('', true) . '.' . $extension;
            $targetDirectory = self::localTargetDirectory($directory);

            if (!is_dir($targetDirectory) && !@mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
                throw new RuntimeException('Nao foi possivel preparar o diretorio de upload: ' . $directory);
            }

            try {
                $file->move($targetDirectory, $name);
            } catch (Throwable $exception) {
                throw new RuntimeException('Nao foi possivel salvar o arquivo enviado.', 0, $exception);
            }

            return ltrim(($directory !== '' ? $directory . '/' : '') . $name, '/');
        }

        $putOptions = ['visibility' => 'public'];
        if ($filename !== null && $filename !== '') {
            return (string) self::disk()->putFileAs($directory, $file, $filename, $putOptions);
        }

        return (string) self::disk()->putFile($directory, $file, $putOptions);
    }

    /**
     * Despacha o job de pos-processamento na queue 'uploads'.
     *
     * Em caso de falha do dispatch (queue indisponivel, banco fora do ar),
     * apenas loga - o original ja foi armazenado e permanece intacto.
     */
    private static function dispatchImageProcessing(
        string $storedPath,
        string $directory,
        array $options
    ): void {
        try {
            $jobOptions = $options;
            // Remove flags de controle que nao se aplicam ao job.
            unset($jobOptions['process_image'], $jobOptions['watermark'], $jobOptions['prefix']);

            ProcessImageUploadJob::dispatch($storedPath, $directory, $jobOptions);
        } catch (Throwable $exception) {
            Log::warning('UploadStorage: falha ao enfileirar ProcessImageUploadJob, original preservado.', [
                'exception' => $exception->getMessage(),
                'stored_path' => $storedPath,
                'directory' => $directory,
            ]);
        }
    }

    public static function isLocal(): bool
    {
        return self::effectiveDisk() === 'public';
    }

    public static function effectiveUploadLimitBytes(?int $applicationMaxBytes = null, ?string $uploadMax = null, ?string $postMax = null): ?int
    {
        $limits = [];

        $uploadLimit = self::parseIniSizeToBytes($uploadMax ?? ini_get('upload_max_filesize'));
        if ($uploadLimit !== null && $uploadLimit > 0) {
            $limits[] = $uploadLimit;
        }

        $postLimit = self::parseIniSizeToBytes($postMax ?? ini_get('post_max_size'));
        if ($postLimit !== null && $postLimit > 0) {
            $limits[] = $postLimit;
        }

        if ($applicationMaxBytes !== null && $applicationMaxBytes > 0) {
            $limits[] = $applicationMaxBytes;
        }

        if ($limits === []) {
            return null;
        }

        return min($limits);
    }

    public static function recommendedChunkSizeBytes(?int $applicationMaxBytes = null): int
    {
        $effectiveLimit = self::effectiveUploadLimitBytes($applicationMaxBytes);
        $fallback = 1024 * 1024;
        $minimum = 256 * 1024;
        $maximum = 5 * 1024 * 1024;
        $safetyMargin = 512 * 1024;

        if ($effectiveLimit === null || $effectiveLimit <= 0) {
            return $applicationMaxBytes !== null && $applicationMaxBytes > 0
                ? max($minimum, min($fallback, $applicationMaxBytes))
                : $fallback;
        }

        $chunkSize = $effectiveLimit - $safetyMargin;
        if ($applicationMaxBytes !== null && $applicationMaxBytes > 0) {
            $chunkSize = min($chunkSize, $applicationMaxBytes);
        }

        return max($minimum, min($chunkSize, $maximum));
    }

    public static function parseIniSizeToBytes(?string $value): ?int
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (!preg_match('/^(\d+)([kmgtp]?)(b)?$/i', $value, $matches)) {
            return is_numeric($value) ? (int) $value : null;
        }

        $bytes = (int) $matches[1];
        $unit = strtolower($matches[2] ?? '');
        $multipliers = [
            '' => 1,
            'k' => 1024,
            'm' => 1024 ** 2,
            'g' => 1024 ** 3,
            't' => 1024 ** 4,
            'p' => 1024 ** 5,
        ];

        return isset($multipliers[$unit]) ? $bytes * $multipliers[$unit] : $bytes;
    }

    public static function normalizePath(?string $path): ?string
    {
        $value = trim((string) $path);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            $value = (string) parse_url($value, PHP_URL_PATH);
        }

        $value = str_replace('\\', '/', $value);
        $value = preg_replace('/[?#].*$/', '', $value) ?? $value;
        $value = trim($value, '/');

        foreach (['storage/app/public/', 'public/'] as $prefix) {
            if (str_starts_with($value, $prefix)) {
                $value = substr($value, strlen($prefix));
            }
        }

        if (str_starts_with($value, 'storage/')) {
            $value = substr($value, strlen('storage/'));
        }

        return $value !== '' ? $value : null;
    }

    public static function url(?string $path, ?string $default = null): ?string
    {
        $raw = trim((string) $path);
        if ($raw === '') {
            return $default;
        }

        if (preg_match('/^https?:\/\//i', $raw)) {
            return $raw;
        }

        $normalized = self::normalizePath($raw);
        if ($normalized === null) {
            return $default;
        }

        // PRIORIDADE 1: Se o arquivo existe localmente, SEMPRE usar URL local.
        // Isso garante que imagens/PDFs nunca "sumam" do site mesmo com S3 ativo.
        $localUrl = self::resolveLocalUrl($raw, $normalized);
        if ($localUrl !== null) {
            return $localUrl;
        }

        // PRIORIDADE 2: Se o disco efetivo e S3, tentar gerar URL S3 (com cache).
        if (self::effectiveDisk() === 's3') {
            if (self::isOnS3Cached($normalized)) {
                try {
                    // Usar URL assinada temporaria (1h) para garantir acesso mesmo com bucket privado
                    return Storage::disk('s3')->temporaryUrl($normalized, now()->addHour());
                } catch (\Throwable $e) {
                    // Se temporaryUrl nao for suportado, tentar url() normal
                    try {
                        return Storage::disk('s3')->url($normalized);
                    } catch (\Throwable $e2) {
                        // Fallback final
                    }
                }
            }
        }

        // PRIORIDADE 3: Fallback — gerar URL local mesmo que arquivo nao exista
        // (pode ser que o arquivo apareca depois, ou que o path esteja em cache do navegador)
        if (self::shouldUseUploadsRoute($raw, $normalized)) {
            return asset($normalized);
        }

        return asset('storage/' . ltrim($normalized, '/'));
    }

    /**
     * Tenta resolver URL local se o arquivo existir fisicamente no servidor.
     */
    private static function resolveLocalUrl(string $raw, string $normalized): ?string
    {
        // Verificar se existe em algum dos caminhos locais conhecidos
        foreach (self::publicCandidates($normalized) as $candidate) {
            if (is_file($candidate)) {
                // Arquivo existe localmente — gerar URL local
                if (self::shouldUseUploadsRoute($raw, $normalized)) {
                    return asset($normalized);
                }
                return asset('storage/' . ltrim($normalized, '/'));
            }
        }
        return null;
    }

    /**
     * Verifica se o arquivo existe no S3, usando cache de longa duracao.
     * Apos uma migracao, o cache e populado proativamente para evitar HEAD repetitivos.
     */
    public static function isOnS3Cached(string $normalized): bool
    {
        $cacheKey = self::s3LocationCacheKey($normalized);

        try {
            $cached = Cache::get($cacheKey);

            // 'yes' = arquivo confirmado no S3 (positivo persistente)
            if ($cached === 'yes') {
                return true;
            }

            // 'no' = arquivo confirmado nao existe no S3 (negativo curto, evita repetir HEAD)
            if ($cached === 'no') {
                return false;
            }

            // Sem cache: faz HEAD no S3 e cacheia o resultado
            $exists = Storage::disk('s3')->exists($normalized);
            // Positivo: cache longo. Negativo: cache curto (5 min) para nao impedir migracao tardia
            Cache::put($cacheKey, $exists ? 'yes' : 'no', $exists ? self::S3_LOCATION_CACHE_TTL : 300);
            return $exists;
        } catch (\Throwable $e) {
            // Falha de conexao S3 nao deve bloquear o site — cai pro fallback local
            return false;
        }
    }

    /**
     * Marca um caminho como confirmado no S3. Usado pela migracao para popular cache proativamente.
     */
    public static function markAsOnS3(string $path): void
    {
        $normalized = self::normalizePath($path);
        if ($normalized === null) {
            return;
        }
        try {
            Cache::put(self::s3LocationCacheKey($normalized), 'yes', self::S3_LOCATION_CACHE_TTL);
        } catch (\Throwable $e) {
            // noop
        }
    }

    /**
     * Remove o cache de localizacao S3 para um caminho (util ao deletar do S3).
     */
    public static function forgetS3Location(string $path): void
    {
        $normalized = self::normalizePath($path);
        if ($normalized === null) {
            return;
        }
        try {
            Cache::forget(self::s3LocationCacheKey($normalized));
        } catch (\Throwable $e) {
            // noop
        }
    }

    /**
     * Limpa todo o cache de localizacao S3 (util apos trocar bucket).
     */
    public static function flushS3LocationCache(): void
    {
        try {
            // Como nao temos cache tags com driver file, usamos um marker version.
            Cache::put('s3_location_cache_version', (string) time(), self::S3_LOCATION_CACHE_TTL);
        } catch (\Throwable $e) {
            // noop
        }
    }

    private static function s3LocationCacheKey(string $normalized): string
    {
        $version = Cache::get('s3_location_cache_version', '1');
        return 'unn:s3loc:v' . $version . ':' . md5($normalized);
    }

    public static function exists(?string $path): bool
    {
        $normalized = self::normalizePath($path);
        if ($normalized === null) {
            return false;
        }

        try {
            if (self::disk()->exists($normalized)) {
                return true;
            }
        } catch (\Throwable $e) {
            // noop
        }

        foreach (self::publicCandidates($normalized) as $candidate) {
            if (is_file($candidate)) {
                return true;
            }
        }

        return false;
    }

    public static function size(?string $path): ?int
    {
        $normalized = self::normalizePath($path);
        if ($normalized === null) {
            return null;
        }

        try {
            $size = self::disk()->size($normalized);
            if (is_numeric($size) && (int) $size >= 0) {
                return (int) $size;
            }
        } catch (\Throwable $e) {
            // noop
        }

        foreach (self::publicCandidates($normalized) as $candidate) {
            if (is_file($candidate)) {
                $size = @filesize($candidate);
                if (is_int($size) && $size >= 0) {
                    return $size;
                }
            }
        }

        return null;
    }

    public static function delete(?string $path): bool
    {
        $normalized = self::normalizePath($path);
        if ($normalized === null) {
            return false;
        }

        try {
            if (self::disk()->exists($normalized)) {
                return (bool) self::disk()->delete($normalized);
            }
        } catch (\Throwable $e) {
            // noop
        }

        $deleted = false;
        foreach (self::publicCandidates($normalized) as $candidate) {
            if (is_file($candidate)) {
                @unlink($candidate);
                $deleted = true;
            }
        }

        return $deleted;
    }

    public static function response(string $path)
    {
        $normalized = self::normalizePath($path);
        abort_if($normalized === null, 404);

        if (self::isLocal()) {
            foreach (self::publicCandidates($normalized) as $candidate) {
                if (is_file($candidate)) {
                    $size = @filesize($candidate);
                    $lastModified = @filemtime($candidate);
                    $headers = [
                        'Content-Type' => self::mimeTypeFromPath($candidate),
                        'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=604800',
                        'Accept-Ranges' => 'bytes',
                    ];

                    if (is_int($lastModified) && $lastModified > 0) {
                        $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', $lastModified) . ' GMT';
                    }

                    if (!is_int($size) || $size < 1) {
                        return response()->stream(function () use ($candidate) {
                            $handle = fopen($candidate, 'rb');
                            if ($handle !== false) {
                                fpassthru($handle);
                                fclose($handle);
                            }
                        }, 200, $headers);
                    }

                    $etag = '"' . sha1($normalized . '|' . $size . '|' . (int) $lastModified) . '"';
                    $headers['ETag'] = $etag;

                    if (trim((string) request()->header('If-None-Match')) === $etag) {
                        return response('', 304, $headers);
                    }

                    $range = self::requestedByteRange($size);
                    if ($range === false) {
                        return response('', 416, $headers + ['Content-Range' => 'bytes */' . $size]);
                    }

                    [$start, $end] = $range ?? [0, $size - 1];
                    $length = $end - $start + 1;
                    $status = $range === null ? 200 : 206;
                    $headers['Content-Length'] = (string) $length;

                    if ($status === 206) {
                        $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
                    }

                    return response()->stream(function () use ($candidate, $start, $length) {
                        $handle = fopen($candidate, 'rb');
                        if ($handle === false) {
                            return;
                        }

                        if ($start > 0) {
                            fseek($handle, $start);
                        }

                        $remaining = $length;
                        while ($remaining > 0 && !feof($handle)) {
                            $chunk = fread($handle, min(1024 * 1024, $remaining));
                            if ($chunk === false || $chunk === '') {
                                break;
                            }
                            echo $chunk;
                            $remaining -= strlen($chunk);
                        }
                        fclose($handle);
                    }, $status, $headers);
                }
            }
        }

        $stream = self::disk()->readStream($normalized);
        if ($stream === false) {
            abort(404);
        }

        $headers = [
            'Content-Type' => self::mimeType($normalized),
            'Cache-Control' => 'public, max-age=31536000',
        ];

        try {
            $size = (int) self::disk()->size($normalized);
            if ($size > 0) {
                $headers['Content-Length'] = (string) $size;
            }
        } catch (\Throwable $e) {
            // noop
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $headers);
    }

    /** @return array{0:int,1:int}|null|false */
    private static function requestedByteRange(int $size): array|null|false
    {
        $header = trim((string) request()->header('Range', ''));
        if ($header === '') {
            return null;
        }

        if (preg_match('/^bytes=(\d*)-(\d*)$/', $header, $matches) !== 1) {
            return false;
        }

        if ($matches[1] === '' && $matches[2] === '') {
            return false;
        }

        if ($matches[1] === '') {
            $suffixLength = (int) $matches[2];
            if ($suffixLength < 1) {
                return false;
            }

            return [max(0, $size - $suffixLength), $size - 1];
        }

        $start = (int) $matches[1];
        $end = $matches[2] === '' ? $size - 1 : (int) $matches[2];

        if ($start >= $size || $end < $start) {
            return false;
        }

        return [$start, min($end, $size - 1)];
    }

    public static function localPublicDiskConfig(): array
    {
        return [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => '/storage',
            'visibility' => 'public',
            'throw' => false,
        ];
    }

    private static function s3Configured(array $settings): bool
    {
        return trim((string) ($settings['s3_key'] ?? '')) !== ''
            && trim((string) ($settings['s3_secret'] ?? '')) !== ''
            && trim((string) ($settings['s3_region'] ?? '')) !== ''
            && trim((string) ($settings['s3_bucket'] ?? '')) !== '';
    }

    private static function s3DriverAvailable(): bool
    {
        $override = config('uploads.s3_driver_available');
        if ($override !== null) {
            return (bool) $override;
        }

        return class_exists(\Aws\S3\S3Client::class)
            && class_exists(\League\Flysystem\AwsS3V3\AwsS3V3Adapter::class)
            && class_exists(\League\Flysystem\AwsS3V3\PortableVisibilityConverter::class);
    }

    private static function s3DiskConfig(array $settings): array
    {
        $endpoint = self::normalizeS3Endpoint($settings['s3_endpoint'] ?? null);
        $pathStyle = self::toBoolean($settings['s3_path_style'] ?? false);
        $bucket = trim((string) ($settings['s3_bucket'] ?? ''));

        $config = array_merge((array) config('filesystems.disks.s3', []), [
            'driver' => 's3',
            'key' => trim((string) ($settings['s3_key'] ?? '')),
            'secret' => trim((string) ($settings['s3_secret'] ?? '')),
            'region' => trim((string) ($settings['s3_region'] ?? '')),
            'bucket' => $bucket,
            'endpoint' => $endpoint,
            'url' => self::resolveS3PublicBaseUrl($settings, $endpoint, $bucket, $pathStyle),
            'use_path_style_endpoint' => $pathStyle,
            'throw' => false,
        ]);

        if (empty($config['endpoint'])) {
            unset($config['endpoint']);
        }

        if (empty($config['url'])) {
            unset($config['url']);
        } else {
            $config['url'] = rtrim((string) $config['url'], '/');
        }

        return $config;
    }

    private static function forgetDisks(): void
    {
        try {
            Storage::forgetDisk('public');
            Storage::forgetDisk('uploads');
            Storage::forgetDisk('s3');
            Storage::forgetDisk('local_public');
        } catch (\Throwable $e) {
            // noop
        }
    }

    private static function publicCandidates(string $normalized): array
    {
        $configuredRoot = trim((string) config(
            'filesystems.disks.public.root',
            is_dir(public_path('storage')) ? public_path('storage') : storage_path('app/public')
        ));

        $candidates = [
            public_path($normalized),
        ];

        if ($configuredRoot !== '') {
            $candidates[] = rtrim($configuredRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
        }

        if (!str_starts_with($normalized, 'uploads/')) {
            $candidates[] = public_path('uploads/' . $normalized);
        }

        if (!str_starts_with($normalized, 'storage/')) {
            $candidates[] = public_path('storage/' . $normalized);
        }

        return array_values(array_unique($candidates));
    }

    private static function localTargetDirectory(string $directory): string
    {
        $root = (string) config(
            'filesystems.disks.public.root',
            is_dir(public_path('storage')) ? public_path('storage') : storage_path('app/public')
        );

        return $directory !== '' ? rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory) : $root;
    }

    private static function mimeType(string $path): string
    {
        try {
            return (string) (self::disk()->mimeType($path) ?: self::mimeTypeFromPath($path));
        } catch (\Throwable $e) {
            return self::mimeTypeFromPath($path);
        }
    }

    private static function mimeTypeFromPath(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'txt', 'log', 'csv' => 'text/plain; charset=UTF-8',
            'json' => 'application/json',
            default => 'application/octet-stream',
        };
    }

    private static function shouldUseUploadsRoute(string $raw, string $normalized): bool
    {
        $raw = trim(str_replace('\\', '/', $raw), '/');

        return str_starts_with($raw, 'uploads/') || str_starts_with($normalized, 'uploads/');
    }

    private static function distinctPublicBaseUrl(): ?string
    {
        return null;
    }

    private static function resolveS3PublicBaseUrl(array $settings, ?string $endpoint, string $bucket, bool $pathStyle): ?string
    {
        $configuredUrl = self::normalizeS3Url($settings['s3_url'] ?? null);
        if ($configuredUrl !== null && !self::matchesAppHost($configuredUrl)) {
            return $configuredUrl;
        }

        if ($endpoint === null || $bucket === '') {
            return null;
        }

        $baseEndpoint = rtrim($endpoint, '/');

        return $pathStyle
            ? $baseEndpoint . '/' . rawurlencode($bucket)
            : preg_replace('#^(https?://)#i', '$1' . $bucket . '.', $baseEndpoint, 1);
    }

    private static function normalizeS3Endpoint(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $value)) {
            $value = 'https://' . $value;
        }

        return rtrim($value, '/');
    }

    private static function normalizeS3Url(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $value)) {
            $value = 'https://' . $value;
        }

        return rtrim($value, '/');
    }

    private static function matchesAppHost(string $url): bool
    {
        $configuredHost = strtolower((string) parse_url($url, PHP_URL_HOST));
        $appHost = strtolower((string) parse_url((string) config('app.url', ''), PHP_URL_HOST));

        return $configuredHost !== '' && $appHost !== '' && $configuredHost === $appHost;
    }

    private static function joinUrl(string $baseUrl, string $path): string
    {
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private static function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }
}
