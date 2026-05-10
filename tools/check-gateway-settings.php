<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$keys = [
    'mercadopago_enabled',
    'sumup_enabled',
    'mercadopago_allow_subscriptions',
    'sumup_allow_subscriptions',
    'mercadopago_public_key',
    'mercadopago_access_token',
    'mercadopago_prod_public_key',
    'mercadopago_prod_access_token',
    'mercadopago_sandbox_public_key',
    'mercadopago_sandbox_access_token',
    'mercadopago_env',
    'sumup_api_key',
    'sumup_merchant_code',
];
foreach ($keys as $k2) {
    $val = \App\Models\Setting::get($k2, '(missing)');
    if (strlen((string) $val) > 40) {
        $val = substr((string) $val, 0, 20) . '... [' . strlen($val) . ' chars]';
    }
    echo $k2 . ' = ' . ($val === '' ? '(empty)' : $val) . PHP_EOL;
}
