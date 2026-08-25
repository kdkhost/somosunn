<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('scheduled_tasks')) {
            return;
        }

        DB::table('scheduled_tasks')->updateOrInsert(
            ['command' => 'magazines:import-manchete'],
            [
                'frequency' => '30 2 * * *',
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('scheduled_tasks')) {
            DB::table('scheduled_tasks')->where('command', 'magazines:import-manchete')->delete();
        }
    }
};
