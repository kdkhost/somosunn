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
                'command' => 'orders:cancel-unpaid',
                'frequency' => '0 * * * *', // A cada hora
                'active' => true,
            ],
            [
                'command' => 'orders:abandoned-cart',
                'frequency' => '0 * * * *', // A cada hora
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
            [
                'command' => 'users:send-birthday-emails',
                'frequency' => '0 9 * * *', // Diariamente às 09:00
                'active' => true,
            ],
            [
                'command' => 'invoices:send-overdue-reminders',
                'frequency' => '0 8 * * *', // Diariamente às 08:00
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
