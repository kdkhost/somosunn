<?php

/**
 * Sistema UNN - create_notifications_table
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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
