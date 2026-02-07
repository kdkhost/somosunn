<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_contents')) {
            return;
        }

        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120);
            $table->string('key', 120);
            $table->longText('value')->nullable();
            $table->string('type', 20)->default('text');
            $table->timestamps();

            $table->unique(['slug', 'key']);
            $table->index(['slug', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_contents');
    }
};
