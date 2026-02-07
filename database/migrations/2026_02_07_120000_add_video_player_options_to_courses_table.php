<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'video_block_download')) {
                $table->boolean('video_block_download')->default(false);
            }

            if (!Schema::hasColumn('courses', 'video_floating_enabled')) {
                $table->boolean('video_floating_enabled')->default(false);
            }

            if (!Schema::hasColumn('courses', 'video_floating_width')) {
                $table->unsignedSmallInteger('video_floating_width')->nullable();
            }

            if (!Schema::hasColumn('courses', 'video_floating_height')) {
                $table->unsignedSmallInteger('video_floating_height')->nullable();
            }
        });
    }

    public function down(): void
    {
        // no-op (safety)
    }
};

