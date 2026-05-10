<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$slug = $argv[1] ?? 'revista-manchete-edicao-07';
$m = App\Models\Magazine::where('slug', $slug)->first();
if (!$m) { echo "NOT FOUND for slug: $slug\n"; exit(1); }

echo "id: " . $m->id . PHP_EOL;
echo "slug: " . $m->slug . PHP_EOL;
echo "pdf_file: " . $m->pdf_file . PHP_EOL;
echo "pdf_url: " . $m->pdf_url . PHP_EOL;
echo "status: " . $m->status . PHP_EOL;

// Test if file is accessible via HTTP from server itself
$url = $m->pdf_url;
$headers = @get_headers($url);
echo "HTTP check: " . ($headers ? $headers[0] : 'FAILED') . PHP_EOL;
