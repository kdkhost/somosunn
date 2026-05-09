<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CORRIGINDO SCHEDULED_TASKS ===\n\n";

// Corrigir comandos com assinatura antiga
$renames = [
    'orders:abandoned-cart' => 'abandoned-cart:send',
];

foreach ($renames as $old => $new) {
    $affected = DB::table('scheduled_tasks')
        ->where('command', $old)
        ->update(['command' => $new, 'active' => 1]);
    echo "Renomeado: {$old} -> {$new} ({$affected} registros)\n";
}

// Reativar comandos que agora existem
$validCommands = [
    'users:send-birthday-emails',
    'invoices:send-overdue-reminders',
    'abandoned-cart:send',
    'notifications:cleanup',
    'events:update-batches',
    'subscriptions:check-expired',
    'orders:cancel-unpaid',
    'cart:cleanup-expired',
    'dashboard:warm-cache',
    'points:award-top-ranking',
    'points:award-birthday-bonus',
    'share-requests:expire',
];

foreach ($validCommands as $cmd) {
    $exists = DB::table('scheduled_tasks')->where('command', $cmd)->exists();
    if ($exists) {
        DB::table('scheduled_tasks')->where('command', $cmd)->update(['active' => 1]);
        echo "Reativado: {$cmd}\n";
    }
}

echo "\n=== TASKS ATIVAS RESTANTES ===\n";
$tasks = DB::table('scheduled_tasks')->where('active', 1)->get(['id', 'command', 'frequency']);
foreach ($tasks as $t) {
    echo "  [{$t->id}] {$t->command} | {$t->frequency}\n";
}

echo "\nOK!\n";
