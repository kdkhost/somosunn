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
 * Sistema UNN - BackupService
 *
 * Servico responsavel por backups automaticos do banco de dados
 * (mysqldump + gzip) e dos arquivos de configuracao (.env + config/*.php
 * empacotados em tar.gz), com upload para o disco S3 (ou compativel S3,
 * como IDrive E2) e retencao automatica configuravel.
 *
 * Estrutura de paths no bucket:
 *   - backups/db/YYYY-MM-DD_HHmmss.sql.gz
 *   - backups/config/YYYY-MM-DD_HHmmss.tar.gz
 *
 * Configuracoes via tabela settings (com fallback):
 *   - backup_keep_daily   (default 30)  -> backups diarios mantidos
 *   - backup_keep_weekly  (default 12)  -> backups semanais mantidos
 *
 * Estrategia fail-safe: falhas registram log no canal stack, disparam
 * notificacao por email ao Superadmin (job na queue emails) e retornam
 * BackupResult com success = false. Nunca lancam excecao para o caller.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 7.1, 7.2, 7.3, 7.4, 7.6, 7.7, 7.8
 */

namespace App\Services;

use App\Contracts\BackupInterface;
use App\Jobs\SendGenericTemplateEmail;
use App\Models\Setting;
use App\Models\User;
use App\Support\BackupResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupService implements BackupInterface
{
    /** Diretorio S3 para backups de banco de dados. */
    public const BACKUP_DIR_DB = 'backups/db';

    /** Diretorio S3 para backups de configuracao. */
    public const BACKUP_DIR_CONFIG = 'backups/config';

    /** Subdiretorio dentro de storage/app para artefatos temporarios de backup. */
    private const TEMP_DIR = 'backup_temp';

    /** Disco remoto preferencial e disco local de contingencia. */
    private const PRIMARY_BACKUP_DISK = 's3';
    private const FALLBACK_BACKUP_DISK = 'local';

    /** Defaults de retencao (usados como fallback quando settings indisponiveis). */
    private const DEFAULT_KEEP_DAILY = 30;
    private const DEFAULT_KEEP_WEEKLY = 12;

    /** Tipos de backup suportados em listBackups(). */
    private const VALID_TYPES = ['db', 'config'];

    /** Mapeamento tipo -> diretorio S3 correspondente. */
    private const TYPE_TO_DIR = [
        'db' => self::BACKUP_DIR_DB,
        'config' => self::BACKUP_DIR_CONFIG,
    ];

    public function backupDatabase(): BackupResult
    {
        $start = microtime(true);
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $relativePath = self::BACKUP_DIR_DB . "/{$timestamp}.sql.gz";
        $tempPath = $this->makeTempPath("db_{$timestamp}.sql.gz");

        try {
            $connection = (string) config('database.default');
            $config = (array) config("database.connections.{$connection}", []);

            $this->runMysqldumpToGzip($config, $tempPath);

            $sizeBytes = $this->localSize($tempPath);
            if ($sizeBytes <= 0) {
                throw new \RuntimeException('mysqldump produziu arquivo vazio.');
            }

            $diskName = $this->storeBackupFile($tempPath, $relativePath);
            $this->safeUnlink($tempPath);

            $duration = microtime(true) - $start;

            Log::info('backup.database.success', [
                'path' => $relativePath,
                'disk' => $diskName,
                'size_bytes' => $sizeBytes,
                'duration_seconds' => round($duration, 3),
            ]);

            $this->notifySuperadminSuccess('database', $relativePath, $diskName, $sizeBytes, $duration);

            return new BackupResult(
                success: true,
                path: $relativePath,
                sizeBytes: $sizeBytes,
                durationSeconds: $duration,
            );
        } catch (Throwable $e) {
            $this->safeUnlink($tempPath);
            $duration = microtime(true) - $start;
            $message = $e->getMessage();

            Log::error('backup.database.failed', [
                'path' => $relativePath,
                'duration_seconds' => round($duration, 3),
                'exception' => $message,
            ]);

            $this->notifySuperadminFailure('database', $message, $duration, $relativePath);

            return new BackupResult(
                success: false,
                path: null,
                sizeBytes: 0,
                durationSeconds: $duration,
                error: $message,
            );
        }
    }

    public function backupConfig(): BackupResult
    {
        $start = microtime(true);
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $relativePath = self::BACKUP_DIR_CONFIG . "/{$timestamp}.tar.gz";
        $tarPath = $this->makeTempPath("config_{$timestamp}.tar");
        $tempPath = $tarPath . '.gz';

        try {
            $this->buildConfigArchive($tarPath, $tempPath);

            $sizeBytes = $this->localSize($tempPath);
            if ($sizeBytes <= 0) {
                throw new \RuntimeException('Empacotamento de configuracao produziu arquivo vazio.');
            }

            $diskName = $this->storeBackupFile($tempPath, $relativePath);
            $this->safeUnlink($tempPath);
            $this->safeUnlink($tarPath);

            $duration = microtime(true) - $start;

            Log::info('backup.config.success', [
                'path' => $relativePath,
                'disk' => $diskName,
                'size_bytes' => $sizeBytes,
                'duration_seconds' => round($duration, 3),
            ]);

            $this->notifySuperadminSuccess('config', $relativePath, $diskName, $sizeBytes, $duration);

            return new BackupResult(
                success: true,
                path: $relativePath,
                sizeBytes: $sizeBytes,
                durationSeconds: $duration,
            );
        } catch (Throwable $e) {
            $this->safeUnlink($tempPath);
            $this->safeUnlink($tarPath);
            $duration = microtime(true) - $start;
            $message = $e->getMessage();

            Log::error('backup.config.failed', [
                'path' => $relativePath,
                'duration_seconds' => round($duration, 3),
                'exception' => $message,
            ]);

            $this->notifySuperadminFailure('config', $message, $duration, $relativePath);

            return new BackupResult(
                success: false,
                path: null,
                sizeBytes: 0,
                durationSeconds: $duration,
                error: $message,
            );
        }
    }

    public function listBackups(string $type = 'db'): array
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            return [];
        }

        $prefix = self::TYPE_TO_DIR[$type];
        $items = [];

        foreach ($this->backupStorageDisks() as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                $paths = (array) $disk->files($prefix);

                foreach ($paths as $p) {
                    $path = (string) $p;
                    if ($path === '') {
                        continue;
                    }

                    try {
                        $size = (int) $disk->size($path);
                    } catch (Throwable $e) {
                        $size = 0;
                    }

                    try {
                        $modified = (int) $disk->lastModified($path);
                    } catch (Throwable $e) {
                        $modified = 0;
                    }

                    $items[] = [
                        'path' => $path,
                        'disk' => $diskName,
                        'size' => $size,
                        'modified' => $modified,
                    ];
                }
            } catch (Throwable $e) {
                Log::warning('backup.list.disk_failed', [
                    'type' => $type,
                    'disk' => $diskName,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        usort($items, static function (array $a, array $b): int {
            return ($b['modified'] ?? 0) <=> ($a['modified'] ?? 0);
        });

        return $items;
    }

    public function deleteOldBackups(int $keepDaily = 30, int $keepWeekly = 12): int
    {
        // Aplica configuracoes da tabela settings (sobrescreve defaults vindos via parametro
        // somente quando os parametros estao nos defaults do contrato).
        if ($keepDaily === self::DEFAULT_KEEP_DAILY) {
            $keepDaily = $this->resolvePositiveInt('backup_keep_daily', self::DEFAULT_KEEP_DAILY);
        }
        if ($keepWeekly === self::DEFAULT_KEEP_WEEKLY) {
            $keepWeekly = $this->resolvePositiveInt('backup_keep_weekly', self::DEFAULT_KEEP_WEEKLY);
        }

        $deleted = 0;
        $deleted += $this->pruneType('db', $keepDaily);
        $deleted += $this->pruneType('config', $keepWeekly);

        return $deleted;
    }

    public function getBackupSize(string $path): int
    {
        foreach ($this->backupStorageDisks() as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                if (!$disk->exists($path)) {
                    continue;
                }

                $size = $disk->size($path);

                return is_numeric($size) ? (int) $size : 0;
            } catch (Throwable $e) {
                Log::warning('backup.size.disk_failed', [
                    'path' => $path,
                    'disk' => $diskName,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        return 0;
    }

    // ------------------------------------------------------------------
    // Internos
    // ------------------------------------------------------------------

    /**
     * Garante o diretorio temporario e retorna o path absoluto para um arquivo.
     */
    private function makeTempPath(string $filename): string
    {
        $dir = storage_path('app/' . self::TEMP_DIR);

        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            // Mesmo se o mkdir falhar de forma transitoria, retornamos o path
            // esperado; a operacao seguinte que tentar gravar acusara o erro real.
        }

        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Executa `mysqldump ... | gzip` capturando stdout em $outputPath.
     *
     * Usa proc_open com pipes binarios e escapeshellarg em todos os valores
     * vindos de configuracao para evitar injecao de shell.
     *
     * @param array<string, mixed> $config Configuracao do connection ativo.
     */
    private function runMysqldumpToGzip(array $config, string $outputPath): void
    {
        $driver = (string) ($config['driver'] ?? '');
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new \RuntimeException("Driver de banco nao suportado para backup: {$driver}");
        }

        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (string) ($config['port'] ?? '3306');
        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');
        $database = (string) ($config['database'] ?? '');

        if ($database === '' || $username === '') {
            throw new \RuntimeException('Configuracao de banco incompleta (database/username vazios).');
        }

        $cmd = 'mysqldump --single-transaction --quick --no-tablespaces --routines --triggers'
            . ' -h' . escapeshellarg($host)
            . ' -P' . escapeshellarg($port)
            . ' -u' . escapeshellarg($username)
            . ($password !== '' ? ' -p' . escapeshellarg($password) : '')
            . ' ' . escapeshellarg($database)
            . ' | gzip';

        $descriptorSpec = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout (gzipped binary)
            2 => ['pipe', 'w'], // stderr
        ];

        $process = @proc_open(['/bin/sh', '-c', $cmd], $descriptorSpec, $pipes);

        // Em ambientes sem /bin/sh (Windows ou shells nao-Unix) tenta o fallback string.
        if (!is_resource($process)) {
            $process = @proc_open($cmd, $descriptorSpec, $pipes);
        }

        if (!is_resource($process)) {
            throw new \RuntimeException('Nao foi possivel iniciar o processo mysqldump.');
        }

        // Nao escrevemos nada em stdin.
        @fclose($pipes[0]);

        $out = @fopen($outputPath, 'wb');
        if ($out === false) {
            // Cleanup do processo antes de abortar.
            @fclose($pipes[1]);
            @fclose($pipes[2]);
            @proc_close($process);
            throw new \RuntimeException('Nao foi possivel abrir arquivo temporario para escrita: ' . $outputPath);
        }

        // Stream stdout em chunks de 64KB para nao estourar memoria com dumps grandes.
        while (!feof($pipes[1])) {
            $chunk = @fread($pipes[1], 65536);
            if ($chunk === false || $chunk === '') {
                break;
            }
            @fwrite($out, $chunk);
        }
        @fclose($pipes[1]);
        @fclose($out);

        $stderr = @stream_get_contents($pipes[2]);
        @fclose($pipes[2]);

        $exitCode = @proc_close($process);

        if ($exitCode !== 0) {
            $err = is_string($stderr) ? trim($stderr) : '';
            throw new \RuntimeException('mysqldump falhou (exit ' . (int) $exitCode . '): ' . ($err !== '' ? $err : 'sem mensagem'));
        }
    }

    /**
     * Cria um tar com .env e config/*.php e comprime em gzip.
     *
     * Usa PharData (extensao Phar e padrao do PHP) para ser compativel com
     * hospedagem compartilhada onde tar pode nao estar disponivel ou shell
     * pode estar restrito.
     */
    private function buildConfigArchive(string $tarPath, string $gzPath): void
    {
        $envPath = base_path('.env');
        $configDir = config_path();

        if (!class_exists('PharData')) {
            throw new \RuntimeException('Extensao PHP `phar` indisponivel para empacotar configuracao.');
        }

        // Phar nao sobrescreve por padrao; remove residuo antes.
        $this->safeUnlink($tarPath);
        $this->safeUnlink($gzPath);

        try {
            $phar = new \PharData($tarPath, 0, null, \Phar::TAR);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Falha ao criar arquivo tar de configuracao: ' . $e->getMessage());
        }

        // Adiciona .env (se existir).
        if (is_file($envPath) && is_readable($envPath)) {
            $phar->addFile($envPath, '.env');
        }

        // Adiciona todos os arquivos .php em config/ (recursivo).
        if (is_dir($configDir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($configDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iterator as $fileInfo) {
                /** @var \SplFileInfo $fileInfo */
                if (!$fileInfo->isFile()) {
                    continue;
                }
                $absolute = (string) $fileInfo->getPathname();
                $extension = strtolower((string) $fileInfo->getExtension());
                if ($extension !== 'php') {
                    continue;
                }
                $relative = 'config/' . ltrim(str_replace(
                    ['\\', $configDir],
                    ['/', ''],
                    $absolute
                ), '/');
                try {
                    $phar->addFile($absolute, $relative);
                } catch (\Throwable $e) {
                    // Continua processando demais arquivos; loga e segue.
                    Log::warning('backup.config.add_file_failed', [
                        'file' => $absolute,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Comprime o tar em gz e remove o tar nao comprimido.
        try {
            $phar->compress(\Phar::GZ);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Falha ao comprimir arquivo tar.gz de configuracao: ' . $e->getMessage());
        }

        // PharData::compress cria $tarPath.gz; o tar original pode ser removido.
        if (!is_file($gzPath)) {
            throw new \RuntimeException('Arquivo tar.gz de configuracao nao foi gerado em ' . $gzPath);
        }

        $this->safeUnlink($tarPath);
    }

    /**
     * Armazena o backup no disco remoto preferencial e usa local como contingencia.
     */
    private function storeBackupFile(string $localPath, string $relativePath): string
    {
        try {
            $this->putBackupFile(self::PRIMARY_BACKUP_DISK, $localPath, $relativePath);

            return self::PRIMARY_BACKUP_DISK;
        } catch (Throwable $e) {
            Log::warning('backup.storage.primary_failed_using_local', [
                'path' => $relativePath,
                'primary_disk' => self::PRIMARY_BACKUP_DISK,
                'fallback_disk' => self::FALLBACK_BACKUP_DISK,
                'exception' => $e->getMessage(),
            ]);
        }

        $this->putBackupFile(self::FALLBACK_BACKUP_DISK, $localPath, $relativePath);

        return self::FALLBACK_BACKUP_DISK;
    }

    private function putBackupFile(string $diskName, string $localPath, string $relativePath): void
    {
        $disk = Storage::disk($diskName);

        $stream = @fopen($localPath, 'rb');
        if ($stream === false) {
            throw new \RuntimeException('Nao foi possivel abrir arquivo local para armazenamento: ' . $localPath);
        }

        try {
            $ok = $disk->put($relativePath, $stream);
            if ($ok === false) {
                throw new \RuntimeException("Armazenamento no disco {$diskName} retornou false em {$relativePath}");
            }
        } finally {
            if (is_resource($stream)) {
                @fclose($stream);
            }
        }
    }

    /**
     * @return array<int,string>
     */
    private function backupStorageDisks(): array
    {
        return [
            self::PRIMARY_BACKUP_DISK,
            self::FALLBACK_BACKUP_DISK,
        ];
    }

    /**
     * Mantem somente os $keep arquivos mais recentes do tipo informado e
     * remove os demais. Retorna o numero de arquivos removidos.
     */
    private function pruneType(string $type, int $keep): int
    {
        if ($keep < 0) {
            $keep = 0;
        }

        $items = $this->listBackups($type);
        if (count($items) <= $keep) {
            return 0;
        }

        $toDelete = array_slice($items, $keep);
        $deleted = 0;

        foreach ($toDelete as $item) {
            $path = (string) ($item['path'] ?? '');
            if ($path === '') {
                continue;
            }
            $diskName = (string) ($item['disk'] ?? self::PRIMARY_BACKUP_DISK);
            try {
                $disk = Storage::disk($diskName);
                if ($disk->delete($path)) {
                    $deleted++;
                }
            } catch (Throwable $e) {
                Log::warning('backup.prune.delete_failed', [
                    'path' => $path,
                    'disk' => $diskName,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        Log::info('backup.prune.completed', [
            'type' => $type,
            'kept' => $keep,
            'deleted' => $deleted,
        ]);

        return $deleted;
    }

    private function notifySuperadminSuccess(
        string $type,
        string $path,
        string $diskName,
        int $sizeBytes,
        float $durationSeconds
    ): void {
        if (!$this->shouldNotifyBackupSuccess()) {
            return;
        }

        $this->notifySuperadminBackup(
            status: 'success',
            type: $type,
            path: $path,
            diskName: $diskName,
            sizeBytes: $sizeBytes,
            durationSeconds: $durationSeconds,
        );
    }

    /**
     * Notifica o Superadmin sobre falha de backup, despachando um job na queue.
     * Se a propria notificacao falhar, registra log critical.
     */
    private function notifySuperadminFailure(
        string $type,
        string $errorMessage,
        float $durationSeconds = 0.0,
        ?string $path = null
    ): void {
        $this->notifySuperadminBackup(
            status: 'failed',
            type: $type,
            path: $path,
            diskName: null,
            sizeBytes: 0,
            durationSeconds: $durationSeconds,
            errorMessage: $errorMessage,
        );
    }

    private function notifySuperadminBackup(
        string $status,
        string $type,
        ?string $path,
        ?string $diskName,
        int $sizeBytes,
        float $durationSeconds,
        ?string $errorMessage = null
    ): void {
        try {
            $superadmin = User::query()
                ->whereNotNull('email')
                ->where(function ($query): void {
                    $query->where('role', 'superadmin')
                        ->orWhere('level', 'superadmin');
                })
                ->first();

            if (!$superadmin || empty($superadmin->email)) {
                Log::warning('backup.notify.no_superadmin', [
                    'type' => $type,
                    'status' => $status,
                ]);

                return;
            }

            $appName = (string) config('app.name', 'UNN');
            $statusLabel = $this->backupStatusLabel($status);
            $subject = ($status === 'success' ? 'Backup concluido - ' : 'Backup falhou - ') . $appName;
            $when = Carbon::now()->format('d/m/Y H:i:s');

            $html = '<h2 style="margin:0 0 12px;color:#111827;">Backup ' . $this->escape($statusLabel) . '</h2>'
                . '<p style="margin:0 0 16px;color:#374151;">O sistema finalizou uma rotina de backup automatico. Veja os detalhes abaixo.</p>'
                . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                . $this->backupEmailRow('Status', $statusLabel)
                . $this->backupEmailRow('Tipo', $this->backupTypeLabel($type))
                . $this->backupEmailRow('Quando', $when)
                . $this->backupEmailRow('Disco', $diskName ?: 'nao gravado')
                . $this->backupEmailRow('Caminho', $path ?: 'nao gerado')
                . $this->backupEmailRow('Tamanho', $sizeBytes > 0 ? $this->formatBytes($sizeBytes) : '0 B')
                . $this->backupEmailRow('Duracao', number_format($durationSeconds, 2, ',', '.') . 's')
                . '</table>';

            if ($errorMessage !== null && $errorMessage !== '') {
                $html .= '<p style="margin:16px 0 6px;color:#991b1b;"><strong>Mensagem do erro:</strong></p>'
                    . '<pre style="white-space:pre-wrap;background:#fef2f2;color:#7f1d1d;border:1px solid #fecaca;border-radius:6px;padding:12px;">'
                    . $this->escape($errorMessage)
                    . '</pre>';
            }

            if ($status !== 'success') {
                $html .= '<p style="margin:16px 0 0;color:#374151;">Verifique os logs do Laravel e a configuracao do armazenamento de backup.</p>';
            }

            SendGenericTemplateEmail::dispatch((string) $superadmin->email, $subject, $html);
        } catch (Throwable $e) {
            Log::critical('backup.notify.failed', [
                'type' => $type,
                'status' => $status,
                'original_error' => $errorMessage,
                'notify_exception' => $e->getMessage(),
            ]);
        }
    }

    private function backupEmailRow(string $label, string $value): string
    {
        return '<tr>'
            . '<td style="padding:8px 10px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;font-weight:700;width:160px;">'
            . $this->escape($label)
            . '</td>'
            . '<td style="padding:8px 10px;border:1px solid #e5e7eb;color:#111827;">'
            . $this->escape($value)
            . '</td>'
            . '</tr>';
    }

    private function backupStatusLabel(string $status): string
    {
        return $status === 'success' ? 'sucesso' : 'falha';
    }

    private function backupTypeLabel(string $type): string
    {
        return match ($type) {
            'database' => 'banco de dados',
            'config' => 'configuracoes',
            default => $type,
        };
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $size = $bytes / 1024;

        foreach ($units as $unit) {
            if ($size < 1024 || $unit === 'TB') {
                return number_format($size, 2, ',', '.') . ' ' . $unit;
            }
            $size /= 1024;
        }

        return $bytes . ' B';
    }

    private function shouldNotifyBackupSuccess(): bool
    {
        try {
            $value = Setting::get('backup_notify_success', true);
        } catch (Throwable $e) {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return ((int) $value) === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'sim', 'yes', 'on'], true);
        }

        return true;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Le um valor inteiro positivo da tabela settings com fallback.
     */
    private function resolvePositiveInt(string $key, int $fallback): int
    {
        try {
            $value = Setting::get($key, $fallback);
        } catch (Throwable $e) {
            return $fallback;
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

    /**
     * filesize() seguro: retorna 0 em caso de stat inacessivel.
     */
    private function localSize(string $path): int
    {
        if (!is_file($path)) {
            return 0;
        }
        $size = @filesize($path);

        return is_int($size) && $size > 0 ? $size : 0;
    }

    private function safeUnlink(string $path): void
    {
        if ($path !== '' && is_file($path)) {
            @unlink($path);
        }
    }
}
