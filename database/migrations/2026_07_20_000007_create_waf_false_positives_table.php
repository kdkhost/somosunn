<?php

/**
 * Sistema UNN - Migration waf_false_positives
 *
 * Registro de WAF_Events marcados manualmente como falso positivo.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 14.6
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('waf_false_positives', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('rule_id')->nullable();
            $table->unsignedBigInteger('reviewed_by');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('event_id', 'idx_waf_fp_event');
            $table->index('rule_id', 'idx_waf_fp_rule');
            $table->index('reviewed_by', 'idx_waf_fp_reviewer');

            $table->foreign('event_id')
                ->references('id')->on('waf_events')
                ->cascadeOnDelete();
            $table->foreign('rule_id')
                ->references('id')->on('waf_rules')
                ->nullOnDelete();
            $table->foreign('reviewed_by')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waf_false_positives');
    }
};
