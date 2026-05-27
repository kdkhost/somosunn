<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$events = DB::table('events')->whereNotNull('image')->where('image', '!=', '')->get(['id', 'title', 'image']);
echo "Eventos com imagem no banco: " . $events->count() . "\n\n";
foreach ($events as $e) {
    $shortTitle = mb_substr($e->title, 0, 50);
    echo "  #{$e->id} \"{$shortTitle}\"\n";
    echo "    path: {$e->image}\n";
}
