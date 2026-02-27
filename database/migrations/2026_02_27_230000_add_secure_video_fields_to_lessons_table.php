<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'video_storage_disk')) {
                $table->string('video_storage_disk', 50)->nullable()->after('video_url');
            }

            if (!Schema::hasColumn('lessons', 'video_storage_path')) {
                $table->text('video_storage_path')->nullable()->after('video_storage_disk');
            }

            if (!Schema::hasColumn('lessons', 'video_hls_manifest_path')) {
                $table->text('video_hls_manifest_path')->nullable()->after('video_storage_path');
            }

            if (!Schema::hasColumn('lessons', 'video_hls_key_path')) {
                $table->text('video_hls_key_path')->nullable()->after('video_hls_manifest_path');
            }

            if (!Schema::hasColumn('lessons', 'video_transcode_status')) {
                $table->string('video_transcode_status', 20)->default('none')->after('video_hls_key_path');
            }

            if (!Schema::hasColumn('lessons', 'video_transcode_error')) {
                $table->text('video_transcode_error')->nullable()->after('video_transcode_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'video_transcode_error')) {
                $table->dropColumn('video_transcode_error');
            }

            if (Schema::hasColumn('lessons', 'video_transcode_status')) {
                $table->dropColumn('video_transcode_status');
            }

            if (Schema::hasColumn('lessons', 'video_hls_key_path')) {
                $table->dropColumn('video_hls_key_path');
            }

            if (Schema::hasColumn('lessons', 'video_hls_manifest_path')) {
                $table->dropColumn('video_hls_manifest_path');
            }

            if (Schema::hasColumn('lessons', 'video_storage_path')) {
                $table->dropColumn('video_storage_path');
            }

            if (Schema::hasColumn('lessons', 'video_storage_disk')) {
                $table->dropColumn('video_storage_disk');
            }
        });
    }
};
