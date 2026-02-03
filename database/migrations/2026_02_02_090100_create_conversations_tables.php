<?php

/**
 * Sistema UNN - create_conversations_tables
 *
 * Autor: George Marcelo (KDKHOST SOLUÇÕES)
 * Telefone: +55 (21) 98132-5441
 * Telegram: https://t.me/MARCELO_BRAD
 *
 * Copyright (c) 2026 Kdkhost Soluções. Todos os direitos reservados.
 *
 * AVISO LEGAL:
 * Este software e seu código-fonte são propriedade intelectual de kdkhost soluções.
 * É proibida a reprodução, distribuição, modificação, engenharia reversa ou uso não autorizado,
 * total ou parcial, sem autorização prévia e por escrito.
 *
 * Contato: contato@kdkhost.com.br
 * Licenciamento: Uso restrito conforme contrato/termos aplicáveis.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('direct'); // direct|group
            $table->string('title')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('conversation_user', function (Blueprint $table) {
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('member'); // admin|member
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->primary(['conversation_id', 'user_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->string('type')->default('text'); // text|image|file
            $table->string('media_path')->nullable();
            $table->timestamps();
        });

        Schema::create('message_reads', function (Blueprint $table) {
            $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->primary(['message_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('message_reads');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_user');
        Schema::dropIfExists('conversations');
    }
};
