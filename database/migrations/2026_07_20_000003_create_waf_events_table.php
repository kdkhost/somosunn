<?php

/**
 * Sistema UNN - Migration waf_events
 *
 * Registro de cada requisição inspecionada pelo WAF (quando relevante).
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 12.1, 12.3
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('waf_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uid', 26)->unique(); // ULID

            // correlaciona com ActivityLog / logs da aplicação
            $table->char('request_id', 36)->index('idx_waf_events_request_id');

            // timestamp com precisão de milissegundos
            $table->timestamp('occurred_at', 3);

            // IPv4 ou IPv6 em formato texto (facilita filtros do painel)
            $table->string('ip', 45);
            $table->char('country', 2)->nullable();
            $table->unsignedInteger('asn')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('method', 10);
            $table->string('route', 255)->nullable();
            $table->string('path', 500);

            $table->unsignedSmallInteger('status');
            $table->unsignedSmallInteger('risk_score')->default(0);

            // allowed | monitored | challenged | blocked
            $table->string('decision', 20);

            // [ { rule_id, uid, name, score, attack_pattern } ]
            $table->json('rules_fired')->nullable();

            // amostras mascaradas dos campos ofensores, truncadas em 2 KB
            $table->json('samples')->nullable();

            $table->string('user_agent', 500)->nullable();
            $table->string('referrer', 500)->nullable();

            $table->boolean('is_false_positive')->default(false);

            $table->timestamp('created_at')->useCurrent();

            $table->index('occurred_at', 'idx_waf_events_occurred_at');
            $table->index(['ip', 'occurred_at'], 'idx_waf_events_ip_time');
            $table->index(['decision', 'occurred_at'], 'idx_waf_events_decision_time');
            $table->index(['route', 'occurred_at'], 'idx_waf_events_route_time');
            $table->index(['user_id', 'occurred_at'], 'idx_waf_events_user_time');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waf_events');
    }
};
