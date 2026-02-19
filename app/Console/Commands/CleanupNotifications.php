<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove notificações com mais de 30 dias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando limpeza de notificações antigas...');

        $count = Notification::where('created_at', '<', now()->subDays(30))->delete();

        $this->info("Sucesso! {$count} notificações removidas.");
    }
}
