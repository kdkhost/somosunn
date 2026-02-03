<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('status');
            $table->json('certificate_settings')->nullable()->after('is_certificate_enabled');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->integer('duration')->default(0)->comment('Duration in seconds')->after('video_url');
        });

        // Update status enum to include 'paused' if possible, or we just map it in code.
        // Changing ENUM in Doctrine/Laravel can be tricky.
        // Let's use raw SQL to add 'paused' to the enum for MySQL.
        DB::statement("ALTER TABLE courses MODIFY COLUMN status ENUM('draft', 'published', 'archived', 'paused') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'certificate_settings']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
        
        // Reverting enum is dangerous if data exists, skipping safely.
    }
};
