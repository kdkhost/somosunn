<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('events', ['published', 'type', 'start_at'], 'events_public_type_start_idx');
        $this->addIndex('testimonials', ['status', 'is_active', 'is_featured', 'created_at'], 'testimonials_public_sort_idx');
        $this->addIndex('magazines', ['status', 'visibility', 'is_featured', 'published_at'], 'magazines_public_sort_idx');
        $this->addIndex('magazines', ['status', 'category', 'published_at'], 'magazines_category_sort_idx');
    }

    public function down(): void
    {
        $this->dropIndex('events', 'events_public_type_start_idx');
        $this->dropIndex('testimonials', 'testimonials_public_sort_idx');
        $this->dropIndex('magazines', 'magazines_public_sort_idx');
        $this->dropIndex('magazines', 'magazines_category_sort_idx');
    }

    private function addIndex(string $table, array $columns, string $name): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
        } catch (\Throwable) {
            // Compatibilidade com instalações que já receberam o índice manualmente.
        }
    }

    private function dropIndex(string $table, string $name): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
        } catch (\Throwable) {
            // Rollback idempotente em instalações legadas.
        }
    }
};
