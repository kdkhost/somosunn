<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('duration')->nullable()->comment('Duration in minutes');
            $table->boolean('is_certificate_enabled')->default(false);
            $table->string('thumbnail')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('author_name')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};