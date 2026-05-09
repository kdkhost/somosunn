<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COLUNAS MENTORSHIPS ===\n";
$cols = \Schema::getColumnListing('mentorships');
echo implode(', ', $cols) . "\n";
echo "Tem 'status'? " . (in_array('status', $cols) ? 'SIM' : 'NAO') . "\n\n";

echo "=== SCHEDULED_TASKS ATIVAS ===\n";
if (\Schema::hasTable('scheduled_tasks')) {
    $tasks = \DB::table('scheduled_tasks')->where('active', 1)->get(['id', 'command', 'frequency']);
    foreach ($tasks as $t) {
        echo "  [{$t->id}] {$t->command} | {$t->frequency}\n";
    }
} else {
    echo "Tabela scheduled_tasks nao existe\n";
}

echo "\n=== QUERIES COM 'status' EM MENTORSHIPS ===\n";
$files = glob(__DIR__ . '/../app/**/*.php') + glob(__DIR__ . '/../app/**/**/*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (stripos($content, 'mentorship') !== false && stripos($content, "status") !== false && stripos($content, "where") !== false) {
        // Procurar linhas com where status em mentorships
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (preg_match("/mentorship.*where.*status|where.*status.*mentorship/i", $line)) {
                echo "  " . basename($file) . ":" . ($i+1) . " -> " . trim($line) . "\n";
            }
        }
    }
}
