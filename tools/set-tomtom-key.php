<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

App\Models\Setting::set('tomtom_api_key', 'q1Jg76U7DJ0XLsILfEVsIgT6PZSt2rTk');
App\Models\Setting::set('venue_search_provider', 'auto');
echo "TomTom API key saved\n";
