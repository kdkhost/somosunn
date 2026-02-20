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
        if (!Schema::hasTable('job_applies')) {
            Schema::create('job_applies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_vacancy_id');
                $table->unsignedBigInteger('user_id');
                $table->string('resume_path')->nullable();
                $table->text('cover_letter')->nullable();
                $table->string('status')->default('pending'); // pending, reviewing, accepted, rejected
                $table->timestamps();

                $table->foreign('job_vacancy_id')->references('id')->on('job_vacancies')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applies');
    }
};
