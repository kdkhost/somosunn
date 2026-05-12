<?php

/**
 * Sistema UNN - Migration waf_rules
 *
 * Tabela central das regras do WAF próprio da Unn.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 10.2
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('waf_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uid', 26)->unique(); // ULID

            $table->string('name', 150);
            $table->text('description')->nullable();

            // SQLi, XSS, Path_Traversal, LFI, RFI, SSRF, XXE, RCE,
            // Malicious_Upload, Brute_Force, Credential_Stuffing,
            // User_Enumeration, Scraping, Bot, CSRF_Missing,
            // Webhook_Invalid_Signature, Custom
            $table->string('attack_pattern', 40);

            // Escopo da regra: métodos, rotas (regex), campos
            //   { methods: [], routes_regex: null, fields: [...] }
            $table->json('scope');

            // regex | list | numeric | function
            $table->string('matcher_type', 20);

            // Depende do matcher_type
            //   regex:    { pattern, flags, target }
            //   list:     { values, case_insensitive }
            //   numeric:  { target, operator, value }
            //   function: { function, args }
            $table->json('matcher_payload');

            // 0..100
            $table->unsignedSmallInteger('score')->default(0);

            // monitor | challenge | block
            $table->string('action', 20)->default('monitor');

            // info | low | medium | high | critical
            $table->string('severity', 20)->default('medium');

            $table->boolean('is_active')->default(true);
            $table->boolean('quarantined')->default(false);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'attack_pattern'], 'idx_waf_rules_active_pattern');
            $table->index('attack_pattern', 'idx_waf_rules_pattern');

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waf_rules');
    }
};
