<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria tabela para armazenar scores de reputacao dos membros.
     */
    public function up(): void
    {
        Schema::create('member_reputation_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->tinyInteger('overall_score')->unsigned()->default(50);
            $table->decimal('delivery_rate', 5, 2)->default(70.00);
            $table->decimal('relationship_score', 5, 2)->default(100.00);
            $table->decimal('interaction_score', 5, 2)->default(0.00);
            $table->decimal('engagement_score', 5, 2)->default(0.00);
            $table->boolean('has_seller_store')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('decay_started_at')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_reputation_scores');
    }
};
