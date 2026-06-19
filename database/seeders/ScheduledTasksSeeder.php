<?php

namespace Database\Seeders;

use App\Models\ScheduledTask;
use Illuminate\Database\Seeder;

class ScheduledTasksSeeder extends Seeder
{
    public function run(): void
    {
        foreach ((array) config('cron-panel.defaults', []) as $taskData) {
            ScheduledTask::updateOrCreate(
                ['command' => $taskData['command']],
                [
                    'frequency' => $taskData['frequency'],
                    'active' => (bool) ($taskData['active'] ?? true),
                ]
            );
        }
    }
}
