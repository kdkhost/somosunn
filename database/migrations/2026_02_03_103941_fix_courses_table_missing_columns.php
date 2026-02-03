<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'author_name')) {
                $table->string('author_name')->nullable();
            }
            if (!Schema::hasColumn('courses', 'status')) {
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for fix migration
    }
};
