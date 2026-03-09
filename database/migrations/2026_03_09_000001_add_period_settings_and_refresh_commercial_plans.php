<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'period_settings')) {
                $table->json('period_settings')->nullable()->after('price_periods');
            }
        });

        $now = now();
        $plans = DB::table('plans')->get();

        $freePlanIds = [];
        $hasExplicitFreePlan = DB::table('plans')->where('is_free', true)->exists();

        foreach ($plans as $row) {
            $slug = strtolower(trim((string) ($row->slug ?? '')));
            $price = round((float) ($row->price ?? 0), 2);
            $isFree = (bool) ($row->is_free ?? false) || $price <= 0;

            if ($isFree) {
                $freePlanIds[] = (int) $row->id;
            }

            $existingPricePeriods = $this->decodeJsonArray($row->price_periods ?? null);
            $pricePeriods = Plan::normalizePricePeriods($existingPricePeriods, $price, $isFree);

            $existingPeriodSettings = $this->decodeJsonArray($row->period_settings ?? null);
            $periodSettings = Plan::normalizePeriodSettings(
                $existingPeriodSettings,
                $pricePeriods,
                $isFree
            );

            $update = [
                'price_periods' => json_encode($pricePeriods),
                'period_settings' => json_encode($periodSettings),
                'updated_at' => $now,
            ];

            if ($isFree) {
                $blueprint = Plan::blueprintForPlan($slug, true);
                if ($blueprint) {
                    if (!$hasExplicitFreePlan) {
                        $update['is_free'] = true;
                        $hasExplicitFreePlan = true;
                    }

                    if (trim((string) ($row->description ?? '')) === '') {
                        $update['description'] = $blueprint['description'];
                    }

                    if ($this->decodeJsonArray($row->benefits ?? null) === []) {
                        $update['benefits'] = json_encode($blueprint['benefits']);
                    }

                    if ($this->decodeJsonArray($row->permissions ?? null) === []) {
                        $update['permissions'] = json_encode(Plan::normalizeCommercialPermissions(
                            $blueprint['permissions'],
                            true,
                            0
                        ));
                    }

                    if (trim((string) ($row->period ?? '')) === '') {
                        $update['period'] = $blueprint['period'];
                    }

                    if ($existingPeriodSettings === []) {
                        $update['period_settings'] = json_encode($blueprint['period_settings']);
                    }

                    if ($existingPricePeriods === []) {
                        $update['price_periods'] = json_encode(['mensal' => 0.0]);
                    }
                }
            } elseif (in_array($slug, ['pro', 'elite'], true)) {
                $blueprint = Plan::blueprintForPlan($slug, false);

                if ($blueprint) {
                    if ($existingPeriodSettings === []) {
                        $pricePeriods = Plan::ensureEnabledPeriodPrices(
                            $pricePeriods,
                            $blueprint['period_settings'],
                            $price
                        );
                        $update['price_periods'] = json_encode($pricePeriods);
                        $update['period_settings'] = json_encode($blueprint['period_settings']);
                    }

                    if (trim((string) ($row->description ?? '')) === '') {
                        $update['description'] = $blueprint['description'];
                    }

                    if ($this->decodeJsonArray($row->benefits ?? null) === []) {
                        $update['benefits'] = json_encode($blueprint['benefits']);
                    }

                    if ($this->decodeJsonArray($row->permissions ?? null) === []) {
                        $update['permissions'] = json_encode(Plan::normalizeCommercialPermissions(
                            $blueprint['permissions'],
                            false,
                            $price
                        ));
                    }

                    if (trim((string) ($row->period ?? '')) === '') {
                        $update['period'] = $blueprint['period'];
                    }
                }
            }

            DB::table('plans')->where('id', $row->id)->update($update);
        }

        if (!$hasExplicitFreePlan && $freePlanIds !== []) {
            DB::table('plans')
                ->where('id', $freePlanIds[0])
                ->update(['is_free' => true, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'period_settings')) {
                $table->dropColumn('period_settings');
            }
        });
    }

    private function decodeJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
};
