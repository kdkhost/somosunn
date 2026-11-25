<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('order_split_payouts')) {
            return;
        }

        Schema::table('order_split_payouts', function (Blueprint $table) {
            if (!Schema::hasColumn('order_split_payouts', 'scheduled_for')) {
                $table->timestamp('scheduled_for')->nullable()->after('last_attempt_at');
            }
            if (!Schema::hasColumn('order_split_payouts', 'gateway_response')) {
                $table->json('gateway_response')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('order_split_payouts', 'batch_id')) {
                $table->string('batch_id', 64)->nullable()->after('external_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_split_payouts')) {
            return;
        }

        Schema::table('order_split_payouts', function (Blueprint $table) {
            foreach (['scheduled_for', 'gateway_response', 'batch_id'] as $column) {
                if (Schema::hasColumn('order_split_payouts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
