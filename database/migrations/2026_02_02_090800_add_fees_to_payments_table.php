<?php

/**
 * Sistema UNN - add_fees_to_payments_table
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
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('fee_amount', 10, 2)->default(0)->after('amount');
            $table->decimal('fee_percentage', 8, 2)->default(0)->after('fee_amount');
            $table->boolean('fee_passed')->default(false)->after('fee_percentage');
            $table->decimal('platform_fee_amount', 10, 2)->default(0)->after('fee_passed');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['fee_amount','fee_percentage','fee_passed','platform_fee_amount']);
        });
    }
};
