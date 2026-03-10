<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('redeemable_items')) {
            Schema::table('redeemable_items', function (Blueprint $table) {
                if (!Schema::hasColumn('redeemable_items', 'item_type')) {
                    $table->string('item_type', 20)->default('service')->after('provider_name');
                }

                if (!Schema::hasColumn('redeemable_items', 'fulfillment_instructions')) {
                    $table->text('fulfillment_instructions')->nullable()->after('item_type');
                }
            });

            DB::table('redeemable_items')
                ->whereNull('item_type')
                ->update(['item_type' => 'service']);
        }

        if (Schema::hasTable('redemptions')) {
            Schema::table('redemptions', function (Blueprint $table) {
                if (!Schema::hasColumn('redemptions', 'item_type')) {
                    $table->string('item_type', 20)->nullable()->after('provider_name');
                }

                if (!Schema::hasColumn('redemptions', 'fulfillment_instructions')) {
                    $table->text('fulfillment_instructions')->nullable()->after('admin_notes');
                }
            });

            if (Schema::hasTable('redeemable_items')) {
                $items = DB::table('redeemable_items')
                    ->select('id', 'item_type', 'fulfillment_instructions')
                    ->get()
                    ->keyBy('id');

                DB::table('redemptions')
                    ->orderBy('id')
                    ->chunkById(100, function ($rows) use ($items): void {
                        foreach ($rows as $row) {
                            $item = $items->get($row->redeemable_item_id);
                            DB::table('redemptions')
                                ->where('id', $row->id)
                                ->update([
                                    'item_type' => $row->item_type ?: ($item->item_type ?? 'service'),
                                    'fulfillment_instructions' => $row->fulfillment_instructions ?: ($item->fulfillment_instructions ?? null),
                                ]);
                        }
                    });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('redemptions')) {
            Schema::table('redemptions', function (Blueprint $table) {
                $columns = array_values(array_filter([
                    'item_type',
                    'fulfillment_instructions',
                ], static fn (string $column) => Schema::hasColumn('redemptions', $column)));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('redeemable_items')) {
            Schema::table('redeemable_items', function (Blueprint $table) {
                $columns = array_values(array_filter([
                    'item_type',
                    'fulfillment_instructions',
                ], static fn (string $column) => Schema::hasColumn('redeemable_items', $column)));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
