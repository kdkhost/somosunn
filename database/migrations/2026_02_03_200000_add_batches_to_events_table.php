<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'batch_1_price')) {
                $table->decimal('batch_1_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('events', 'batch_1_deadline')) {
                $table->dateTime('batch_1_deadline')->nullable();
            }

            if (!Schema::hasColumn('events', 'batch_2_price')) {
                $table->decimal('batch_2_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('events', 'batch_2_deadline')) {
                $table->dateTime('batch_2_deadline')->nullable();
            }

            if (!Schema::hasColumn('events', 'batch_3_price')) {
                $table->decimal('batch_3_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('events', 'batch_3_deadline')) {
                $table->dateTime('batch_3_deadline')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $columns = [
                'batch_1_price', 'batch_1_deadline',
                'batch_2_price', 'batch_2_deadline',
                'batch_3_price', 'batch_3_deadline',
            ];

            $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('events', $column)));
            if ($existing) {
                $table->dropColumn($existing);
            }
        });
    }
};
