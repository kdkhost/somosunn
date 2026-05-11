<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Total: " . App\Models\Magazine::count() . PHP_EOL;
foreach (App\Models\Magazine::orderBy('id')->get() as $m) {
    echo sprintf("  #%d - %s (%s MB) status=%s\n",
        $m->id, $m->title, number_format(($m->file_size_kb ?? 0) / 1024, 1), $m->status);
}
