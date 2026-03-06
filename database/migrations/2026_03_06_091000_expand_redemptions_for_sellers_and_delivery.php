<?php

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('redeemable_items', function (Blueprint $table) {
            if (!Schema::hasColumn('redeemable_items', 'provider_type')) {
                $table->string('provider_type', 20)->default('platform')->after('is_active');
            }

            if (!Schema::hasColumn('redeemable_items', 'provider_user_id')) {
                $table->foreignId('provider_user_id')->nullable()->after('provider_type')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('redeemable_items', 'provider_name')) {
                $table->string('provider_name')->nullable()->after('provider_user_id');
            }

            if (!Schema::hasColumn('redeemable_items', 'reference_value')) {
                $table->decimal('reference_value', 10, 2)->nullable()->after('provider_name');
            }

            if (!Schema::hasColumn('redeemable_items', 'delivery_lead_days')) {
                $table->unsignedInteger('delivery_lead_days')->default(7)->after('reference_value');
            }
        });

        Schema::table('redemptions', function (Blueprint $table) {
            if (!Schema::hasColumn('redemptions', 'provider_type')) {
                $table->string('provider_type', 20)->default('platform')->after('redeemable_item_id');
            }

            if (!Schema::hasColumn('redemptions', 'provider_user_id')) {
                $table->foreignId('provider_user_id')->nullable()->after('provider_type')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('redemptions', 'provider_name')) {
                $table->string('provider_name')->nullable()->after('provider_user_id');
            }

            if (!Schema::hasColumn('redemptions', 'reference_value')) {
                $table->decimal('reference_value', 10, 2)->nullable()->after('points_spent');
            }

            if (!Schema::hasColumn('redemptions', 'delivery_notes')) {
                $table->text('delivery_notes')->nullable()->after('admin_notes');
            }

            if (!Schema::hasColumn('redemptions', 'tracking_code')) {
                $table->string('tracking_code')->nullable()->after('delivery_notes');
            }

            if (!Schema::hasColumn('redemptions', 'tracking_url')) {
                $table->string('tracking_url')->nullable()->after('tracking_code');
            }

            if (!Schema::hasColumn('redemptions', 'estimated_delivery_at')) {
                $table->timestamp('estimated_delivery_at')->nullable()->after('tracking_url');
            }

            if (!Schema::hasColumn('redemptions', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('estimated_delivery_at');
            }

            if (!Schema::hasColumn('redemptions', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('shipped_at');
            }

            if (!Schema::hasColumn('redemptions', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }
        });

        $platformName = (string) (Setting::get('company_name')
            ?: Setting::get('app_name')
            ?: config('app.name', 'SOMOS UNN'));

        DB::table('redeemable_items')
            ->whereNull('provider_name')
            ->update([
                'provider_type' => 'platform',
                'provider_name' => $platformName,
                'delivery_lead_days' => 7,
            ]);

        $items = DB::table('redeemable_items')
            ->select('id', 'provider_type', 'provider_user_id', 'provider_name', 'reference_value', 'delivery_lead_days')
            ->get()
            ->keyBy('id');

        DB::table('redemptions')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($items, $platformName): void {
                foreach ($rows as $row) {
                    $item = $items->get($row->redeemable_item_id);
                    $updates = [
                        'provider_type' => $item->provider_type ?? 'platform',
                        'provider_user_id' => $item->provider_user_id ?? null,
                        'provider_name' => $item->provider_name ?? $platformName,
                        'reference_value' => $item->reference_value ?? null,
                    ];

                    if (($row->status ?? '') === 'completed' && empty($row->completed_at)) {
                        $updates['completed_at'] = $row->updated_at ?? $row->created_at;
                    }

                    if (($row->status ?? '') === 'cancelled' && empty($row->cancelled_at)) {
                        $updates['cancelled_at'] = $row->updated_at ?? $row->created_at;
                    }

                    if (empty($row->estimated_delivery_at)) {
                        $leadDays = max(1, (int) ($item->delivery_lead_days ?? 7));
                        $updates['estimated_delivery_at'] = Carbon::parse($row->created_at)->addDays($leadDays);
                    }

                    DB::table('redemptions')->where('id', $row->id)->update($updates);
                }
            });
    }

    public function down(): void
    {
        Schema::table('redemptions', function (Blueprint $table) {
            foreach (['provider_user_id'] as $foreignId) {
                if (Schema::hasColumn('redemptions', $foreignId)) {
                    $table->dropConstrainedForeignId($foreignId);
                }
            }

            $columns = [
                'provider_type',
                'provider_name',
                'reference_value',
                'delivery_notes',
                'tracking_code',
                'tracking_url',
                'estimated_delivery_at',
                'shipped_at',
                'completed_at',
                'cancelled_at',
            ];

            $existing = array_values(array_filter($columns, static fn ($column) => Schema::hasColumn('redemptions', $column)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });

        Schema::table('redeemable_items', function (Blueprint $table) {
            if (Schema::hasColumn('redeemable_items', 'provider_user_id')) {
                $table->dropConstrainedForeignId('provider_user_id');
            }

            $columns = [
                'provider_type',
                'provider_name',
                'reference_value',
                'delivery_lead_days',
            ];

            $existing = array_values(array_filter($columns, static fn ($column) => Schema::hasColumn('redeemable_items', $column)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
