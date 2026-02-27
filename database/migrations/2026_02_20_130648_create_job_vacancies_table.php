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
        if (!Schema::hasTable('job_vacancies')) {
            Schema::create('job_vacancies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Empresa (usuário com permissão)
                $table->string('title');
                $table->string('company_name')->nullable();
                $table->string('location')->nullable();
                $table->string('type')->default('full-time'); // full-time, part-time, remote, etc
                $table->text('short_description')->nullable();
                $table->longText('description');
                $table->text('requirements')->nullable();
                $table->text('benefits')->nullable();
                $table->string('salary_range')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};
