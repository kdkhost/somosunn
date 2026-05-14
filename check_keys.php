<?php
require_once 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$keys = ['tomtom_api_key', 'locationiq_api_key', 'google_places_api_key', 'venue_search_provider'];
foreach ($keys as $k) {
    $v = App\Models\Setting::get($k, '(vazio)');
    echo "$k = $v\n";
}
