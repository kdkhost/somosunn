<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans','highlight')) {
                $table->boolean('highlight')->default(false)->after('image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans','highlight')) {
                $table->dropColumn('highlight');
            }
        });
    }
};
