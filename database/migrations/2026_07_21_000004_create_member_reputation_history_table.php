<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria tabela para historico diario de scores de reputacao.
     */
    public function up(): void
    {
        Schema::create('member_reputation_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('overall_score')->unsigned();
            $table->date('recorded_at');
            $table->timestamp('created_at')->nullable();
            $table->index(['user_id', 'recorded_at']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverte a migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_reputation_history');
    }
};
