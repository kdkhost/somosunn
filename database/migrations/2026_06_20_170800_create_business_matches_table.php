<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('business_matches')) {
            return;
        }

        Schema::create('business_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('matched_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->string('status', 30)->default('suggested')->index();
            $table->timestamps();
            $table->unique(['user_id', 'matched_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_matches');
    }
};
