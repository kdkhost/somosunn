<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Corrige a coluna feedback na tabela satisfactions:
     * - A validação aceita até 600 caracteres mas o campo era varchar(255).
     * - Adiciona unique em interaction_id (uma satisfação por interação).
     */
    public function up(): void
    {
        if (!Schema::hasTable('satisfactions')) {
            return;
        }

        Schema::table('satisfactions', function (Blueprint $table) {
            if (Schema::hasColumn('satisfactions', 'feedback')) {
                $table->text('feedback')->nullable()->change();
            }

            // Garantir unique constraint em interaction_id
            try {
                $table->unique('interaction_id', 'satisfactions_interaction_id_unique');
            } catch (\Throwable $e) {
                // índice já existe — ignora
            }
        });
    }

    public function down(): void
    {
        Schema::table('satisfactions', function (Blueprint $table) {
            if (Schema::hasColumn('satisfactions', 'feedback')) {
                $table->string('feedback')->nullable()->change();
            }
        });
    }
};
