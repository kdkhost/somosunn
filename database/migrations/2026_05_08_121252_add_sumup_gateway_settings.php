<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar configurações do SumUp
        $sumupSettings = [
            // Configurações básicas
            'sumup_enabled' => '0',
            'sumup_env' => 'sandbox',
            'sumup_api_key' => '',
            'sumup_merchant_code' => '',
            'sumup_client_id' => '',
            'sumup_client_secret' => '',
            'sumup_webhook_secret' => '',
            
            // Métodos de pagamento
            'sumup_method_card' => '1',
            'sumup_method_pix' => '1',
            
            // Taxas e cobrança
            'sumup_fee_percentage' => '2.75',
            'sumup_fee_fixed' => '0.00',
            'sumup_pass_fee' => '0',
            'sumup_max_installments' => '12',
            'sumup_installments_no_interest' => '1',
            'sumup_installment_tax' => '0.00',
            'sumup_interest_type' => 'per_installment',
            'sumup_pix_expiration_minutes' => '10',
            
            // Permissões por nível de usuário
            'sumup_allow_members' => '1',
            'sumup_allow_instructors' => '1',
            'sumup_allow_sellers' => '1',
            'sumup_allow_mentors' => '1',
            
            // Permissões por tipo de produto/serviço
            'sumup_allow_courses' => '1',
            'sumup_allow_mentorships' => '1',
            'sumup_allow_events' => '1',
            'sumup_allow_marketplace' => '1',
            'sumup_allow_subscriptions' => '1',
            'sumup_allow_services' => '1',
            
            // Configurações avançadas
            'sumup_minimum_amount' => '0.00',
            'sumup_maximum_amount' => '0.00',
            'sumup_fallback_to_mercadopago' => '1',
        ];

        foreach ($sumupSettings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover configurações do SumUp
        $sumupKeys = [
            'sumup_enabled', 'sumup_env', 'sumup_api_key', 'sumup_merchant_code',
            'sumup_client_id', 'sumup_client_secret', 'sumup_webhook_secret',
            'sumup_method_card', 'sumup_method_pix',
            'sumup_fee_percentage', 'sumup_fee_fixed', 'sumup_pass_fee',
            'sumup_max_installments', 'sumup_installments_no_interest', 'sumup_installment_tax',
            'sumup_interest_type', 'sumup_pix_expiration_minutes',
            'sumup_allow_members', 'sumup_allow_instructors', 'sumup_allow_sellers', 'sumup_allow_mentors',
            'sumup_allow_courses', 'sumup_allow_mentorships', 'sumup_allow_events',
            'sumup_allow_marketplace', 'sumup_allow_subscriptions', 'sumup_allow_services',
            'sumup_minimum_amount', 'sumup_maximum_amount', 'sumup_fallback_to_mercadopago',
        ];

        Setting::whereIn('key', $sumupKeys)->delete();
    }
};
