<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PONTOS RULES ===\n";
$rules = App\Models\PointsRule::orderBy('category')->orderBy('sort_order')->get();
foreach ($rules as $r) {
    echo sprintf("%-35s | %4d pts | cat:%-12s | rep:%-3s | max_daily:%s\n",
        $r->key, $r->points, $r->category ?? '—', $r->repeatable ? 'sim' : 'nao', $r->max_daily ?? '∞');
}

echo "\n=== POINTS LOG (últimos 20) ===\n";
$logs = App\Models\PointsLog::with('user')->latest()->take(20)->get();
foreach ($logs as $l) {
    echo sprintf("user:%s | key:%-30s | %+d pts\n", optional($l->user)->name ?? '?', $l->action_key, $l->points);
}
