<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $indexes = [
        ['users', ['points'], 'idx_users_points'],
        ['users', ['role', 'level'], 'idx_users_role_level'],
        ['courses', ['slug'], 'idx_courses_slug'],
        ['courses', ['status', 'published'], 'idx_courses_status_published'],
        ['courses', ['user_id', 'status'], 'idx_courses_user_status'],
        ['lessons', ['course_id', 'order'], 'idx_lessons_course_order'],
        ['lessons', ['slug'], 'idx_lessons_slug'],
        ['enrollments', ['user_id', 'enrollable_type', 'enrollable_id'], 'idx_enrollments_user_enrollable'],
        ['payments', ['order_id', 'status'], 'idx_payments_order_status'],
        ['activity_logs', ['user_id', 'created_at'], 'idx_activity_logs_user_date'],
        ['service_visits', ['visited_at', 'service_type', 'service_id'], 'idx_service_visits_composite'],
        ['points_logs', ['user_id', 'created_at'], 'idx_points_logs_user_date'],
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
                // Ignore if index already exists
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as [$tableName, , $indexName]) {
            try {
                if (Schema::hasTable($tableName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                        $table->dropIndex($indexName);
                    });
                }
            } catch (\Throwable) {
                // Ignore errors on rollback
            }
        }
    }
};
