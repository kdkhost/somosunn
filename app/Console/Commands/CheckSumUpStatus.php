<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\SumUpService;
use Illuminate\Console\Command;

class CheckSumUpStatus extends Command
{
    protected $signature = 'sumup:status {--enable : Enable SumUp} {--disable : Disable SumUp}';
    protected $description = 'Check SumUp configuration status and optionally enable/disable it';

    public function handle()
    {
        $this->info('=== SumUp Configuration Status ===');
        
        // Verificar configurações básicas
        $enabled = Setting::get('sumup_enabled', 0);
        $apiKey = Setting::get('sumup_api_key', '');
        $merchantCode = Setting::get('sumup_merchant_code', '');
        $env = Setting::get('sumup_env', 'sandbox');
        
        $this->info("Status: " . ($enabled ? 'ENABLED' : 'DISABLED'));
        $this->info("Environment: " . strtoupper($env));
        $this->info("API Key: " . ($apiKey ? 'SET (' . substr($apiKey, 0, 10) . '...)' : 'NOT SET'));
        $this->info("Merchant Code: " . ($merchantCode ?: 'NOT SET'));
        
        // Verificar métodos de pagamento
        $cardEnabled = Setting::get('sumup_method_card', 1);
        $pixEnabled = Setting::get('sumup_method_pix', 1);
        
        $this->info("\n=== Payment Methods ===");
        $this->info("Card: " . ($cardEnabled ? 'ENABLED' : 'DISABLED'));
        $this->info("PIX: " . ($pixEnabled ? 'ENABLED' : 'DISABLED'));
        
        // Verificar permissões
        $this->info("\n=== User Permissions ===");
        $this->info("Members: " . (Setting::get('sumup_allow_members', 1) ? 'ALLOWED' : 'BLOCKED'));
        $this->info("Instructors: " . (Setting::get('sumup_allow_instructors', 1) ? 'ALLOWED' : 'BLOCKED'));
        $this->info("Sellers: " . (Setting::get('sumup_allow_sellers', 1) ? 'ALLOWED' : 'BLOCKED'));
        $this->info("Mentors: " . (Setting::get('sumup_allow_mentors', 1) ? 'ALLOWED' : 'BLOCKED'));
        
        $this->info("\n=== Product Permissions ===");
        $this->info("Courses: " . (Setting::get('sumup_allow_courses', 1) ? 'ALLOWED' : 'BLOCKED'));
        $this->info("Mentorships: " . (Setting::get('sumup_allow_mentorships', 1) ? 'ALLOWED' : 'BLOCKED'));
        $this->info("Events: " . (Setting::get('sumup_allow_events', 1) ? 'ALLOWED' : 'BLOCKED'));
        $this->info("Marketplace: " . (Setting::get('sumup_allow_marketplace', 1) ? 'ALLOWED' : 'BLOCKED'));
        
        // Opções de enable/disable
        if ($this->option('enable')) {
            Setting::updateOrCreate(['key' => 'sumup_enabled'], ['value' => '1']);
            $this->info("\n✅ SumUp has been ENABLED");
        }
        
        if ($this->option('disable')) {
            Setting::updateOrCreate(['key' => 'sumup_enabled'], ['value' => '0']);
            $this->info("\n❌ SumUp has been DISABLED");
        }
        
        // Testar conexão se habilitado e configurado
        if ($enabled && $apiKey && $merchantCode) {
            $this->info("\n=== Connection Test ===");
            try {
                $sumupService = app(SumUpService::class);
                $result = $sumupService->testConnection();
                
                if ($result['success']) {
                    $this->info("✅ " . $result['message']);
                } else {
                    $this->error("❌ " . $result['message']);
                }
            } catch (\Exception $e) {
                $this->error("❌ Connection test failed: " . $e->getMessage());
            }
        } else {
            $this->warn("\n⚠️  Cannot test connection: SumUp is not fully configured");
            if (!$enabled) $this->warn("   - SumUp is disabled");
            if (!$apiKey) $this->warn("   - API Key is not set");
            if (!$merchantCode) $this->warn("   - Merchant Code is not set");
        }
        
        $this->info("\n=== Next Steps ===");
        if (!$enabled) {
            $this->info("1. Enable SumUp: php artisan sumup:status --enable");
        }
        if (!$apiKey || !$merchantCode) {
            $this->info("2. Configure credentials in admin panel: /panel/admin/settings?group=gateway");
        }
        
        return 0;
    }
}
