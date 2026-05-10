<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$m = App\Models\Magazine::latest()->first();
if (!$m) { echo "No magazines found\n"; exit; }
echo "id: " . $m->id . PHP_EOL;
echo "title: " . $m->title . PHP_EOL;
echo "pdf_file (DB): " . $m->pdf_file . PHP_EOL;
echo "pdf_url (accessor): " . $m->pdf_url . PHP_EOL;
echo "thumbnail (DB): " . $m->thumbnail . PHP_EOL;
echo "thumbnail_url: " . $m->thumbnail_url . PHP_EOL;

// Check if file exists on disk
$path = public_path($m->pdf_file);
echo "public_path: " . $path . PHP_EOL;
echo "exists at public_path: " . (file_exists($path) ? 'YES' : 'NO') . PHP_EOL;

$storagePath = storage_path('app/public/' . $m->pdf_file);
echo "storage_path: " . $storagePath . PHP_EOL;
echo "exists at storage: " . (file_exists($storagePath) ? 'YES' : 'NO') . PHP_EOL;

// Check UploadStorage::url
echo "UploadStorage::url: " . App\Support\UploadStorage::url($m->pdf_file) . PHP_EOL;
