<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_sponsors')) {
            return;
        }

        Schema::create('event_sponsors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('sponsor_id')->constrained('sponsors')->cascadeOnDelete();
            $table->string('type', 30)->default('bronze')->index();
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['event_id', 'sponsor_id', 'type'], 'event_sponsor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_sponsors');
    }
};
