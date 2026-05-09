<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CORRIGINDO SCHEDULED_TASKS ===\n\n";

// Desativar comandos que não existem
$invalidCommands = [
    'users:send-birthday-emails',
    'invoices:send-overdue-reminders',
    'orders:abandoned-cart',
];

foreach ($invalidCommands as $cmd) {
    $affected = DB::table('scheduled_tasks')
        ->where('command', $cmd)
        ->update(['active' => 0]);
    echo "Desativado: {$cmd} ({$affected} registros)\n";
}

echo "\n=== TASKS ATIVAS RESTANTES ===\n";
$tasks = DB::table('scheduled_tasks')->where('active', 1)->get(['id', 'command', 'frequency']);
foreach ($tasks as $t) {
    echo "  [{$t->id}] {$t->command} | {$t->frequency}\n";
}

echo "\nOK!\n";
