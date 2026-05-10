<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('magazines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 80)->nullable()->index();
            $table->string('edition', 60)->nullable();
            $table->date('published_at')->nullable()->index();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('pdf_file');
            $table->unsignedSmallInteger('pages_count')->nullable();
            $table->unsignedInteger('file_size_kb')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_download')->default(true);
            $table->boolean('enable_sound')->default(true);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->enum('visibility', ['public', 'members', 'interest'])->default('interest');
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magazines');
    }
};
