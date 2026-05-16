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
 * Sistema UNN - Migration add_performance_indexes
 *
 * Adiciona indices compostos em tabelas existentes para otimizacao
 * de queries de uso frequente (listagens admin, dashboards, relatorios,
 * processamento de queue e analise de eventos do WAF).
 *
 * Defensiva: cada CREATE INDEX e envolvido em try/catch e checagem
 * de Schema::hasColumn para evitar falhas em ambientes onde alguma
 * coluna ainda nao exista ou onde o indice ja tenha sido criado
 * manualmente. Falhas individuais sao silenciadas para nao bloquear
 * o deploy.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requisitos: 12.1, 12.4, 12.6
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Lista de indices a serem criados.
     *
     * Cada item: [tabela, [colunas], nome_indice]
     */
    private array $indexes = [
        // users
        ['users',      ['plan_id', 'status'],                'idx_users_plan_status'],
        ['users',      ['created_at'],                       'idx_users_created'],
        ['users',      ['email_verified_at'],                'idx_users_email_verified'],

        // orders
        ['orders',     ['user_id', 'status'],                'idx_orders_user_status'],
        ['orders',     ['created_at', 'status'],             'idx_orders_created_status'],
        ['orders',     ['payment_method'],                   'idx_orders_payment_method'],

        // payments
        ['payments',   ['order_id', 'status'],               'idx_payments_order_status'],
        ['payments',   ['created_at'],                       'idx_payments_created'],

        // waf_events
        ['waf_events', ['ip_address', 'created_at'],         'idx_waf_ip_created'],
        ['waf_events', ['event_type', 'created_at'],         'idx_waf_type_created'],

        // jobs
        ['jobs',       ['queue', 'available_at'],            'idx_jobs_queue_available'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as [$tableName, $columns, $indexName]) {
            $this->createIndexSafely($tableName, $columns, $indexName);
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as [$tableName, $columns, $indexName]) {
            $this->dropIndexSafely($tableName, $indexName);
        }
    }

    /**
     * Cria um indice de forma defensiva.
     * Skips silenciosamente se:
     *  - Tabela nao existe
     *  - Alguma coluna nao existe
     *  - Indice ja existe (capturado via try/catch)
     */
    private function createIndexSafely(string $tableName, array $columns, string $indexName): void
    {
        try {
            if (!Schema::hasTable($tableName)) {
                return;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($tableName, $column)) {
                    return;
                }
            }

            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        } catch (\Throwable $e) {
            // Indice ja existe ou nome de coluna divergente - silenciar
        }
    }

    /**
     * Remove um indice de forma defensiva.
     */
    private function dropIndexSafely(string $tableName, string $indexName): void
    {
        try {
            if (!Schema::hasTable($tableName)) {
                return;
            }

            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        } catch (\Throwable $e) {
            // Indice pode nao existir - silenciar
        }
    }
};
