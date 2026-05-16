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
 * Sistema UNN - Unit tests para LogsCleanup
 *
 * Spec: .kiro/specs/advanced-security-performance (task 14.3)
 *
 * Cobertura (focada em casos pontuais; o varredor probabilistico
 * estatistico esta em tests/Property/LogRotationTest.php):
 *
 *   - cleanup() retorna CleanupResult mesmo quando o diretorio de
 *     logs nao existe (fallback seguro).
 *   - Remocao de arquivos com idade > retencao por canal (waf 30,
 *     security 90, application 30) e contabilizacao de bytesReclaimed.
 *   - Compressao de arquivos com idade > 7 dias e <= retencao do
 *     canal, removendo o original e gerando .gz.
 *   - Arquivos com <= 7 dias permanecem intactos.
 *   - Retencao por canal sobrepoe a retencao global (security mantem
 *     90d mesmo quando log_retention_days esta configurado para 30d).
 *   - getRetentionDays() respeita os defaults documentados e leitura
 *     via Setting::get com fallback quando valor for <= 0.
 *   - compress() retorna false em arquivo inexistente sem lancar
 *     excecao e gera .gz removendo o original em caso de sucesso.
 *   - cleanup() nao propaga excecoes em condicoes adversas.
 *   - cleanup() continua processando os demais arquivos quando a
 *     compressao de um arquivo individual falha (simulado criando
 *     um diretorio no caminho do destino .gz, fail-safe portavel).
 *   - handle() loga "logs:cleanup completed" com files_removed,
 *     files_compressed, bytes_reclaimed e errors no contexto.
 *
 * Estrategia:
 *
 *   - Diretorio temporario criado via sys_get_temp_dir() para
 *     isolamento total e reapontado via $this->app->useStoragePath().
 *     O storage path original eh restaurado em tearDown.
 *
 *   - Para evitar I/O no banco durante a leitura de retencao via
 *     Setting::get(), o runtime cache estatico de Setting e
 *     pre-populado por reflection com os defaults documentados (e
 *     com valores especificos quando o teste exige sobreposicao).
 *     Setting::flushRuntimeCache() e chamado em tearDown para
 *     evitar vazamento entre testes.
 *
 *   - Os arquivos de teste seguem o padrao do driver `daily` do
 *     Laravel (`{prefix}-YYYY-MM-DD.log`) cuja idade e detectada
 *     primariamente pelo nome em LogsCleanup::getAgeDays(). O
 *     mtime tambem e sincronizado por defesa em profundidade.
 *
 * Validates: Requirements 10.2, 10.3, 10.5, 10.6, 10.7
 */

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\LogsCleanup;
use App\Models\Setting;
use App\Support\CleanupResult;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Tests\TestCase;

class LogsCleanupTest extends TestCase
{
    /** Diretorio temporario usado como storage path da Application. */
    private string $tempStoragePath;

    /** $tempStoragePath . '/logs' - varrido por LogsCleanup::cleanup(). */
    private string $logsDir;

    /** Storage path original da Application, restaurado em tearDown. */
    private string $originalStoragePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalStoragePath = $this->app->storagePath();

        $this->tempStoragePath = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'unn_logs_cleanup_test_'
            . uniqid('', true);
        @mkdir($this->tempStoragePath, 0775, true);

        $this->logsDir = $this->tempStoragePath . DIRECTORY_SEPARATOR . 'logs';
        @mkdir($this->logsDir, 0775, true);

        // Redireciona storage_path('logs') para o diretorio temporario.
        $this->app->useStoragePath($this->tempStoragePath);

        // Pre-popula cache de Setting com defaults para que Setting::get()
        // jamais consulte o banco durante a execucao destes testes.
        $this->primeSettingCache([
            'log_retention_days'      => '30',
            'log_security_retention'  => '90',
        ]);
    }

    protected function tearDown(): void
    {
        Setting::flushRuntimeCache();
        $this->app->useStoragePath($this->originalStoragePath);
        $this->rmdirRecursive($this->tempStoragePath);

        parent::tearDown();
    }

    /* -------------------------------------------------------------- */
    /* cleanup()                                                       */
    /* -------------------------------------------------------------- */

    public function test_cleanup_returns_cleanup_result_instance(): void
    {
        $result = (new LogsCleanup())->cleanup();

        $this->assertInstanceOf(CleanupResult::class, $result);
        $this->assertSame(0, $result->filesRemoved);
        $this->assertSame(0, $result->filesCompressed);
        $this->assertSame(0, $result->bytesReclaimed);
        $this->assertSame([], $result->errors);
    }

    public function test_cleanup_returns_empty_result_when_logs_dir_does_not_exist(): void
    {
        // Remove o diretorio para exercitar o branch de saida cedo.
        @rmdir($this->logsDir);
        $this->assertDirectoryDoesNotExist($this->logsDir);

        $result = (new LogsCleanup())->cleanup();

        $this->assertInstanceOf(CleanupResult::class, $result);
        $this->assertSame(0, $result->filesRemoved);
        $this->assertSame(0, $result->filesCompressed);
        $this->assertSame(0, $result->bytesReclaimed);
        $this->assertSame([], $result->errors);
    }

    public function test_cleanup_removes_application_log_older_than_retention(): void
    {
        // Application: retencao default 30d -> arquivo de 35d deve ser removido.
        $path = $this->createLogFile('laravel-', 35);
        $size = (int) filesize($path);

        $result = (new LogsCleanup())->cleanup();

        $this->assertFileDoesNotExist($path);
        $this->assertFileDoesNotExist($path . '.gz');
        $this->assertSame(1, $result->filesRemoved);
        $this->assertSame(0, $result->filesCompressed);
        $this->assertGreaterThanOrEqual($size, $result->bytesReclaimed);
        $this->assertSame([], $result->errors);
    }

    public function test_cleanup_compresses_application_log_between_seven_days_and_retention(): void
    {
        // Application: 15d esta em (7, 30] -> deve ser comprimido.
        $path = $this->createLogFile('laravel-', 15);

        $result = (new LogsCleanup())->cleanup();

        $this->assertFileDoesNotExist($path);
        $this->assertFileExists($path . '.gz');
        $this->assertSame(0, $result->filesRemoved);
        $this->assertSame(1, $result->filesCompressed);
        $this->assertGreaterThanOrEqual(0, $result->bytesReclaimed);
        $this->assertSame([], $result->errors);
    }

    public function test_cleanup_keeps_log_younger_than_seven_days_intact(): void
    {
        $path = $this->createLogFile('laravel-', 3);
        $originalContent = (string) file_get_contents($path);

        $result = (new LogsCleanup())->cleanup();

        $this->assertFileExists($path);
        $this->assertFileDoesNotExist($path . '.gz');
        $this->assertSame($originalContent, (string) file_get_contents($path));
        $this->assertSame(0, $result->filesRemoved);
        $this->assertSame(0, $result->filesCompressed);
        $this->assertSame([], $result->errors);
    }

    public function test_cleanup_uses_waf_channel_retention_of_thirty_days(): void
    {
        // 35d > 30 -> deletado; 15d em (7, 30] -> comprimido; 3d -> intacto.
        $oldWaf      = $this->createLogFile('waf-', 35);
        $compressWaf = $this->createLogFile('waf-', 15);
        $freshWaf    = $this->createLogFile('waf-', 3);

        $result = (new LogsCleanup())->cleanup();

        $this->assertFileDoesNotExist($oldWaf);
        $this->assertFileDoesNotExist($compressWaf);
        $this->assertFileExists($compressWaf . '.gz');
        $this->assertFileExists($freshWaf);
        $this->assertFileDoesNotExist($freshWaf . '.gz');

        $this->assertSame(1, $result->filesRemoved);
        $this->assertSame(1, $result->filesCompressed);
        $this->assertSame([], $result->errors);
    }

    public function test_cleanup_uses_security_channel_retention_of_ninety_days(): void
    {
        // Security: 95d > 90 -> deletado; 50d em (7, 90] -> comprimido.
        $oldSec      = $this->createLogFile('security-', 95);
        $compressSec = $this->createLogFile('security-', 50);

        $result = (new LogsCleanup())->cleanup();

        $this->assertFileDoesNotExist($oldSec);
        $this->assertFileDoesNotExist($compressSec);
        $this->assertFileExists($compressSec . '.gz');

        $this->assertSame(1, $result->filesRemoved);
        $this->assertSame(1, $result->filesCompressed);
        $this->assertSame([], $result->errors);
    }

    public function test_security_channel_retention_overrides_global_retention(): void
    {
        // Mesmo configurando log_retention_days=30 (default), security
        // mantem 90d. Um arquivo security de 50d deve ser COMPRIMIDO,
        // enquanto um arquivo application de 50d deve ser DELETADO.
        $secPath = $this->createLogFile('security-', 50);
        $appPath = $this->createLogFile('laravel-', 50);

        $result = (new LogsCleanup())->cleanup();

        // Security: comprimido (sobrepoe global).
        $this->assertFileDoesNotExist($secPath);
        $this->assertFileExists($secPath . '.gz');

        // Application: deletado pela retencao global de 30d.
        $this->assertFileDoesNotExist($appPath);
        $this->assertFileDoesNotExist($appPath . '.gz');

        $this->assertSame(1, $result->filesRemoved);
        $this->assertSame(1, $result->filesCompressed);
        $this->assertSame([], $result->errors);
    }

    public function test_cleanup_treats_unknown_prefix_as_application_channel(): void
    {
        // Prefixo desconhecido -> canal `application` (retencao 30d).
        $path = $this->createLogFile('phpcli-', 50);

        $result = (new LogsCleanup())->cleanup();

        $this->assertFileDoesNotExist($path);
        $this->assertSame(1, $result->filesRemoved);
        $this->assertSame(0, $result->filesCompressed);
    }

    public function test_cleanup_does_not_propagate_exceptions(): void
    {
        // Mistura de cenarios validos garante que cleanup() nunca
        // propaga uma excecao, mesmo com nomes nao-padrao.
        $this->createLogFile('laravel-', 50);
        $this->createLogFile('security-', 5);
        $this->createLogFile('waf-', 15);
        file_put_contents(
            $this->logsDir . DIRECTORY_SEPARATOR . 'random-without-date.log',
            'data'
        );

        try {
            $result = (new LogsCleanup())->cleanup();
        } catch (\Throwable $e) {
            $this->fail('cleanup() NUNCA deve propagar excecao: ' . $e->getMessage());
        }

        $this->assertInstanceOf(CleanupResult::class, $result);
        $this->assertGreaterThanOrEqual(0, $result->filesRemoved);
        $this->assertGreaterThanOrEqual(0, $result->filesCompressed);
    }

    public function test_cleanup_continues_processing_when_individual_compress_fails(): void
    {
        // Estrategia portavel: cria um arquivo na faixa de compressao
        // (15d para canal `application`, A em (7, 30]) e em seguida cria
        // um DIRETORIO com o mesmo nome do destino .gz. O compress()
        // tentara `gzopen($gzPath, 'wb9')` sobre um caminho que ja existe
        // como diretorio e retornara false sem propagar excecao.
        //
        // Em paralelo, criamos outro arquivo legitimo na faixa de
        // remocao para verificar que o cleanup continua processando os
        // demais arquivos mesmo apos o erro pontual.
        $compressTargetLog = $this->createLogFile('laravel-', 15);
        $blockingDir = $compressTargetLog . '.gz';

        $this->assertTrue(
            @mkdir($blockingDir, 0775, false),
            'pre-condicao: criacao do diretorio bloqueador deve ter sucesso.'
        );

        $deletableLog = $this->createLogFile('security-', 95); // 95d > 90 -> remover

        try {
            $result = (new LogsCleanup())->cleanup();

            // O arquivo legitimo (security 95d) deve ter sido removido
            // mesmo apos a falha de compressao do outro arquivo.
            $this->assertFileDoesNotExist(
                $deletableLog,
                'cleanup() deveria continuar removendo arquivos validos apos falha em outro.'
            );
            $this->assertSame(
                1,
                $result->filesRemoved,
                'Apenas o arquivo de retencao expirada deveria contar em filesRemoved.'
            );

            // O arquivo cuja compressao falhou permanece intacto (a
            // remocao do original so ocorre apos compressao bem sucedida).
            $this->assertFileExists(
                $compressTargetLog,
                'O original nao deve ser removido quando a compressao falha.'
            );
            $this->assertSame(
                0,
                $result->filesCompressed,
                'Nenhum arquivo deveria contar como comprimido quando o destino .gz e bloqueado.'
            );

            // O fail-safe deve ter registrado a falha sem propagar.
            $this->assertNotEmpty(
                $result->errors,
                'cleanup() deveria registrar uma mensagem em errors quando a compressao falha.'
            );
            $this->assertTrue(
                $this->errorsContain($result->errors, basename($compressTargetLog)),
                'A mensagem de erro deveria mencionar o nome do arquivo cujo .gz falhou.'
            );
        } finally {
            // Limpeza explicita: remove o diretorio bloqueador para nao
            // vazar para o tearDown recursivo (que so opera em arquivos).
            if (is_dir($blockingDir)) {
                @rmdir($blockingDir);
            }
        }
    }

    /* -------------------------------------------------------------- */
    /* compress()                                                      */
    /* -------------------------------------------------------------- */

    public function test_compress_returns_false_for_missing_file(): void
    {
        $command = new LogsCleanup();

        $missing = $this->logsDir . DIRECTORY_SEPARATOR . 'does-not-exist.log';

        $this->assertFalse($command->compress($missing));
        $this->assertFileDoesNotExist($missing . '.gz');
    }

    public function test_compress_creates_gz_and_removes_original_on_success(): void
    {
        $path = $this->logsDir . DIRECTORY_SEPARATOR . 'sample.log';
        $payload = str_repeat("compress-test-line\n", 200);
        file_put_contents($path, $payload);

        $command = new LogsCleanup();
        $ok = $command->compress($path);

        $this->assertTrue($ok);
        $this->assertFileDoesNotExist($path);
        $this->assertFileExists($path . '.gz');
        $this->assertGreaterThan(0, (int) filesize($path . '.gz'));
    }

    /* -------------------------------------------------------------- */
    /* getRetentionDays()                                              */
    /* -------------------------------------------------------------- */

    public function test_get_retention_days_returns_documented_defaults(): void
    {
        $command = new LogsCleanup();

        $this->assertSame(30, $command->getRetentionDays('waf'));
        $this->assertSame(90, $command->getRetentionDays('security'));
        $this->assertSame(30, $command->getRetentionDays('application'));

        // Canal desconhecido cai no default global de 30d.
        $this->assertSame(30, $command->getRetentionDays('whatever'));
    }

    public function test_get_retention_days_security_uses_setting_when_configured(): void
    {
        $this->primeSettingCache([
            'log_retention_days'     => '30',
            'log_security_retention' => '60',
        ]);

        $this->assertSame(60, (new LogsCleanup())->getRetentionDays('security'));
    }

    public function test_get_retention_days_application_uses_setting_when_configured(): void
    {
        $this->primeSettingCache([
            'log_retention_days'     => '45',
            'log_security_retention' => '90',
        ]);

        $this->assertSame(45, (new LogsCleanup())->getRetentionDays('application'));
    }

    public function test_get_retention_days_falls_back_to_default_when_setting_zero_or_negative(): void
    {
        $this->primeSettingCache([
            'log_retention_days'     => '-5',
            'log_security_retention' => '0',
        ]);

        $command = new LogsCleanup();

        $this->assertSame(90, $command->getRetentionDays('security'));
        $this->assertSame(30, $command->getRetentionDays('application'));
    }

    public function test_get_retention_days_waf_is_fixed_regardless_of_settings(): void
    {
        // O canal WAF usa retencao fixa do design (30d), mesmo com
        // log_retention_days configurado em valor diferente.
        $this->primeSettingCache([
            'log_retention_days'     => '120',
            'log_security_retention' => '90',
        ]);

        $this->assertSame(30, (new LogsCleanup())->getRetentionDays('waf'));
    }

    /* -------------------------------------------------------------- */
    /* handle()                                                        */
    /* -------------------------------------------------------------- */

    public function test_handle_logs_final_result_with_required_fields(): void
    {
        // Cria 1 arquivo para deletar (50d application) e 1 para
        // comprimir (15d security) -> filesRemoved=1, filesCompressed=1.
        $this->createLogFile('laravel-', 50);
        $this->createLogFile('security-', 15);

        $captured = [];
        Log::shouldReceive('info')
            ->andReturnUsing(function (string $message, array $context = []) use (&$captured): void {
                $captured[] = ['message' => $message, 'context' => $context];
            });
        Log::shouldReceive('warning')->andReturnNull();
        Log::shouldReceive('error')->andReturnNull();
        Log::shouldReceive('debug')->andReturnNull();
        Log::shouldReceive('channel')->andReturnSelf();

        $exitCode = Artisan::call('logs:cleanup');

        $this->assertSame(0, $exitCode, 'logs:cleanup deveria retornar SUCCESS (0).');

        $entry = null;
        foreach ($captured as $row) {
            if (($row['message'] ?? null) === 'logs:cleanup completed') {
                $entry = $row;
                break;
            }
        }

        $this->assertNotNull(
            $entry,
            'Esperava log "logs:cleanup completed" no canal padrao.'
        );

        $this->assertArrayHasKey('files_removed', $entry['context']);
        $this->assertArrayHasKey('files_compressed', $entry['context']);
        $this->assertArrayHasKey('bytes_reclaimed', $entry['context']);
        $this->assertArrayHasKey('errors', $entry['context']);

        $this->assertSame(1, $entry['context']['files_removed']);
        $this->assertSame(1, $entry['context']['files_compressed']);
        $this->assertIsInt($entry['context']['bytes_reclaimed']);
        $this->assertGreaterThanOrEqual(0, $entry['context']['bytes_reclaimed']);
        $this->assertIsArray($entry['context']['errors']);
        $this->assertSame([], $entry['context']['errors']);
    }

    /* -------------------------------------------------------------- */
    /* Helpers                                                         */
    /* -------------------------------------------------------------- */

    /**
     * Cria um arquivo de log no diretorio temporario com nome no
     * padrao do driver `daily` (`{prefix}YYYY-MM-DD.log`) e mtime
     * sincronizado com a data, de forma que LogsCleanup::getAgeDays()
     * detecte a idade aproximada informada (a producao usa data 00:00:00
     * e mtime como fallback).
     */
    private function createLogFile(string $prefix, int $ageDays, ?string $contents = null): string
    {
        $today = new \DateTimeImmutable('today');
        $fileDate = $today->modify("-{$ageDays} days");
        $basename = $prefix . $fileDate->format('Y-m-d') . '.log';
        $path = $this->logsDir . DIRECTORY_SEPARATOR . $basename;

        $payload = $contents ?? str_repeat(
            "logs-cleanup-test-{$prefix}{$ageDays}\n",
            50
        );
        file_put_contents($path, $payload);
        @touch($path, $fileDate->getTimestamp());

        return $path;
    }

    /**
     * Pre-popula o runtime cache estatico de Setting com pares
     * chave/valor especificos e marca o cache como carregado.
     * Setting::get($key, $default) entao devolve o valor injetado
     * sem nunca consultar o banco de dados.
     *
     * @param  array<string, string>  $pairs
     */
    private function primeSettingCache(array $pairs): void
    {
        $reflection = new ReflectionClass(Setting::class);

        $cache = $reflection->getProperty('runtimeCache');
        $cache->setAccessible(true);
        $cache->setValue(null, $pairs);

        $loaded = $reflection->getProperty('runtimeCacheLoaded');
        $loaded->setAccessible(true);
        $loaded->setValue(null, true);
    }

    /**
     * Verifica se alguma das mensagens de erro contem o substring
     * informado, util para asserir que o erro registrado se refere
     * ao arquivo correto sem acoplar com a mensagem exata.
     *
     * @param  array<int, string>  $errors
     */
    private function errorsContain(array $errors, string $needle): bool
    {
        foreach ($errors as $err) {
            if (is_string($err) && str_contains($err, $needle)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remocao recursiva do diretorio temporario. Silenciosa: tearDown
     * nao deve falhar caso algum arquivo ja tenha sido removido.
     */
    private function rmdirRecursive(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path)) {
                $this->rmdirRecursive($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
