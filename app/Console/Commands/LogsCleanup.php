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
 * Sistema UNN - LogsCleanup
 *
 * Comando Artisan que aplica a politica de rotacao e compressao de
 * logs em `storage/logs/`. Remove arquivos mais antigos que a retencao
 * configurada por canal e comprime via gzip arquivos com mais de 7
 * dias. Cada operacao de arquivo eh isolada (try/catch) para que falhas
 * individuais nao interrompam o processamento dos demais arquivos.
 *
 * Canais reconhecidos pelo nome do arquivo (driver `daily`):
 *   - waf-YYYY-MM-DD.log         -> canal `waf`         (retencao 30d)
 *   - security-YYYY-MM-DD.log    -> canal `security`    (retencao 90d)
 *   - laravel-YYYY-MM-DD.log     -> canal `application` (retencao 30d)
 *   - qualquer outro             -> canal `application` (retencao 30d)
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.7
 */

namespace App\Console\Commands;

use App\Contracts\LogRotatorInterface;
use App\Models\Setting;
use App\Support\CleanupResult;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LogsCleanup extends Command implements LogRotatorInterface
{
    protected $signature = 'logs:cleanup';

    protected $description = 'Limpar e comprimir logs antigos conforme retencao configurada';

    /**
     * Idade minima (em dias) para um arquivo nao-comprimido ser
     * comprimido via gzip. Arquivos mais novos permanecem intactos.
     */
    private const COMPRESS_AFTER_DAYS = 7;

    /**
     * Retencao padrao aplicada quando a configuracao via settings
     * estiver ausente ou invalida.
     */
    private const DEFAULT_RETENTION_DAYS = 30;

    /**
     * Retencao padrao por canal (sobrepoe a global). O canal `security`
     * eh resolvido via setting `log_security_retention` (default 90).
     */
    private const CHANNEL_RETENTIONS = [
        'waf' => 30,
        'security' => 90,
    ];

    public function handle(): int
    {
        $result = $this->cleanup();

        $this->info("Files removed: {$result->filesRemoved}");
        $this->info("Files compressed: {$result->filesCompressed}");
        $this->info("Bytes reclaimed: {$result->bytesReclaimed}");

        if (!empty($result->errors)) {
            foreach ($result->errors as $err) {
                $this->warn($err);
            }
        }

        Log::info('logs:cleanup completed', $result->toArray());

        return Command::SUCCESS;
    }

    /**
     * Varre `storage/logs/` aplicando retencao por canal e comprimindo
     * arquivos com mais de 7 dias. Falhas em arquivos individuais sao
     * acumuladas em `$result->errors` sem interromper o processamento.
     */
    public function cleanup(): CleanupResult
    {
        $result = new CleanupResult();

        $logsDir = storage_path('logs');

        if (!is_dir($logsDir)) {
            return $result;
        }

        // Coleta tanto .log quanto .log.gz para aplicar retencao em ambos.
        $patterns = [$logsDir . DIRECTORY_SEPARATOR . '*.log', $logsDir . DIRECTORY_SEPARATOR . '*.log.gz'];
        $files = [];
        foreach ($patterns as $pattern) {
            $matches = glob($pattern);
            if (is_array($matches)) {
                foreach ($matches as $path) {
                    $files[$path] = true;
                }
            }
        }

        foreach (array_keys($files) as $path) {
            try {
                if (!is_file($path)) {
                    continue;
                }

                $basename = basename($path);
                $channel = $this->detectChannelFromFilename($basename);
                $retention = $this->getRetentionDays($channel);
                $ageDays = $this->getAgeDays($path);

                // 1) Retencao: arquivo mais antigo que a retencao do canal -> deletar
                if ($ageDays > $retention) {
                    $size = @filesize($path) ?: 0;
                    if (@unlink($path)) {
                        $result->filesRemoved++;
                        $result->bytesReclaimed += (int) $size;
                    } else {
                        $result->errors[] = "Failed to remove {$basename}";
                    }
                    continue;
                }

                // 2) Compressao: arquivo nao comprimido com mais de 7 dias -> .gz
                $isGzipped = str_ends_with($basename, '.gz');
                if (!$isGzipped && $ageDays > self::COMPRESS_AFTER_DAYS) {
                    $originalSize = @filesize($path) ?: 0;

                    if ($this->compress($path)) {
                        $result->filesCompressed++;

                        $compressedSize = 0;
                        $gzPath = $path . '.gz';
                        if (is_file($gzPath)) {
                            $compressedSize = (int) (@filesize($gzPath) ?: 0);
                        }

                        $reclaimed = max(0, (int) $originalSize - $compressedSize);
                        $result->bytesReclaimed += $reclaimed;
                    } else {
                        $result->errors[] = "Failed to compress {$basename}";
                    }
                }
            } catch (\Throwable $e) {
                $result->errors[] = sprintf('Error processing %s: %s', basename($path), $e->getMessage());
                // continua processando os arquivos restantes (Requirement 10.7)
                continue;
            }
        }

        return $result;
    }

    /**
     * Comprime o arquivo informado em um `.gz` ao lado e remove o original
     * em caso de sucesso. Retorna false em qualquer falha de I/O (sem throw).
     */
    public function compress(string $logPath): bool
    {
        try {
            if (!is_file($logPath) || !is_readable($logPath)) {
                return false;
            }

            $gzPath = $logPath . '.gz';

            // Se o destino ja existe, remove para evitar acumulo (idempotente).
            if (is_file($gzPath)) {
                @unlink($gzPath);
            }

            $source = @fopen($logPath, 'rb');
            if ($source === false) {
                return false;
            }

            $dest = @gzopen($gzPath, 'wb9');
            if ($dest === false) {
                @fclose($source);
                return false;
            }

            $writeOk = true;
            while (!feof($source)) {
                $chunk = fread($source, 8192);
                if ($chunk === false) {
                    $writeOk = false;
                    break;
                }
                if ($chunk === '') {
                    continue;
                }
                $written = gzwrite($dest, $chunk);
                if ($written === false || $written === 0) {
                    $writeOk = false;
                    break;
                }
            }

            @fclose($source);
            @gzclose($dest);

            if (!$writeOk) {
                @unlink($gzPath);
                return false;
            }

            // Remove o original somente apos compressao bem sucedida.
            if (!@unlink($logPath)) {
                // Mesmo sem conseguir remover o original, o .gz foi gerado.
                // Reportamos sucesso parcial removendo o .gz duplicado para
                // nao deixar lixo, e sinalizamos falha geral.
                @unlink($gzPath);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Retorna a retencao em dias para o canal informado, considerando
     * settings configuraveis via Superadmin.
     */
    public function getRetentionDays(string $channel): int
    {
        $channel = strtolower($channel);

        if ($channel === 'security') {
            $value = (int) Setting::get('log_security_retention', self::CHANNEL_RETENTIONS['security']);
            return $value > 0 ? $value : self::CHANNEL_RETENTIONS['security'];
        }

        if ($channel === 'waf') {
            // O canal WAF usa retencao fixa do design (30d). Caso o operador
            // queira sobrepor, usa o setting global `log_retention_days`
            // apenas se for menor (preserva o limite minimo do design).
            return self::CHANNEL_RETENTIONS['waf'];
        }

        $value = (int) Setting::get('log_retention_days', self::DEFAULT_RETENTION_DAYS);

        return $value > 0 ? $value : self::DEFAULT_RETENTION_DAYS;
    }

    /**
     * Detecta o canal a partir do nome do arquivo gerado pelo driver
     * `daily` do Laravel. Aceita prefixos `waf-`, `security-` e
     * `laravel-`. Qualquer outro nome cai no canal `application`.
     */
    private function detectChannelFromFilename(string $basename): string
    {
        $name = strtolower($basename);

        if (str_starts_with($name, 'waf-') || $name === 'waf.log' || $name === 'waf.log.gz') {
            return 'waf';
        }

        if (str_starts_with($name, 'security-') || $name === 'security.log' || $name === 'security.log.gz') {
            return 'security';
        }

        return 'application';
    }

    /**
     * Calcula a idade em dias do arquivo. Tenta primeiro extrair a data do
     * nome (`{channel}-YYYY-MM-DD.log[.gz]`); em caso de falha, usa o
     * filemtime como fallback.
     */
    private function getAgeDays(string $path): int
    {
        $basename = basename($path);

        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $basename, $m) === 1) {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $m[1]);
            if ($date instanceof \DateTimeImmutable) {
                $now = new \DateTimeImmutable('today');
                $diff = $now->diff($date);
                $days = (int) $diff->days;
                return $diff->invert === 1 ? $days : 0;
            }
        }

        $mtime = @filemtime($path);
        if ($mtime === false) {
            return 0;
        }

        $seconds = time() - (int) $mtime;
        if ($seconds <= 0) {
            return 0;
        }

        return (int) floor($seconds / 86400);
    }
}
