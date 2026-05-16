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
 * Sistema UNN - BackupConfigCommand
 *
 * Comando Artisan responsavel por executar o backup semanal dos
 * arquivos de configuracao (.env + config/*.php empacotados em tar.gz)
 * e enviar para o disco S3.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 7.5
 */

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupConfigCommand extends Command
{
    protected $signature = 'backup:config';

    protected $description = 'Backup de arquivos de configuracao para S3';

    public function handle(BackupService $service): int
    {
        $result = $service->backupConfig();

        if ($result->success) {
            $this->info("Backup OK: {$result->path}");

            return Command::SUCCESS;
        }

        $this->error("Backup falhou: {$result->error}");

        return Command::FAILURE;
    }
}
