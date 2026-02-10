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
        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->change();
            $table->foreignId('mentorship_id')->nullable()->after('course_id')->constrained('mentorships')->nullOnDelete();
            $table->foreignId('event_id')->nullable()->after('mentorship_id')->constrained('events')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['mentorship_id']);
            $table->dropForeign(['event_id']);
            $table->dropColumn(['mentorship_id', 'event_id']);
            $table->foreignId('course_id')->nullable(false)->change();
        });
    }
};
