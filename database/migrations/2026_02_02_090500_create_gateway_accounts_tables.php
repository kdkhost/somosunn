<?php

/**
 * Sistema UNN - create_gateway_accounts_tables
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
        Schema::create('gateway_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('provider'); // mercadopago|pagseguro
            $table->string('public_key')->nullable();
            $table->string('access_token')->nullable();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->string('pix_key')->nullable();
            $table->boolean('enabled')->default(true);
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'provider']);
        });

        Schema::create('gateway_configs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();
            $table->boolean('pass_fee_to_buyer')->default(false);
            $table->decimal('fee_percentage', 8, 2)->default(0);
            $table->decimal('fee_fixed', 10, 2)->default(0);
            $table->decimal('platform_fee_percentage', 8, 2)->default(0);
            $table->string('platform_fee_mode')->default('per_sale'); // per_sale|subscription
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gateway_configs');
        Schema::dropIfExists('gateway_accounts');
    }
};
