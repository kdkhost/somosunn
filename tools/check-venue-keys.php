<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "TomTom: " . (App\Models\Setting::get('tomtom_api_key') ? 'OK (' . substr(App\Models\Setting::get('tomtom_api_key'), 0, 8) . '...)' : 'VAZIO') . PHP_EOL;
echo "Google: " . (App\Models\Setting::get('google_places_api_key') ? 'OK' : 'VAZIO') . PHP_EOL;
echo "LocationIQ: " . (App\Models\Setting::get('locationiq_api_key') ? 'OK' : 'VAZIO') . PHP_EOL;
echo "Provider: " . (App\Models\Setting::get('venue_search_provider') ?: 'auto') . PHP_EOL;
