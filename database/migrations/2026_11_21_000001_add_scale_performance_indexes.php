<?php

/**
 * Migration: Indices compostos de alta performance para MariaDB.
 *
 * Cobre consultas frequentes em service_visits, activity_logs, visitor_logs,
 * notifications, connections, order_items, users, orders e scheduled_tasks.
 * Implementacao idempotente e defensiva (verifica tabela e coluna antes de criar).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, array{0: string, 1: array<int, string>, 2: string}>
     */
    private array $indexes = [
        // Visitas de servicos e timeline por dono/periodo
        ['service_visits', ['service_type', 'service_id', 'visited_at'], 'idx_sv_type_id_visited'],
        ['service_visits', ['user_id', 'visited_at'], 'idx_sv_user_visited'],

        // Logs de atividade e visitantes
        ['activity_logs', ['created_at'], 'idx_activity_logs_created'],
        ['activity_logs', ['action', 'created_at'], 'idx_activity_logs_action_created'],
        ['visitor_logs', ['ip_hash', 'created_at'], 'idx_visitor_logs_ip_created'],
        ['visitor_logs', ['user_id', 'created_at'], 'idx_visitor_logs_user_created'],

        // Notificacoes nao lidas e paginadas por usuario
        ['notifications', ['notifiable_type', 'notifiable_id', 'read_at', 'created_at'], 'idx_notif_morph_read_created'],

        // Conexoes bidirecionais e ordenacao recente
        ['connections', ['requested_id', 'status', 'created_at'], 'idx_conn_reqd_status_created'],
        ['connections', ['requester_id', 'status', 'created_at'], 'idx_conn_reqr_status_created'],

        // Itens de pedido para relatorios de vendas e verificacao de matricula/acesso
        ['order_items', ['item_type', 'item_id', 'order_id'], 'idx_order_items_type_id_order'],

        // Usuarios: ranking, nivel, plano ativo e verificacao
        ['users', ['role', 'level', 'points'], 'idx_users_role_level_points'],
        ['users', ['plan_id', 'plan_expires_at'], 'idx_users_plan_expires'],

        // Pedidos: dashboard financeiro, relatorios por vendedor e conciliacao
        ['orders', ['status', 'paid_at'], 'idx_orders_status_paid_at'],
        ['orders', ['seller_id', 'status', 'created_at'], 'idx_orders_seller_status_created'],
        ['orders', ['status', 'is_manual_approval', 'created_at'], 'idx_orders_status_manual_created'],

        // Central de cron
        ['scheduled_tasks', ['active', 'id'], 'idx_scheduled_tasks_active_id'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as [$tableName, $columns, $indexName]) {
            $this->createIndexSafely($tableName, $columns, $indexName);
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as [$tableName, , $indexName]) {
            $this->dropIndexSafely($tableName, $indexName);
        }
    }

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

            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
                $table->index($columns, $indexName);
            });
        } catch (\Throwable) {
            // Indice ja existente em producao ou collation divergente - ignorar de forma segura
        }
    }

    private function dropIndexSafely(string $tableName, string $indexName): void
    {
        try {
            if (!Schema::hasTable($tableName)) {
                return;
            }

            Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
                $table->dropIndex($indexName);
            });
        } catch (\Throwable) {
            // Indice pode nao existir durante rollback
        }
    }
};
