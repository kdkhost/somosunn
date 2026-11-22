<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('scheduled_tasks')) {
            return;
        }

        $targetCommand = 'queue:work --queue=webhooks,uploads,notifications,default --stop-when-empty --tries=3 --timeout=120';

        DB::table('scheduled_tasks')
            ->whereIn('command', [
                'queue:work --stop-when-empty --tries=3 --timeout=120',
                'queue:work --stop-when-empty --tries=3',
            ])
            ->update([
                'command' => $targetCommand,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('scheduled_tasks')) {
            return;
        }

        DB::table('scheduled_tasks')
            ->where('command', 'queue:work --queue=webhooks,uploads,notifications,default --stop-when-empty --tries=3 --timeout=120')
            ->update([
                'command' => 'queue:work --stop-when-empty --tries=3 --timeout=120',
                'updated_at' => now(),
            ]);
    }
};
