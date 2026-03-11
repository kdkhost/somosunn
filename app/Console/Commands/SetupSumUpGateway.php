<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class SetupSumUpGateway extends Command
{
    protected $signature   = 'sumup:setup {--key= : API Key (Personal) da SumUp}';
    protected $description = 'Configura as credenciais do gateway SumUp no banco de dados';

    public function handle(): int
    {
        $key          = $this->option('key') ?: 'sup_pk_2gjFf7oxZDmcSm6rZKOdGvyl29nEShzf1';
        $clientId     = 'cc_classic_EiJusbsGWRTQtrD0oRhd7t8wltFQJ';
        $clientSecret = 'cc_sk_classic_Wkw5FEzuFDJS7Qds7UUbfruerjlnBPoVYMgHKKlsWvQbkjZgvG';

        Setting::updateOrCreate(['key' => 'sumup_access_token'],   ['value' => $key]);
        Setting::updateOrCreate(['key' => 'sumup_client_id'],      ['value' => $clientId]);
        Setting::updateOrCreate(['key' => 'sumup_client_secret'],  ['value' => $clientSecret]);
        Setting::updateOrCreate(['key' => 'sumup_enabled'],        ['value' => '1']);
        Setting::updateOrCreate(['key' => 'sumup_env'],            ['value' => 'production']);

        $this->info('✅ SumUp configurado com sucesso!');
        $this->line('   API Key:       ' . substr($key, 0, 12) . '...');
        $this->line('   Client ID:     ' . $clientId);
        $this->line('   Client Secret: ' . substr($clientSecret, 0, 12) . '...');
        $this->line('   Ambiente:      produção');
        $this->line('   Status:        ativo');

        return Command::SUCCESS;
    }
}
