<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

App\Models\Setting::set('locationiq_api_key', 'pk.ef19fd805d33c47c6befb9e18267ecd2');
echo "LocationIQ API key saved: " . App\Models\Setting::get('locationiq_api_key') . "\n";
