<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('magazines')) {
            return;
        }

        DB::table('magazines')
            ->where('visibility', 'interest')
            ->update(['visibility' => 'public']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE magazines MODIFY visibility ENUM('public', 'members', 'interest') NOT NULL DEFAULT 'public'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('magazines') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE magazines MODIFY visibility ENUM('public', 'members', 'interest') NOT NULL DEFAULT 'interest'");
        }
    }
};
