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
 * Sistema UNN - Registry de provedores S3-compativeis.
 *
 * Centraliza a leitura/escrita de configuracoes dos provedores
 * (IDrive e2, Wasabi, AWS S3) na tabela settings, gerencia o
 * provedor ativo e roda testes de conexao isolados por provedor.
 *
 * Convencoes:
 *   - Identificadores internos: 'idrive', 'wasabi', 'aws', 'local'
 *   - Prefixos na tabela settings: 'idrive_', 'wasabi_', 'aws_'
 *   - Chave do provedor ativo: 'storage_active_provider'
 *
 * Spec: .kiro/specs/multi-provider-s3-storage (tasks 3.1, 3.2)
 * Requirements: 1.1-1.5, 2.1, 2.4, 5.1-5.7, 8.4
 */

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem as FlysystemFilesystem;

final class StorageProviderRegistry
{
    /** Chaves internas dos provedores S3 suportados. */
    public const PROVIDERS = ['idrive', 'wasabi', 'aws'];

    /** Provedor padrao quando nenhum esta selecionado. */
    public const DEFAULT_PROVIDER = 'idrive';

    /** Identificador especial para "sem S3" (disco local). */
    public const PROVIDER_LOCAL = 'local';

    /** Chave em settings para o provedor ativo. */
    public const KEY_ACTIVE_PROVIDER = 'storage_active_provider';

    /** Nome de exibicao por provedor (Req 9). */
    public const DISPLAY_NAMES = [
        'idrive' => 'IDrive e2',
        'wasabi' => 'Wasabi',
        'aws' => 'AWS S3',
        'local' => 'Local (disco publico)',
    ];

    /** Limite total para o teste de conexao (Req 5.6). */
    private const TEST_TIMEOUT_SECONDS = 30;

    /** Timeout de leitura HTTP por step (Req 5.6 - parte). */
    private const TEST_HTTP_TIMEOUT_SECONDS = 8;

    /**
     * Retorna o identificador do provedor atualmente ativo.
     * Falla para DEFAULT_PROVIDER quando o valor armazenado nao
     * esta na lista de provedores conhecidos.
     */
    public function activeProvider(): string
    {
        $stored = (string) Setting::get(self::KEY_ACTIVE_PROVIDER, self::DEFAULT_PROVIDER);
        $stored = strtolower(trim($stored));

        if ($stored === self::PROVIDER_LOCAL) {
            return self::PROVIDER_LOCAL;
        }

        if (in_array($stored, self::PROVIDERS, true)) {
            return $stored;
        }

        return self::DEFAULT_PROVIDER;
    }

    /**
     * Define o provedor ativo. NAO valida creds aqui (a validacao
     * via testConnection e responsabilidade do controller).
     *
     * Side effect: invalida o cache de localizacao S3 (Req 2.4)
     * para que URLs antigas em cache nao apontem para o provedor
     * anterior.
     *
     * @throws \InvalidArgumentException quando o provedor for invalido
     */
    public function setActiveProvider(string $provider): void
    {
        $provider = strtolower(trim($provider));

        if (
            $provider !== self::PROVIDER_LOCAL
            && !in_array($provider, self::PROVIDERS, true)
        ) {
            throw new \InvalidArgumentException(
                sprintf('Provedor desconhecido: "%s". Validos: %s.', $provider, implode(', ', self::PROVIDERS))
            );
        }

        $previous = $this->activeProvider();

        Setting::set(self::KEY_ACTIVE_PROVIDER, $provider);
        Setting::flushRuntimeCache();

        if ($provider !== $previous) {
            // Req 2.4: alternar provedor invalida URLs em cache.
            UploadStorage::flushS3LocationCache();

            // Req 8.4: registrar mudanca no canal security.
            $this->securityLog('storage.active_provider.changed', [
                'previous' => $previous,
                'current' => $provider,
            ]);
        }

        // Aplica a mudanca em runtime imediatamente para a request atual
        // (Req 2.2). UploadStorage::applyRuntimeConfig le do registry.
        UploadStorage::applyRuntimeConfig();
    }

    /**
     * Le a configuracao de UM provedor a partir da tabela settings.
     * Tolera valores ausentes (retorna Config com strings vazias).
     */
    public function configFor(string $provider): StorageProviderConfig
    {
        $this->guardProvider($provider);
        $prefix = $this->prefixFor($provider);
        $settings = $this->readPrefixedSettings($prefix);

        return StorageProviderConfig::fromSettings($settings, $prefix);
    }

    /**
     * Le as configuracoes de TODOS os provedores S3 conhecidos.
     *
     * @return array<string, StorageProviderConfig>
     */
    public function allConfigs(): array
    {
        $configs = [];
        foreach (self::PROVIDERS as $provider) {
            $configs[$provider] = $this->configFor($provider);
        }
        return $configs;
    }

    /**
     * Persiste a configuracao de UM provedor sem afetar os demais
     * (Req 1.2 + 1.5).
     */
    public function persistConfig(string $provider, StorageProviderConfig $config): void
    {
        $this->guardProvider($provider);

        $prefix = $this->prefixFor($provider);
        $payload = $config->toSettingsArray($prefix);

        foreach ($payload as $key => $value) {
            Setting::set($key, (string) $value);
        }
        Setting::flushRuntimeCache();

        // Req 8.4: registra alteracao de credenciais. NAO logamos
        // o secret em texto claro - apenas o fato da mudanca.
        $this->securityLog('storage.provider.credentials_updated', [
            'provider' => $provider,
            'has_access_key' => $config->accessKey !== '',
            'has_secret_key' => $config->secretKey !== '',
            'bucket' => $config->bucket,
            'region' => $config->region,
            'endpoint' => $config->endpoint,
        ]);

        // Aplica em runtime se o provedor alterado e o ativo (Req 2.2).
        if ($this->activeProvider() === $provider) {
            UploadStorage::applyRuntimeConfig();
        }
    }

    /**
     * Indica se um provedor possui credenciais validas (4 campos
     * minimos preenchidos). Delega para Config::isValid().
     */
    public function isConfigured(string $provider): bool
    {
        if ($provider === self::PROVIDER_LOCAL) {
            return true;
        }

        return $this->configFor($provider)->isValid();
    }

    /**
     * Retorna o array compativel com `filesystems.disks.s3.*` para
     * o provedor especificado. Use no boot da aplicacao para
     * sobrescrever o disco S3 com as creds do provedor ativo.
     *
     * @return array<string, mixed>
     */
    public function diskConfigArray(string $provider): array
    {
        $this->guardProvider($provider);
        $config = $this->configFor($provider);

        return [
            'driver' => 's3',
            'key' => $config->accessKey,
            'secret' => $config->secretKey,
            'region' => $config->region !== '' ? $config->region : 'us-east-1',
            'bucket' => $config->bucket,
            'url' => $config->url !== '' ? $config->url : null,
            'endpoint' => $config->endpoint !== '' ? $this->normalizeEndpoint($config->endpoint) : null,
            'use_path_style_endpoint' => $config->pathStyle,
            'visibility' => 'public',
            'throw' => false,
        ];
    }

    /**
     * Retorna o nome de exibicao de um provedor para uso na UI.
     */
    public function displayName(string $provider): string
    {
        return self::DISPLAY_NAMES[$provider] ?? $provider;
    }

    /**
     * Executa um teste de conexao ISOLADO contra um provedor
     * especifico, SEM alterar o provedor ativo (Req 5.5).
     *
     * Steps (Req 5.2):
     *   1. upload de arquivo de teste
     *   2. verificar existencia
     *   3. gerar URL publica
     *   4. HTTP GET na URL
     *   5. comparar conteudo
     *   6. excluir o arquivo
     *
     * Garantias:
     *   - Aborta no primeiro step que falhar (Req 5.4).
     *   - Sempre tenta limpar o arquivo de teste, mesmo apos falha (Req 5.7).
     *   - Limite total de 30s (Req 5.6).
     */
    public function testConnection(string $provider): StorageTestResult
    {
        $this->guardProvider($provider);

        $result = new StorageTestResult($provider);
        $config = $this->configFor($provider);

        if (!$config->isValid()) {
            return $result->markFailed(
                'Provedor "' . $this->displayName($provider) . '" nao tem credenciais validas. '
                . 'Preencha Access Key, Secret Key, Bucket e Region antes de testar.'
            );
        }

        $startTotal = microtime(true);
        $deadline = $startTotal + self::TEST_TIMEOUT_SECONDS;
        $disk = $this->buildAdHocDisk($config);
        $testKey = '_storage_test_' . Str::uuid()->toString() . '.txt';
        $payload = 'unn-storage-test-' . bin2hex(random_bytes(16));
        $uploaded = false;

        try {
            // Step 1: upload
            $stepStart = microtime(true);
            try {
                $ok = $disk->put($testKey, $payload);
                if (!$ok) {
                    return $this->finishWithCleanup(
                        $result->addFailure('upload', 'Storage::put retornou false', $this->elapsedMs($stepStart)),
                        $disk,
                        $testKey,
                        false,
                        $startTotal
                    )->markFailed('Falha no upload do arquivo de teste.');
                }
                $uploaded = true;
                $result->addSuccess('upload', sprintf('arquivo de teste enviado (%d bytes)', strlen($payload)), $this->elapsedMs($stepStart));
            } catch (\Throwable $e) {
                return $this->finishWithCleanup(
                    $result->addFailure('upload', $this->shortError($e), $this->elapsedMs($stepStart)),
                    $disk,
                    $testKey,
                    $uploaded,
                    $startTotal
                )->markFailed('Falha no upload: ' . $this->shortError($e));
            }

            if ($this->shouldAbortDueToTimeout($deadline)) {
                return $this->finishWithCleanup($result, $disk, $testKey, $uploaded, $startTotal)->markTimeout();
            }

            // Step 2: exists
            $stepStart = microtime(true);
            try {
                $exists = $disk->exists($testKey);
                if (!$exists) {
                    return $this->finishWithCleanup(
                        $result->addFailure('exists', 'arquivo nao encontrado apos upload', $this->elapsedMs($stepStart)),
                        $disk,
                        $testKey,
                        $uploaded,
                        $startTotal
                    )->markFailed('Arquivo de teste nao foi encontrado apos upload.');
                }
                $result->addSuccess('exists', 'arquivo confirmado no bucket', $this->elapsedMs($stepStart));
            } catch (\Throwable $e) {
                return $this->finishWithCleanup(
                    $result->addFailure('exists', $this->shortError($e), $this->elapsedMs($stepStart)),
                    $disk,
                    $testKey,
                    $uploaded,
                    $startTotal
                )->markFailed('Falha ao verificar existencia: ' . $this->shortError($e));
            }

            if ($this->shouldAbortDueToTimeout($deadline)) {
                return $this->finishWithCleanup($result, $disk, $testKey, $uploaded, $startTotal)->markTimeout();
            }

            // Step 3: url (temporary url se suportado, senao url publica)
            $stepStart = microtime(true);
            $url = null;
            try {
                $url = $disk->temporaryUrl($testKey, now()->addMinutes(2));
                $result->addSuccess('url', $this->safeUrlForDisplay($url), $this->elapsedMs($stepStart));
            } catch (\Throwable $eTemp) {
                // Fallback: url publica (alguns provedores nao suportam temporaryUrl).
                try {
                    $url = $disk->url($testKey);
                    $result->addSuccess('url', $this->safeUrlForDisplay($url) . ' (publica)', $this->elapsedMs($stepStart));
                } catch (\Throwable $eUrl) {
                    return $this->finishWithCleanup(
                        $result->addFailure('url', $this->shortError($eUrl), $this->elapsedMs($stepStart)),
                        $disk,
                        $testKey,
                        $uploaded,
                        $startTotal
                    )->markFailed('Nao foi possivel gerar URL para o arquivo de teste.');
                }
            }

            if ($this->shouldAbortDueToTimeout($deadline)) {
                return $this->finishWithCleanup($result, $disk, $testKey, $uploaded, $startTotal)->markTimeout();
            }

            // Step 4: HTTP GET na URL gerada
            $stepStart = microtime(true);
            $httpBody = null;
            try {
                $response = Http::timeout(self::TEST_HTTP_TIMEOUT_SECONDS)
                    ->withOptions(['verify' => true])
                    ->get((string) $url);

                if (!$response->successful()) {
                    return $this->finishWithCleanup(
                        $result->addFailure('http_get', 'HTTP ' . $response->status(), $this->elapsedMs($stepStart)),
                        $disk,
                        $testKey,
                        $uploaded,
                        $startTotal
                    )->markFailed('URL retornou HTTP ' . $response->status() . '.');
                }

                $httpBody = $response->body();
                $result->addSuccess('http_get', 'HTTP 200 (' . strlen($httpBody) . ' bytes)', $this->elapsedMs($stepStart));
            } catch (\Throwable $e) {
                return $this->finishWithCleanup(
                    $result->addFailure('http_get', $this->shortError($e), $this->elapsedMs($stepStart)),
                    $disk,
                    $testKey,
                    $uploaded,
                    $startTotal
                )->markFailed('Falha no GET HTTP: ' . $this->shortError($e));
            }

            if ($this->shouldAbortDueToTimeout($deadline)) {
                return $this->finishWithCleanup($result, $disk, $testKey, $uploaded, $startTotal)->markTimeout();
            }

            // Step 5: comparar conteudo
            $stepStart = microtime(true);
            if ($httpBody !== $payload) {
                return $this->finishWithCleanup(
                    $result->addFailure('compare', sprintf('conteudo divergente (esperado %d bytes, recebido %d)', strlen($payload), strlen((string) $httpBody)), $this->elapsedMs($stepStart)),
                    $disk,
                    $testKey,
                    $uploaded,
                    $startTotal
                )->markFailed('Conteudo retornado pela URL nao bate com o payload enviado.');
            }
            $result->addSuccess('compare', 'conteudo identico ao enviado', $this->elapsedMs($stepStart));

            if ($this->shouldAbortDueToTimeout($deadline)) {
                return $this->finishWithCleanup($result, $disk, $testKey, $uploaded, $startTotal)->markTimeout();
            }

            // Step 6: delete (sempre tentado em finishWithCleanup, mas
            // aqui registramos como step explicito quando ha sucesso).
            $stepStart = microtime(true);
            try {
                $deleted = $disk->delete($testKey);
                if ($deleted) {
                    $result->addSuccess('delete', 'arquivo de teste removido', $this->elapsedMs($stepStart));
                } else {
                    $result->addFailure('delete', 'Storage::delete retornou false', $this->elapsedMs($stepStart));
                }
            } catch (\Throwable $e) {
                $result->addFailure('delete', $this->shortError($e), $this->elapsedMs($stepStart));
            }

            $result->totalLatencyMs = round((microtime(true) - $startTotal) * 1000, 2);

            // Sucesso global: todos os steps sucederam (delete pode ter
            // falhado e ainda assim consideramos teste bem-sucedido para
            // o usuario, ja que upload/exists/url/get/compare ok).
            return $result->markSuccess();
        } finally {
            // Garantia ultima: tenta limpar o arquivo se ainda existir
            // (Req 5.7). Em caminhos de sucesso ja foi limpo no step 6.
            if ($uploaded) {
                try {
                    if ($disk->exists($testKey)) {
                        $disk->delete($testKey);
                    }
                } catch (\Throwable $e) {
                    // noop - cleanup best-effort
                }
            }
        }
    }

    /* -------------------------------------------------------------- */
    /* Helpers privados                                                */
    /* -------------------------------------------------------------- */

    /**
     * Mapeia um identificador de provedor para o prefixo da tabela settings.
     */
    private function prefixFor(string $provider): string
    {
        return $provider . '_';
    }

    /**
     * Garante que o provedor informado e suportado (idrive|wasabi|aws).
     * NAO aceita 'local' aqui - o tratamento de local e feito antes,
     * em activeProvider() / setActiveProvider().
     *
     * @throws \InvalidArgumentException
     */
    private function guardProvider(string $provider): void
    {
        if (!in_array($provider, self::PROVIDERS, true)) {
            throw new \InvalidArgumentException(
                sprintf('Provedor S3 desconhecido: "%s". Validos: %s.', $provider, implode(', ', self::PROVIDERS))
            );
        }
    }

    /**
     * Le todas as chaves de settings que comecam com um prefixo.
     * Usa o cache estatico de Setting (sem hit em DB se ja carregado).
     *
     * @return array<string, mixed>
     */
    private function readPrefixedSettings(string $prefix): array
    {
        $result = [];
        foreach (StorageProviderConfig::FIELDS as $field) {
            $key = $prefix . $field;
            $result[$key] = Setting::get($key, '');
        }
        return $result;
    }

    /**
     * Normaliza o endpoint para garantir scheme. Se vier sem
     * https://, adiciona automaticamente.
     */
    private function normalizeEndpoint(string $endpoint): string
    {
        $endpoint = trim($endpoint);
        if ($endpoint === '') {
            return $endpoint;
        }
        if (!str_contains($endpoint, '://')) {
            return 'https://' . $endpoint;
        }
        return $endpoint;
    }

    /**
     * Constroi um disk Filesystem ad-hoc para testar um provedor sem
     * alterar a configuracao global de filesystems. Usa Storage::build
     * (Laravel 10) com a config do provedor.
     */
    private function buildAdHocDisk(StorageProviderConfig $config): \Illuminate\Contracts\Filesystem\Filesystem
    {
        $diskConfig = [
            'driver' => 's3',
            'key' => $config->accessKey,
            'secret' => $config->secretKey,
            'region' => $config->region !== '' ? $config->region : 'us-east-1',
            'bucket' => $config->bucket,
            'url' => $config->url !== '' ? $config->url : null,
            'endpoint' => $config->endpoint !== '' ? $this->normalizeEndpoint($config->endpoint) : null,
            'use_path_style_endpoint' => $config->pathStyle,
            'visibility' => 'public',
            'throw' => true,
        ];

        return Storage::build($diskConfig);
    }

    /**
     * Latencia em milissegundos a partir de um microtime(true) de inicio.
     */
    private function elapsedMs(float $start): float
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Retorna true quando o budget total de 30s ja foi excedido.
     */
    private function shouldAbortDueToTimeout(float $deadline): bool
    {
        return microtime(true) > $deadline;
    }

    /**
     * Mensagem curta e segura derivada de uma excecao para mostrar
     * na UI (sem stack trace, sem creds vazadas).
     */
    private function shortError(\Throwable $e): string
    {
        $msg = $e->getMessage();
        if (strlen($msg) > 200) {
            $msg = substr($msg, 0, 197) . '...';
        }
        // Remove quaisquer ocorrencias de tokens AWS conhecidos.
        $msg = preg_replace('/AKIA[0-9A-Z]{16}/', '[ACCESS_KEY]', $msg) ?? $msg;
        return $msg;
    }

    /**
     * Trunca uma URL longa para exibicao na UI sem expor query string
     * com credenciais.
     */
    private function safeUrlForDisplay(?string $url): string
    {
        if ($url === null || $url === '') {
            return '';
        }
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['host'])) {
            return strlen($url) > 80 ? substr($url, 0, 77) . '...' : $url;
        }
        $base = ($parts['scheme'] ?? 'https') . '://' . $parts['host'] . ($parts['path'] ?? '');
        return strlen($base) > 120 ? substr($base, 0, 117) . '...' : $base;
    }

    /**
     * Limpa o arquivo de teste e retorna o $result inalterado para
     * permitir chaining.
     */
    private function finishWithCleanup(
        StorageTestResult $result,
        \Illuminate\Contracts\Filesystem\Filesystem $disk,
        string $testKey,
        bool $uploaded,
        float $startTotal
    ): StorageTestResult {
        if ($uploaded) {
            try {
                if ($disk->exists($testKey)) {
                    $disk->delete($testKey);
                }
            } catch (\Throwable $e) {
                // noop - cleanup best-effort
            }
        }
        $result->totalLatencyMs = round((microtime(true) - $startTotal) * 1000, 2);
        return $result;
    }

    /**
     * Logs no canal security para auditoria. Falla silenciosa em caso
     * de erro (canal pode nao existir em ambientes minimos).
     *
     * @param array<string, mixed> $context
     */
    private function securityLog(string $message, array $context): void
    {
        try {
            Log::channel('security')->info($message, $context);
        } catch (\Throwable $e) {
            try {
                Log::info($message, $context);
            } catch (\Throwable $e2) {
                // noop - logging nunca pode bloquear operacao
            }
        }
    }
}
