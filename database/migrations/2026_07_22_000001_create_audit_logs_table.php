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
 *
 * Sistema UNN - Migration audit_logs
 *
 * Tabela de auditoria de eventos criticos do sistema (login, logout,
 * mudancas de configuracao, uploads, pagamentos, webhooks, acoes
 * administrativas, mudancas de permissao). Suporta retencao
 * configuravel e diff entre old/new values.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 6.1, 6.2
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->string('action', 50);

            $table->string('target_type', 100)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('request_id', 36)->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id', 'idx_audit_user');
            $table->index('action', 'idx_audit_action');
            $table->index(['target_type', 'target_id'], 'idx_audit_target');
            $table->index('created_at', 'idx_audit_created');
            $table->index('request_id', 'idx_audit_request');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
