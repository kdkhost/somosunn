<?php

/**
 * Sistema UNN - Migration waf_ip_blocklist
 *
 * Lista dinâmica de IPs e CIDRs bloqueados.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 11.3, 11.4, 11.5, 11.6
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('waf_ip_blocklist', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('cidr', 45); // ex.: 10.0.0.0/24 ou 2001:db8::/32
            $table->binary('ip_start');  // BINARY(16) — preenchido via raw
            $table->binary('ip_end');    // BINARY(16)

            $table->text('reason')->nullable();
            $table->timestamp('expires_at')->nullable();

            // manual | auto_risk_score | auto_brute_force | auto_ssrf
            $table->string('source', 30)->default('manual');
            $table->boolean('auto_generated')->default(false);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('cidr', 'idx_waf_bl_cidr');
            $table->index('expires_at', 'idx_waf_bl_expires');

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });

        // Índice composto sobre ip_start, ip_end e expires_at é criado
        // separadamente para suportar o formato binário em MySQL 5.7+.
        \DB::statement(
            'CREATE INDEX idx_waf_bl_range ON waf_ip_blocklist (ip_start(16), ip_end(16), expires_at)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('waf_ip_blocklist');
    }
};
