<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('is_recurring');
            }
            if (!Schema::hasColumn('plans', 'price_periods')) {
                // JSON: {"mensal": 97, "trimestral": 264, "semestral": 510, "anual": 960}
                $table->json('price_periods')->nullable()->after('sort_order');
            }
        });

        // Popular sort_order com a ordem atual (pelo id)
        DB::statement('UPDATE plans SET sort_order = id WHERE sort_order = 0');
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['sort_order', 'price_periods']);
        });
    }
};
