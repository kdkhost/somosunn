<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;
use App\Support\UploadStorage;

$event = Event::whereNotNull('image')->first();
if (!$event) {
    echo "NENHUM evento com imagem encontrado.\n";
    $event = Event::first();
    if ($event) {
        echo "Mas existe evento #{$event->id}: image='" . ($event->image ?? 'NULL') . "'\n";
    }
    exit;
}

echo "=== DIAGNOSTICO IMAGEM EVENTO ===\n\n";
echo "Evento ID: {$event->id}\n";
echo "Titulo: {$event->title}\n";
echo "Image (DB): " . ($event->image ?? 'NULL') . "\n\n";

$path = $event->image;
$normalized = UploadStorage::normalizePath($path);
echo "Normalized: " . ($normalized ?? 'NULL') . "\n\n";

echo "--- UploadStorage::exists() ---\n";
$exists = UploadStorage::exists($path);
echo "Exists: " . ($exists ? 'SIM' : 'NAO') . "\n\n";

echo "--- publicCandidates ---\n";
$ref = new ReflectionMethod(UploadStorage::class, 'publicCandidates');
$ref->setAccessible(true);
$candidates = $ref->invoke(null, $normalized);
foreach ($candidates as $c) {
    echo "  $c => " . (is_file($c) ? 'EXISTE' : 'AUSENTE') . "\n";
}

echo "\n--- URL gerada ---\n";
$url = UploadStorage::url($path);
echo "URL: " . ($url ?? 'NULL') . "\n";

echo "\n--- Disco efetivo ---\n";
echo "effectiveDisk: " . UploadStorage::effectiveDisk() . "\n";
echo "isLocal: " . (UploadStorage::isLocal() ? 'SIM' : 'NAO') . "\n";

echo "\n--- configured_root ---\n";
$root = config('filesystems.disks.public.root', 'default');
echo "public.root: " . ($root ?? 'NULL') . "\n";
echo "public_path(storage) is_dir: " . (is_dir(public_path('storage')) ? 'SIM (symlink/dir)' : 'NAO') . "\n";

echo "\n--- storage_path(app/public) ---\n";
$sp = storage_path('app/public');
echo "$sp => " . (is_dir($sp) ? 'DIR' : 'NAO EXISTE') . "\n";

echo "\n--- Diretorios com arquivos ---\n";
function countFiles($dir) {
    if (!is_dir($dir)) return 0;
    $count = 0;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($it as $f) { if ($f->isFile()) $count++; }
    return $count;
}
$dirs = [
    'public/storage' => public_path('storage'),
    'storage/app/public' => storage_path('app/public'),
    'public/uploads' => public_path('uploads'),
];
foreach ($dirs as $label => $dir) {
    $files = countFiles($dir);
    echo "  $label ($dir): $files arquivos\n";
}
