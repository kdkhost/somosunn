<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $indexes = [
        ['orders', ['seller_id', 'status', 'paid_at'], 'idx_orders_seller_status_paid'],
        ['orders', ['seller_id', 'status', 'manual_approved_at'], 'idx_orders_seller_status_manual'],
        ['orders', ['seller_id', 'status', 'created_at'], 'idx_orders_seller_status_created'],
        ['orders', ['user_id', 'status', 'paid_at'], 'idx_orders_user_status_paid'],
        ['orders', ['user_id', 'status', 'manual_approved_at'], 'idx_orders_user_status_manual'],
        ['orders', ['user_id', 'status', 'created_at'], 'idx_orders_user_status_created'],
        ['conversation_user', ['user_id', 'conversation_id'], 'idx_conversation_user_reverse'],
        ['messages', ['conversation_id', 'read_at', 'user_id'], 'idx_messages_conversation_unread'],
        ['enrollments', ['completed_at', 'enrollable_type', 'enrollable_id', 'user_id'], 'idx_enrollments_pending_certificate'],
        ['certificates', ['user_id', 'course_id'], 'idx_certificates_user_course'],
        ['certificates', ['user_id', 'mentorship_id'], 'idx_certificates_user_mentorship'],
        ['certificates', ['user_id', 'event_id'], 'idx_certificates_user_event'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as [$tableName, $columns, $indexName]) {
            try {
                if (!Schema::hasTable($tableName)) {
                    continue;
                }

                foreach ($columns as $column) {
                    if (!Schema::hasColumn($tableName, $column)) {
                        continue 2;
                    }
                }

                Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
            } catch (\Throwable) {
                // Permite deploy em bases onde o indice ja foi criado manualmente.
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as [$tableName, , $indexName]) {
            try {
                Schema::table($tableName, fn(Blueprint $table) => $table->dropIndex($indexName));
            } catch (\Throwable) {
                // Permite rollback defensivo em bases divergentes.
            }
        }
    }
};
