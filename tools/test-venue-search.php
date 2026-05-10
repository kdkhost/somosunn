<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$tomtomKey = App\Models\Setting::get('tomtom_api_key');
echo "TomTom key: " . ($tomtomKey ? '[set: ' . substr($tomtomKey, 0, 8) . '...]' : '[MISSING]') . PHP_EOL;
echo str_repeat('=', 60) . PHP_EOL;

$queries = ['kdkhost', 'eth estrategias', 'coco bambu'];

foreach ($queries as $q) {
    echo PHP_EOL . "=== Query: {$q} ===" . PHP_EOL;

    // Teste 1: sem bias de localizacao
    echo PHP_EOL . "[1] SEM lat/lon:" . PHP_EOL;
    $url1 = 'https://api.tomtom.com/search/2/search/' . rawurlencode($q) . '.json';
    $r1 = Illuminate\Support\Facades\Http::timeout(8)->get($url1, [
        'key' => $tomtomKey,
        'countrySet' => 'BR',
        'limit' => 10,
        'language' => 'pt-BR',
    ]);
    $d1 = $r1->json();
    echo "Total results: " . count($d1['results'] ?? []) . PHP_EOL;
    foreach (array_slice($d1['results'] ?? [], 0, 5) as $i => $it) {
        $name = $it['poi']['name'] ?? ($it['address']['freeformAddress'] ?? '?');
        $addr = $it['address']['freeformAddress'] ?? '';
        echo "  - {$name} | {$addr}" . PHP_EOL;
    }

    // Teste 2: com bias Rio de Janeiro + raio 150km
    echo PHP_EOL . "[2] COM lat/lon (RJ) + radius=150km:" . PHP_EOL;
    $r2 = Illuminate\Support\Facades\Http::timeout(8)->get($url1, [
        'key' => $tomtomKey,
        'countrySet' => 'BR',
        'limit' => 10,
        'language' => 'pt-BR',
        'lat' => '-23.0045',
        'lon' => '-43.3180',
        'radius' => 150000,
    ]);
    $d2 = $r2->json();
    echo "Total results: " . count($d2['results'] ?? []) . PHP_EOL;
    foreach (array_slice($d2['results'] ?? [], 0, 5) as $i => $it) {
        $name = $it['poi']['name'] ?? ($it['address']['freeformAddress'] ?? '?');
        $addr = $it['address']['freeformAddress'] ?? '';
        echo "  - {$name} | {$addr}" . PHP_EOL;
    }
}
