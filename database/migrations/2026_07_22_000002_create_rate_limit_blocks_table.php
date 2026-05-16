<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 */

/**
 * Sistema UNN - Migration rate_limit_blocks
 *
 * Tabela auxiliar para integração entre o Rate Limiter avançado e o WAF.
 * Mantém histórico de IPs bloqueados por exceder limites de requisições
 * ou por User-Agent suspeito.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requisitos: 5.2, 5.3
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('rate_limit_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('ip_address', 45);
            $table->string('reason', 100);
            $table->timestamp('blocked_until');
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->index('ip_address', 'idx_rlb_ip');
            $table->index('blocked_until', 'idx_rlb_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_limit_blocks');
    }
};
