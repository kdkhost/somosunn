<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

echo "TomTom key: " . (App\Models\Setting::get('tomtom_api_key') ? '[set: ' . substr(App\Models\Setting::get('tomtom_api_key'), 0, 8) . '...]' : '[MISSING]') . PHP_EOL;
echo "Google key: " . (App\Models\Setting::get('google_places_api_key') ? '[set]' : '[empty]') . PHP_EOL;
echo "LocationIQ key: " . (App\Models\Setting::get('locationiq_api_key') ? '[set]' : '[empty]') . PHP_EOL;
echo "Provider: " . App\Models\Setting::get('venue_search_provider', 'auto') . PHP_EOL;
echo "Radius (km): " . App\Models\Setting::get('venue_search_radius_km', 150) . PHP_EOL;
echo str_repeat('=', 60) . PHP_EOL;

$queries = ['kdkhost', 'eth estrategias', 'coco bambu barra'];
$controller = new App\Http\Controllers\Api\VenueSearchController();

foreach ($queries as $q) {
    echo PHP_EOL . ">>> Query: {$q}" . PHP_EOL;
    // Usar lat/lon do Rio de Janeiro (Barra da Tijuca) para simular o usuario
    $request = new Illuminate\Http\Request(['q' => $q, 'lat' => '-23.0045', 'lon' => '-43.3180']);
    $response = $controller->search($request);
    $data = json_decode($response->getContent(), true);
    echo "Provider used: " . ($data['provider'] ?? 'none') . PHP_EOL;
    echo "Results: " . count($data['results'] ?? []) . PHP_EOL;
    foreach (array_slice($data['results'] ?? [], 0, 5) as $i => $r) {
        echo sprintf("  %d. %s | %s (%s,%s)\n", $i+1, $r['name'], $r['address'], $r['lat'], $r['lng']);
    }
}
