<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sponsor_leads')) {
            return;
        }

        Schema::create('sponsor_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_id')->constrained('sponsors')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->string('source', 60)->default('manual')->index();
            $table->boolean('consent')->default(false);
            $table->timestamps();
            $table->index(['sponsor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_leads');
    }
};
