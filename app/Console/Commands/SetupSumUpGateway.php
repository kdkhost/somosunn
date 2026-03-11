<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class SetupSumUpGateway extends Command
{
    protected $signature   = 'sumup:setup {--key= : API Key da SumUp}';
    protected $description = 'Configura as credenciais do gateway SumUp no banco de dados';

    public function handle(): int
    {
        $key = $this->option('key') ?: 'sup_pk_2gjFf7oxZDmcSm6rZKOdGvyl29nEShzf1';

        Setting::updateOrCreate(['key' => 'sumup_access_token'], ['value' => $key]);
        Setting::updateOrCreate(['key' => 'sumup_enabled'],      ['value' => '1']);
        Setting::updateOrCreate(['key' => 'sumup_env'],          ['value' => 'production']);

        $this->info('✅ SumUp configurado com sucesso!');
        $this->line("   API Key: " . substr($key, 0, 12) . '...');
        $this->line("   Ambiente: produção");
        $this->line("   Status: ativo");

        return Command::SUCCESS;
    }
}
