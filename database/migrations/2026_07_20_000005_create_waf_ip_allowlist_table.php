<?php

/**
 * Sistema UNN - Migration waf_ip_allowlist
 *
 * Lista dinâmica de IPs e CIDRs isentos de bloqueio.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 11.3, 9.8
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('waf_ip_allowlist', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('cidr', 45);
            $table->binary('ip_start');
            $table->binary('ip_end');

            $table->text('reason')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('cidr', 'idx_waf_al_cidr');
            $table->index('expires_at', 'idx_waf_al_expires');

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });

        \DB::statement(
            'CREATE INDEX idx_waf_al_range ON waf_ip_allowlist (ip_start(16), ip_end(16), expires_at)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('waf_ip_allowlist');
    }
};
