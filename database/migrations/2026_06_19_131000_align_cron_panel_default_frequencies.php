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

        foreach ((array) config('cron-panel.defaults', []) as $taskData) {
            DB::table('scheduled_tasks')
                ->where('command', $taskData['command'])
                ->update([
                    'frequency' => $taskData['frequency'],
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        // Frequencias personalizaveis pelo painel; rollback nao restaura valores anteriores.
    }
};
