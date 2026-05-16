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
 * Sistema UNN - BackupDatabaseCommand
 *
 * Comando Artisan responsavel por executar o backup diario do banco
 * de dados (mysqldump + gzip), enviar para o disco S3 e aplicar a
 * politica de retencao configurada.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 7.5
 */

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Executar backup do banco de dados e enviar para S3';

    public function handle(BackupService $service): int
    {
        $result = $service->backupDatabase();

        if ($result->success) {
            $this->info("Backup OK: {$result->path} ({$result->sizeBytes} bytes, {$result->durationSeconds}s)");

            // Aplica politica de retencao apos backup bem-sucedido.
            $deleted = $service->deleteOldBackups();
            $this->info("Retencao: {$deleted} backups antigos removidos");

            return Command::SUCCESS;
        }

        $this->error("Backup falhou: {$result->error}");

        return Command::FAILURE;
    }
}
