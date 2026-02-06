<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('visitor_logs')) {
            return;
        }

        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_hash', 64);
            $table->string('ip', 45)->nullable();

            $table->string('country', 2)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone', 60)->nullable();

            $table->string('method', 10)->default('GET');
            $table->string('path', 255);
            $table->text('url')->nullable();
            $table->text('referrer')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['path']);
            $table->index(['country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};

