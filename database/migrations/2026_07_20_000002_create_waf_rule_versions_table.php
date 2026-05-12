<?php

/**
 * Sistema UNN - Migration waf_rule_versions
 *
 * Histórico append-only das alterações em waf_rules.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 10.7, 15.7
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('waf_rule_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rule_id');
            $table->unsignedInteger('version');

            // snapshot do estado ANTERIOR da regra (ou null na criação)
            $table->json('snapshot')->nullable();

            $table->unsignedBigInteger('actor_id')->nullable();

            // created | updated | deleted | toggled
            $table->string('action', 20);

            $table->timestamp('created_at')->useCurrent();

            $table->index(['rule_id', 'version'], 'idx_waf_rv_rule_version');
            $table->index('actor_id', 'idx_waf_rv_actor');
            $table->index('created_at', 'idx_waf_rv_created_at');

            $table->foreign('rule_id')
                ->references('id')->on('waf_rules')
                ->cascadeOnDelete();
            $table->foreign('actor_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waf_rule_versions');
    }
};
