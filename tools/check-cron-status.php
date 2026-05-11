<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;
use App\Models\ScheduledTask;

$hb = Cache::get('cron_heartbeat');
echo "=== Status do Cron ===" . PHP_EOL;
echo "Heartbeat: " . ($hb ? $hb : "NULL (scheduler NAO esta rodando)") . PHP_EOL;
echo "Agora: " . now() . PHP_EOL;

if ($hb) {
    $diff = now()->diffInMinutes(\Carbon\Carbon::parse($hb));
    echo "Ultima execucao: " . $diff . " minutos atras" . PHP_EOL;
    echo "Status: " . ($diff < 5 ? "ATIVO" : "INATIVO (mais de 5 min sem rodar)") . PHP_EOL;
} else {
    echo "Status: INATIVO (nunca rodou ou cache expirou)" . PHP_EOL;
}

echo PHP_EOL . "=== Tarefas do Banco ===" . PHP_EOL;
if (\Schema::hasTable('scheduled_tasks')) {
    $tasks = ScheduledTask::all();
    echo "Total: " . $tasks->count() . " (" . $tasks->where('active', true)->count() . " ativas)" . PHP_EOL;
    foreach ($tasks as $t) {
        echo sprintf("  [%s] %s | freq: %s | last_run: %s\n",
            $t->active ? 'ON' : 'OFF',
            $t->command,
            $t->frequency,
            $t->last_run_at ? $t->last_run_at->format('d/m H:i') : 'nunca'
        );
    }
} else {
    echo "Tabela scheduled_tasks NAO existe!" . PHP_EOL;
}

echo PHP_EOL . "=== Teste: rodando schedule:run ===" . PHP_EOL;
$exitCode = Artisan::call('schedule:run', ['--no-interaction' => true]);
echo "Exit code: " . $exitCode . PHP_EOL;
echo Artisan::output();
