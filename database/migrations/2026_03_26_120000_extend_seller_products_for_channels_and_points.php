<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seller_products')) {
            Schema::table('seller_products', function (Blueprint $table) {
                if (!Schema::hasColumn('seller_products', 'sales_channel')) {
                    $table->string('sales_channel', 30)->default('store_only')->after('status');
                }

                if (!Schema::hasColumn('seller_products', 'external_checkout_url')) {
                    $table->text('external_checkout_url')->nullable()->after('digital_url');
                }

                if (!Schema::hasColumn('seller_products', 'points_reference_value')) {
                    $table->decimal('points_reference_value', 10, 2)->nullable()->after('external_checkout_url');
                }
            });
        }

        if (Schema::hasTable('redeemable_items')) {
            Schema::table('redeemable_items', function (Blueprint $table) {
                if (!Schema::hasColumn('redeemable_items', 'seller_product_id')) {
                    $table->foreignId('seller_product_id')
                        ->nullable()
                        ->after('provider_user_id')
                        ->constrained('seller_products')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('redeemable_items')) {
            Schema::table('redeemable_items', function (Blueprint $table) {
                if (Schema::hasColumn('redeemable_items', 'seller_product_id')) {
                    $table->dropConstrainedForeignId('seller_product_id');
                }
            });
        }

        if (Schema::hasTable('seller_products')) {
            Schema::table('seller_products', function (Blueprint $table) {
                $columns = array_values(array_filter([
                    'sales_channel',
                    'external_checkout_url',
                    'points_reference_value',
                ], static fn (string $column) => Schema::hasColumn('seller_products', $column)));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
