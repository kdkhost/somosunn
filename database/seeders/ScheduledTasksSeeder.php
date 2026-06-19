<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScheduledTask;

class ScheduledTasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tasks = [
            [
                'command' => 'notifications:cleanup',
                'frequency' => '0 0 * * *', // Diariamente à meia-noite
                'active' => true,
            ],
            [
                'command' => 'queue:work --stop-when-empty --tries=3',
                'frequency' => '* * * * *', // A cada minuto
                'active' => true,
            ],
            [
                'command' => 'orders:send-unpaid-reminders',
                'frequency' => '*/15 * * * *', // A cada 15 minutos
                'active' => true,
            ],
            [
                'command' => 'orders:cancel-unpaid',
                'frequency' => '*/5 * * * *', // A cada 5 minutos
                'active' => true,
            ],
            [
                'command' => 'abandoned-cart:send',
                'frequency' => '0 */4 * * *', // A cada 4 horas
                'active' => true,
            ],
            [
                'command' => 'subscriptions:check-expired',
                'frequency' => '0 0 * * *', // Diariamente à meia-noite
                'active' => true,
            ],
            [
                'command' => 'auth:clear-resets',
                'frequency' => '0 0 * * *', // Diariamente à meia-noite
                'active' => true,
            ],
            [
                'command' => 'sanctum:prune-expired',
                'frequency' => '0 0 * * *', // Diariamente à meia-noite
                'active' => true,
            ],
        ];

        foreach ($tasks as $taskData) {
            ScheduledTask::firstOrCreate(
                ['command' => $taskData['command']],
                $taskData
            );
        }
    }
}
