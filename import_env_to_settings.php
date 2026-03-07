<?php
// Script para importar credenciais do .env para o banco de dados (settings)
// Uso: php import_env_to_settings.php

use Illuminate\Database\Capsule\Manager as DB;

require __DIR__ . '/vendor/autoload.php';

// Carregar .env manualmente
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    echo ".env não encontrado\n";
    exit(1);
}
$env = parse_ini_file($envPath, false, INI_SCANNER_RAW);

function setSetting($key, $value) {
    $exists = DB::table('settings')->where('key', $key)->first();
    if ($exists) {
        DB::table('settings')->where('key', $key)->update(['value' => $value]);
    } else {
        DB::table('settings')->insert(['key' => $key, 'value' => $value]);
    }
}

// MercadoPago
if (!empty($env['MERCADOPAGO_ACCESS_TOKEN'])) {
    setSetting('mercadopago_access_token', $env['MERCADOPAGO_ACCESS_TOKEN']);
}
if (!empty($env['MERCADOPAGO_PUBLIC_KEY'])) {
    setSetting('mercadopago_public_key', $env['MERCADOPAGO_PUBLIC_KEY']);
}
if (!empty($env['MERCADOPAGO_CLIENT_ID'])) {
    setSetting('mercadopago_client_id', $env['MERCADOPAGO_CLIENT_ID']);
}
if (!empty($env['MERCADOPAGO_CLIENT_SECRET'])) {
    setSetting('mercadopago_client_secret', $env['MERCADOPAGO_CLIENT_SECRET']);
}

// PagSeguro
if (!empty($env['PAGSEGURO_TOKEN'])) {
    setSetting('pagseguro_token', $env['PAGSEGURO_TOKEN']);
}
if (!empty($env['PAGSEGURO_EMAIL'])) {
    setSetting('pagseguro_email', $env['PAGSEGURO_EMAIL']);
}

// Outros campos que desejar...

echo "Importação concluída.\n";
