<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('item_reviews')) {
            return;
        }

        Schema::create('item_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('reviewable');
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->string('moderation_notes', 255)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->unique(['user_id', 'reviewable_type', 'reviewable_id'], 'item_reviews_unique_user_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_reviews');
    }
};

