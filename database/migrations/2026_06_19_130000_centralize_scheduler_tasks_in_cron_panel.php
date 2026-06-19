<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('scheduled_tasks')) {
            return;
        }

        $now = Carbon::now();

        $this->normalizeLegacyCommands($now);

        foreach ((array) config('cron-panel.defaults', []) as $taskData) {
            $existing = DB::table('scheduled_tasks')
                ->where('command', $taskData['command'])
                ->first();

            if ($existing) {
                DB::table('scheduled_tasks')->where('id', $existing->id)->update([
                    'frequency' => $existing->frequency ?: $taskData['frequency'],
                    'active' => (bool) $existing->active,
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('scheduled_tasks')->insert([
                'command' => $taskData['command'],
                'frequency' => $taskData['frequency'],
                'active' => (bool) ($taskData['active'] ?? true),
                'last_run_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('scheduled_tasks')) {
            return;
        }

        $commands = collect((array) config('cron-panel.defaults', []))
            ->pluck('command')
            ->all();

        DB::table('scheduled_tasks')
            ->whereIn('command', $commands)
            ->delete();
    }

    private function normalizeLegacyCommands(Carbon $now): void
    {
        $renames = [
            'orders:send-unpaid-reminders' => 'orders:send-unpaid-reminders --limit=200',
            'queue:work --stop-when-empty --tries=3' => 'queue:work --stop-when-empty --tries=3 --timeout=120',
        ];

        foreach ($renames as $from => $to) {
            $target = DB::table('scheduled_tasks')->where('command', $to)->first();
            $source = DB::table('scheduled_tasks')->where('command', $from)->first();

            if (!$source) {
                continue;
            }

            if ($target) {
                DB::table('scheduled_tasks')->where('id', $source->id)->delete();
                continue;
            }

            DB::table('scheduled_tasks')->where('id', $source->id)->update([
                'command' => $to,
                'updated_at' => $now,
            ]);
        }
    }
};
