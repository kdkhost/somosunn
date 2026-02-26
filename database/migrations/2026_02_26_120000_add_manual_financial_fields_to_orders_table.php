<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'paid_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('paid_at')->nullable()->after('transaction_id');
            });
        }

        if (!Schema::hasColumn('orders', 'cancelled_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('cancelled_at')->nullable()->after('paid_at');
            });
        }

        if (!Schema::hasColumn('orders', 'payment_method')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('payment_method', 50)->nullable()->after('gateway');
            });
        }

        if (!Schema::hasColumn('orders', 'is_manual_approval')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('is_manual_approval')->default(false)->after('payment_method');
                $table->index('is_manual_approval');
            });
        }

        if (!Schema::hasColumn('orders', 'manual_approved_by')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('manual_approved_by')->nullable()->after('is_manual_approval');
                $table->index('manual_approved_by');
            });
        }

        if (!Schema::hasColumn('orders', 'manual_approved_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('manual_approved_at')->nullable()->after('manual_approved_by');
                $table->index('manual_approved_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'manual_approved_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('manual_approved_at');
            });
        }

        if (Schema::hasColumn('orders', 'manual_approved_by')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('manual_approved_by');
            });
        }

        if (Schema::hasColumn('orders', 'is_manual_approval')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('is_manual_approval');
            });
        }

        if (Schema::hasColumn('orders', 'payment_method')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('payment_method');
            });
        }

        if (Schema::hasColumn('orders', 'cancelled_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('cancelled_at');
            });
        }

        if (Schema::hasColumn('orders', 'paid_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('paid_at');
            });
        }
    }
};
