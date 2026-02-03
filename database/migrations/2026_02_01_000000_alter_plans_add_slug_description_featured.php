<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans','slug')) {
                $table->string('slug')->unique()->after('name');
            }
            if (!Schema::hasColumn('plans','description')) {
                $table->text('description')->nullable()->after('period');
            }
            if (!Schema::hasColumn('plans','is_featured')) {
                $table->boolean('is_featured')->default(false)->after('image');
            }
            if (Schema::hasColumn('plans','highlight')) {
                $table->renameColumn('highlight','highlight_legacy');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans','slug')) $table->dropColumn('slug');
            if (Schema::hasColumn('plans','description')) $table->dropColumn('description');
            if (Schema::hasColumn('plans','is_featured')) $table->dropColumn('is_featured');
            if (Schema::hasColumn('plans','highlight_legacy')) {
                $table->renameColumn('highlight_legacy','highlight');
            }
        });
    }
};
