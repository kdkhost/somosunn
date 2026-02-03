<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans','billing_cycle')) {
                $table->string('billing_cycle')->default('monthly')->after('period'); // monthly, quarterly, semiannual, annual
            }
            if (!Schema::hasColumn('plans','prorata')) {
                $table->boolean('prorata')->default(false)->after('billing_cycle');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans','prorata')) $table->dropColumn('prorata');
            if (Schema::hasColumn('plans','billing_cycle')) $table->dropColumn('billing_cycle');
        });
    }
};
