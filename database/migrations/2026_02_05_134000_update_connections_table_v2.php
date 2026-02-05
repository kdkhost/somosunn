<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('connections', function (Blueprint $table) {
            if (!Schema::hasColumn('connections', 'hide_profile')) {
                $table->boolean('hide_profile')->default(false)->after('status');
            }

            // Note: Enums are tricky to modify in some DBs without doctrine/dbal, 
            // but we'll assume standard Laravel 10 environment or just use raw if needed.
            // For now, we'll just ensure the logic handles 'blocked' in code if it's a string column.
            // One migration used string, another used enum. If it's string, it's fine.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('connections', function (Blueprint $table) {
            if (Schema::hasColumn('connections', 'hide_profile')) {
                $table->dropColumn('hide_profile');
            }
        });
    }
};
