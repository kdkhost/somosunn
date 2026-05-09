<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== REATIVANDO SCHEDULED_TASKS ===\n";

// Reativar com nomes corretos
DB::table('scheduled_tasks')
    ->where('command', 'users:send-birthday-emails')
    ->update(['active' => 1]);
echo "Reativado: users:send-birthday-emails\n";

DB::table('scheduled_tasks')
    ->where('command', 'invoices:send-overdue-reminders')
    ->update(['active' => 1]);
echo "Reativado: invoices:send-overdue-reminders\n";

// Corrigir o abandoned-cart
$exists = DB::table('scheduled_tasks')->where('command', 'orders:abandoned-cart')->exists();
if ($exists) {
    DB::table('scheduled_tasks')
        ->where('command', 'orders:abandoned-cart')
        ->update(['command' => 'abandoned-cart:send', 'active' => 1]);
    echo "Corrigido e reativado: orders:abandoned-cart -> abandoned-cart:send\n";
}

echo "\n=== TASKS ATIVAS ===\n";
$tasks = DB::table('scheduled_tasks')->where('active', 1)->get(['id', 'command', 'frequency']);
foreach ($tasks as $t) {
    echo "  [{$t->id}] {$t->command} | {$t->frequency}\n";
}
echo "\nOK!\n";
