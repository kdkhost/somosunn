<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'gallery_cover_image')) {
                $table->string('gallery_cover_image')->nullable()->after('image');
            }

            if (!Schema::hasColumn('events', 'gallery_cover_media_id')) {
                $table->unsignedBigInteger('gallery_cover_media_id')->nullable()->after('gallery_cover_image');
                $table->index('gallery_cover_media_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'gallery_cover_media_id')) {
                $table->dropIndex(['gallery_cover_media_id']);
                $table->dropColumn('gallery_cover_media_id');
            }

            if (Schema::hasColumn('events', 'gallery_cover_image')) {
                $table->dropColumn('gallery_cover_image');
            }
        });
    }
};
