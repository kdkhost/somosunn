<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mentorships', function (Blueprint $table) {
            $table->boolean('is_certificate_enabled')->default(false)->after('video_link');
            $table->string('certificate_bg')->nullable()->after('is_certificate_enabled');
            $table->string('instructor_signature')->nullable()->after('certificate_bg');
            $table->json('certificate_settings')->nullable()->after('instructor_signature');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_certificate_enabled')->default(false)->after('published');
            $table->string('certificate_bg')->nullable()->after('is_certificate_enabled');
            $table->string('instructor_signature')->nullable()->after('certificate_bg');
            $table->json('certificate_settings')->nullable()->after('instructor_signature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentorships', function (Blueprint $table) {
            $table->dropColumn(['is_certificate_enabled', 'certificate_bg', 'instructor_signature', 'certificate_settings']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['is_certificate_enabled', 'certificate_bg', 'instructor_signature', 'certificate_settings']);
        });
    }
};
