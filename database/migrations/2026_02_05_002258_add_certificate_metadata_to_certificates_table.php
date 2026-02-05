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
            $table->integer('workload_hours')->nullable()->after('course_id');
            $table->string('signature_path')->nullable()->after('pdf_path');
            $table->decimal('completion_percentage', 5, 2)->default(100)->after('workload_hours');
            $table->json('certificate_metadata')->nullable()->after('completion_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['workload_hours', 'signature_path', 'completion_percentage', 'certificate_metadata']);
        });
    }
};
