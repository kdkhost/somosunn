<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $plans = [
            'starter' => $this->definitionFromBlueprint(
                'Gratuito',
                'starter',
                0.0,
                1,
                false,
                true,
                Plan::blueprintForPlan('free', true)
            ),
            'pro' => $this->definitionFromBlueprint(
                'Pro',
                'pro',
                97.0,
                2,
                true,
                false,
                Plan::blueprintForPlan('pro', false)
            ),
            'elite' => $this->definitionFromBlueprint(
                'Elite',
                'elite',
                297.0,
                3,
                false,
                false,
                Plan::blueprintForPlan('elite', false)
            ),
        ];

        foreach ($plans as $slug => $plan) {
            DB::table('plans')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $plan['name'],
                    'slug' => $plan['slug'],
                    'price' => $plan['price'],
                    'description' => $plan['description'],
                    'period' => $plan['period'],
                    'billing_cycle' => $plan['billing_cycle'],
                    'prorata' => $plan['prorata'],
                    'is_active' => true,
                    'is_free' => $plan['is_free'],
                    'is_recurring' => !$plan['is_free'],
                    'sort_order' => $plan['sort_order'],
                    'highlight' => $plan['highlight'],
                    'is_featured' => $plan['highlight'],
                    'benefits' => json_encode($plan['benefits'] ?? []),
                    'permissions' => json_encode(Plan::normalizeCommercialPermissions(
                        $plan['permissions'] ?? [],
                        (bool) ($plan['is_free'] ?? false),
                        (float) ($plan['price'] ?? 0)
                    )),
                    'price_periods' => json_encode($plan['price_periods']),
                    'period_settings' => json_encode($plan['period_settings']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function definitionFromBlueprint(
        string $name,
        string $slug,
        float $price,
        int $sortOrder,
        bool $highlight,
        bool $isFree,
        ?array $blueprint
    ): array {
        $blueprint = $blueprint ?? [];
        $pricePeriods = Plan::normalizePricePeriods([], $price, $isFree);
        $periodSettings = Plan::normalizePeriodSettings(
            $blueprint['period_settings'] ?? [],
            $pricePeriods,
            $isFree
        );

        if (!$isFree) {
            $pricePeriods = Plan::ensureEnabledPeriodPrices($pricePeriods, $periodSettings, $price);
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'price' => $price,
            'description' => (string) ($blueprint['description'] ?? ''),
            'benefits' => is_array($blueprint['benefits'] ?? null) ? $blueprint['benefits'] : [],
            'permissions' => is_array($blueprint['permissions'] ?? null) ? $blueprint['permissions'] : [],
            'period' => (string) ($blueprint['period'] ?? 'mensal'),
            'billing_cycle' => 1,
            'prorata' => false,
            'is_free' => $isFree,
            'sort_order' => $sortOrder,
            'highlight' => $highlight,
            'price_periods' => $pricePeriods,
            'period_settings' => $periodSettings,
        ];
    }
}
