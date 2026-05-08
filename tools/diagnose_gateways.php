<?php
// Diagnóstico completo dos gateways
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSTICO GATEWAYS ===\n\n";

echo "--- MERCADOPAGO ---\n";
$mp = Setting::where('key', 'like', 'mercadopago_%')->orderBy('key')->get();
foreach ($mp as $s) {
    $val = strlen($s->value) > 50 ? substr($s->value, 0, 47) . '...' : $s->value;
    echo sprintf("%-45s = %s\n", $s->key, $val);
}

echo "\n--- SUMUP ---\n";
$sumup = Setting::where('key', 'like', 'sumup_%')->orderBy('key')->get();
foreach ($sumup as $s) {
    $val = strlen($s->value) > 50 ? substr($s->value, 0, 47) . '...' : $s->value;
    echo sprintf("%-45s = %s\n", $s->key, $val);
}

echo "\n--- GATEWAY GENERAL ---\n";
$gw = Setting::where('key', 'like', 'gateway_%')->orderBy('key')->get();
foreach ($gw as $s) {
    $val = strlen($s->value) > 50 ? substr($s->value, 0, 47) . '...' : $s->value;
    echo sprintf("%-45s = %s\n", $s->key, $val);
}

echo "\nTotal MP: " . $mp->count() . " | SumUp: " . $sumup->count() . " | Gateway: " . $gw->count() . "\n";
