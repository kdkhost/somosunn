<?php

namespace App\Console\Commands;

use App\Support\EmailQueueSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ProcessEmailQueue extends Command
{
    protected $signature = 'emails:process-queue';

    protected $description = 'Processa a fila de e-mails usando as configuracoes SMTP/fila do painel';

    public function handle(): int
    {
        if (!EmailQueueSettings::shouldQueue()) {
            $this->info('Fila de e-mails desativada: modo de envio atual e sincrono.');

            return self::SUCCESS;
        }

        if (!EmailQueueSettings::scheduleEnabled()) {
            $this->info('Processamento agendado da fila de e-mails desativado nas configuracoes.');

            return self::SUCCESS;
        }

        $connection = EmailQueueSettings::connection();
        if ($connection === 'sync') {
            $this->info('Conexao de fila sync nao exige worker.');

            return self::SUCCESS;
        }

        $exitCode = Artisan::call('queue:work', [
            'connection' => $connection,
            '--queue' => EmailQueueSettings::queueName(),
            '--stop-when-empty' => true,
            '--tries' => EmailQueueSettings::tries(),
            '--timeout' => EmailQueueSettings::timeout(),
            '--sleep' => EmailQueueSettings::sleep(),
        ]);

        $output = trim(Artisan::output());
        if ($output !== '') {
            $this->line($output);
        }

        return $exitCode === 0 ? self::SUCCESS : self::FAILURE;
    }
}
