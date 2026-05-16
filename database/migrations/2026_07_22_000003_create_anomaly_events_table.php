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
 * Sistema UNN - Migration anomaly_events
 *
 * Tabela utilizada pelo AnomalyDetectorService para registrar eventos
 * anômalos detectados (logins falhos em rajada, flood de uploads,
 * webhooks inválidos repetidos, etc.) com contexto de origem,
 * thresholds aplicados e ações automáticas tomadas (bloqueio via WAF).
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requisitos: 11.1, 11.2, 11.3
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('anomaly_events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('type', 50);
            $table->string('source_ip', 45)->nullable();
            $table->unsignedBigInteger('source_user_id')->nullable();
            $table->string('source_identifier', 255)->nullable();
            $table->integer('threshold_value');
            $table->integer('actual_value');
            $table->integer('window_minutes');
            $table->timestamp('notified_at')->nullable();
            $table->boolean('auto_blocked')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index('type', 'idx_anomaly_type');
            $table->index('source_ip', 'idx_anomaly_source');
            $table->index('created_at', 'idx_anomaly_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomaly_events');
    }
};
