<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

DB::table('waf_settings')->where('key', 'waf.enabled')->update(['value' => json_encode(true)]);
echo "WAF habilitado no banco (waf.enabled = true)" . PHP_EOL;

// Testar novamente
$settings = App\Services\Waf\WafSettings::load();
echo "Verificacao: enabled = " . ($settings->enabled ? 'SIM' : 'NAO') . PHP_EOL;
