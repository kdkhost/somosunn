<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

// Tenta as chaves que parecem ser de producao
$tokens = [
    'mercadopago_prod_access_token' => Setting::get('mercadopago_prod_access_token'),
    'mercadopago_access_token' => Setting::get('mercadopago_access_token'),
];

foreach ($tokens as $key => $token) {
    if (!$token)
        continue;

    echo "Testando chave: $key (" . substr($token, 0, 15) . "...)\n";

    try {
        $response = Http::withToken($token)
            ->get("https://api.mercadopago.com/users/me/mercadopago_account/balance");

        if ($response->successful()) {
            echo "SUCESSO!\n";
            print_r($response->json());
        } else {
            echo "FALHA: " . $response->status() . " - " . $response->body() . "\n";
        }
    } catch (\Exception $e) {
        echo "ERRO: " . $e->getMessage() . "\n";
    }
    echo "-----------------------------------\n";
}
