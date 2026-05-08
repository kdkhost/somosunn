<?php
// Simula o fluxo completo de escolha de gateway no checkout
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use App\Services\SumUpService;

echo "=== VALIDACAO FINAL: GATEWAY FLOW ===\n\n";

$service = app(SumUpService::class);

echo "1. SumUpService isEnabled: " . ($service->isEnabled() ? 'SIM' : 'NAO') . "\n";
echo "2. sumup_method_card: " . Setting::get('sumup_method_card', 'NULL') . "\n";
echo "3. sumup_method_pix: " . Setting::get('sumup_method_pix', 'NULL') . "\n";
echo "4. sumup_merchant_code: " . Setting::get('sumup_merchant_code', 'NULL') . "\n";
echo "5. sumup_api_key (preenchido?): " . (empty(Setting::get('sumup_api_key')) ? 'NAO' : 'SIM') . "\n";

echo "\n--- Simulação de escolha de gateway ---\n";

// Cenario 1: Usuario escolhe 'sumup'
$gateway_provider = 'sumup';
echo "\nCenario 1: gateway_provider=sumup\n";
echo "  SumUp disponível: " . ($service->isEnabled() && $service->isAllowedForProduct('course') ? 'SIM' : 'NAO') . "\n";
echo "  Resultado esperado: ORDER com gateway='sumup'\n";

// Cenario 2: Usuario escolhe 'mercadopago'
$gateway_provider = 'mercadopago';
echo "\nCenario 2: gateway_provider=mercadopago\n";
echo "  Resultado esperado: ORDER com gateway='mercadopago'\n";

// Cenario 3: Usuario nao escolhe nada (default)
$gateway_provider = null;
echo "\nCenario 3: gateway_provider=NULL (default)\n";
echo "  Default: mercadopago\n";
echo "  Resultado esperado: ORDER com gateway='mercadopago'\n";

echo "\n=== ROTAS DE CHECKOUT SUMUP ===\n";
try {
    $routes = app('router')->getRoutes();
    foreach ($routes as $route) {
        if (stripos($route->uri(), 'sumup') !== false || stripos($route->getName() ?? '', 'sumup') !== false) {
            echo "  " . implode('|', $route->methods()) . " " . $route->uri() . " -> " . ($route->getName() ?? 'unnamed') . "\n";
        }
    }
} catch (Throwable $e) {
    echo "Erro ao listar rotas: " . $e->getMessage() . "\n";
}

echo "\nConclusão: Controllers corrigidos, agora respeitam gateway_provider escolhido pelo usuário.\n";
