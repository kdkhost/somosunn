<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        if (!Schema::hasColumn('courses', 'slug')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('slug', 191)->nullable()->after('title');
            });
        }

        // Índice/unique é opcional para não quebrar bancos legados com dados inconsistentes.
        // O backfill de slugs gera valores únicos quando a coluna existe.
        if (Schema::hasColumn('courses', 'slug')) {
            try {
                Schema::table('courses', function (Blueprint $table) {
                    $table->unique('slug', 'courses_slug_unique');
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    public function down(): void
    {
        // no-op (safety)
    }
};
