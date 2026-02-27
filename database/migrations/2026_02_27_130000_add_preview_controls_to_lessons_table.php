<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'free_preview_mode')) {
                $table->string('free_preview_mode', 20)->default('full')->after('is_free_preview');
            }

            if (!Schema::hasColumn('lessons', 'free_preview_seconds')) {
                $table->integer('free_preview_seconds')->nullable()->after('free_preview_mode');
            }
        });
    }

    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'free_preview_seconds')) {
                $table->dropColumn('free_preview_seconds');
            }

            if (Schema::hasColumn('lessons', 'free_preview_mode')) {
                $table->dropColumn('free_preview_mode');
            }
        });
    }
};
