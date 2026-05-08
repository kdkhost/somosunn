<?php
// Restaura métodos SumUp que voltaram a 0 e garante valores corretos
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

echo "=== RESTAURANDO METODOS SUMUP ===\n";

// Se métodos estão 0, ativar card e pix por padrão
$methodCard = (int) Setting::where('key', 'sumup_method_card')->value('value');
$methodPix  = (int) Setting::where('key', 'sumup_method_pix')->value('value');

if ($methodCard === 0 && $methodPix === 0) {
    echo "Ambos métodos SumUp estão DESATIVADOS. Ativando ambos por padrão...\n";
    Setting::updateOrCreate(['key' => 'sumup_method_card'], ['value' => '1']);
    Setting::updateOrCreate(['key' => 'sumup_method_pix'],  ['value' => '1']);
    echo "OK! Card e PIX ativados.\n";
} else {
    echo "Métodos OK: card={$methodCard}, pix={$methodPix}\n";
}

// Verificar que todas as config SumUp existem
$required = [
    'sumup_enabled' => '0',
    'sumup_env' => 'production',
    'sumup_api_key' => '',
    'sumup_merchant_code' => '',
    'sumup_client_id' => '',
    'sumup_client_secret' => '',
    'sumup_webhook_secret' => '',
    'sumup_method_card' => '1',
    'sumup_method_pix' => '1',
    'sumup_fee_percentage' => '2.75',
    'sumup_fee_fixed' => '0.00',
    'sumup_pass_fee' => '0',
    'sumup_max_installments' => '12',
    'sumup_installments_no_interest' => '1',
    'sumup_installment_tax' => '0.00',
    'sumup_interest_type' => 'per_installment',
    'sumup_pix_expiration_minutes' => '10',
    'sumup_allow_members' => '1',
    'sumup_allow_instructors' => '1',
    'sumup_allow_sellers' => '1',
    'sumup_allow_mentors' => '1',
    'sumup_allow_courses' => '1',
    'sumup_allow_mentorships' => '1',
    'sumup_allow_events' => '1',
    'sumup_allow_marketplace' => '1',
    'sumup_allow_subscriptions' => '1',
    'sumup_allow_services' => '1',
    'sumup_minimum_amount' => '0.00',
    'sumup_maximum_amount' => '0.00',
    'sumup_fallback_to_mercadopago' => '1',
];

foreach ($required as $key => $defaultValue) {
    $exists = Setting::where('key', $key)->exists();
    if (!$exists) {
        Setting::create(['key' => $key, 'value' => $defaultValue]);
        echo "Criado: $key = $defaultValue\n";
    }
}

echo "\n=== VERIFICACAO FINAL ===\n";
$all = Setting::where('key', 'like', 'sumup_%')->orderBy('key')->get();
foreach ($all as $s) {
    $v = strlen($s->value) > 40 ? substr($s->value, 0, 37) . '...' : $s->value;
    echo sprintf("%-40s = %s\n", $s->key, $v);
}
echo "\nTotal: " . $all->count() . " configurações SumUp\n";
