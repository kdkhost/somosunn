<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'is_free_preview')) {
                $table->boolean('is_free_preview')->default(false)->after('video_url');
            }
            if (!Schema::hasColumn('lessons', 'duration')) {
                $table->integer('duration')->default(0)->after('order');
            }
            // Garantir que type seja nullable se existir
            if (Schema::hasColumn('lessons', 'type')) {
                $table->string('type')->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'is_free_preview')) {
                $table->dropColumn('is_free_preview');
            }
             if (Schema::hasColumn('lessons', 'duration')) {
                $table->dropColumn('duration');
            }
        });
    }
};
