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
            $existing = DB::table('plans')->where('slug', $slug)->first();
            $payload = $this->buildPayload($plan, $existing, $now);

            if ($existing) {
                DB::table('plans')->where('id', $existing->id)->update($payload);
            } else {
                DB::table('plans')->insert($payload + ['created_at' => $now]);
            }
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

    private function buildPayload(array $plan, object|null $existing, $now): array
    {
        $price = $existing && $existing->price !== null
            ? round((float) $existing->price, 2)
            : round((float) $plan['price'], 2);

        $isFree = $existing && $existing->is_free !== null
            ? (bool) $existing->is_free
            : (bool) $plan['is_free'];

        $existingBenefits = $this->decodeJsonArray($existing?->benefits);
        $existingPermissions = $this->decodeJsonArray($existing?->permissions);
        $existingPricePeriods = $this->decodeJsonArray($existing?->price_periods);
        $existingPeriodSettings = $this->decodeJsonArray($existing?->period_settings);

        $pricePeriods = $existingPricePeriods !== []
            ? Plan::normalizePricePeriods($existingPricePeriods, $price, $isFree)
            : $plan['price_periods'];

        $periodSettings = $existingPeriodSettings !== []
            ? Plan::normalizePeriodSettings($existingPeriodSettings, $pricePeriods, $isFree)
            : Plan::normalizePeriodSettings($plan['period_settings'], $pricePeriods, $isFree);

        if (!$isFree && $existingPricePeriods !== []) {
            $pricePeriods = Plan::ensureEnabledPeriodPrices($pricePeriods, $periodSettings, $price);
        }

        $permissions = $existingPermissions !== []
            ? Plan::normalizeCommercialPermissions($existingPermissions, $isFree, $price)
            : Plan::normalizeCommercialPermissions(
                $plan['permissions'] ?? [],
                $isFree,
                $price
            );

        return [
            'name' => $this->preferExistingString($existing?->name, $plan['name']),
            'slug' => $plan['slug'],
            'price' => $price,
            'description' => $this->preferExistingString($existing?->description, $plan['description']),
            'period' => $this->preferExistingString($existing?->period, $plan['period']),
            'billing_cycle' => $existing?->billing_cycle ?? $plan['billing_cycle'],
            'prorata' => $existing?->prorata ?? $plan['prorata'],
            'is_active' => $existing?->is_active ?? true,
            'is_free' => $isFree,
            'is_recurring' => $existing?->is_recurring ?? !$isFree,
            'sort_order' => $existing?->sort_order ?? $plan['sort_order'],
            'highlight' => $existing?->highlight ?? $plan['highlight'],
            'is_featured' => $existing?->is_featured ?? $plan['highlight'],
            'benefits' => json_encode($existingBenefits !== [] ? $existingBenefits : ($plan['benefits'] ?? [])),
            'permissions' => json_encode($permissions),
            'price_periods' => json_encode($pricePeriods),
            'period_settings' => json_encode($periodSettings),
            'updated_at' => $now,
        ];
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

    private function preferExistingString(mixed $existing, string $default): string
    {
        if (is_string($existing) && trim($existing) !== '') {
            return $existing;
        }

        return $default;
    }
}
