<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

trait HandlesPlanFormData
{
    protected function planFeatures(): array
    {
        return Plan::siteFeatureLabels();
    }

    protected function planFeatureGroups(): array
    {
        return Plan::siteFeatureGroups();
    }

    protected function validatePlanData(Request $request, ?int $id = null): array
    {
        $this->normalizePlanMoneyInputs($request);

        if ($request->has('slug')) {
            $request->merge(['slug' => trim((string) $request->input('slug'))]);
        }

        $allowedFeatures = array_keys($this->planFeatures());
        $periodKeys = Plan::PERIOD_KEYS;

        $rules = [
            'name' => 'required|string|max:120',
            'slug' => [
                'nullable',
                'string',
                'max:160',
                Rule::unique('plans', 'slug')->ignore($id),
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'remove_image' => 'nullable|boolean',
            'price' => 'required|numeric|min:0',
            'period' => 'required|string|in:' . implode(',', array_merge($periodKeys, ['vitalicio', 'vitalício'])),
            'highlight' => 'nullable|boolean',
            'coupons_enabled' => 'nullable|boolean',
            'benefits' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => [
                'string',
                Rule::in($allowedFeatures),
            ],
            'comparison' => 'nullable|array',
            'comparison.connections_per_month' => 'nullable|string|max:50',
            'comparison.group_mentorship' => 'nullable|string|max:50',
            'comparison.individual_mentorship' => 'nullable|string|max:50',
            'comparison.priority_support' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_recurring' => 'nullable|boolean',
            'is_free' => 'nullable|boolean',
            'billing_cycle' => 'nullable|integer|min:1|max:12',
            'price_periods' => 'nullable|array',
            'period_settings' => 'nullable|array',
        ];

        foreach ($periodKeys as $period) {
            $rules['price_periods.' . $period] = 'nullable|numeric|min:0';
            $rules['period_settings.' . $period . '.enabled'] = 'nullable|boolean';
        }

        $data = $request->validate($rules);

        $data['price'] = round((float) $data['price'], 2);
        $data['is_free'] = $request->boolean('is_free');
        $data['highlight'] = $request->boolean('highlight');
        $data['is_featured'] = $data['highlight'];
        $data['coupons_enabled'] = $request->boolean('coupons_enabled');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['billing_cycle'] = (int) ($data['billing_cycle'] ?? 1);

        if ($data['is_free']) {
            $data['price'] = 0.0;
            $data['is_recurring'] = false;
            $data['billing_cycle'] = 1;
        }

        $data['price_periods'] = Plan::normalizePricePeriods(
            is_array($request->input('price_periods')) ? $request->input('price_periods') : [],
            (float) ($data['price'] ?? 0),
            (bool) $data['is_free']
        );

        $data['period_settings'] = Plan::normalizePeriodSettings(
            is_array($request->input('period_settings')) ? $request->input('period_settings') : [],
            $data['price_periods'],
            (bool) $data['is_free']
        );

        if (!(bool) $data['is_free']) {
            $data['price_periods'] = Plan::ensureEnabledPeriodPrices(
                $data['price_periods'],
                $data['period_settings'],
                (float) ($data['price'] ?? 0)
            );
        }

        $requestedPeriod = $this->normalizeRequestedPeriod((string) ($data['period'] ?? 'mensal'));
        if ($requestedPeriod === 'vitalicio') {
            $data['period'] = 'vitalicio';
        } else {
            $data['period'] = $this->resolveDefaultPeriod($data['period_settings'], $requestedPeriod);
        }

        $data['slug'] = $this->generateUniqueSlug($data['slug'] ?: $data['name'], $id);
        $data['benefits'] = $this->normalizeBenefits($request->input('benefits', ''));
        $data['permissions'] = Plan::normalizeCommercialPermissions(
            $this->normalizePermissions($request->input('permissions', []), $allowedFeatures),
            (bool) $data['is_free'],
            (float) ($data['price'] ?? 0)
        );
        $data['comparison'] = $this->normalizeComparison($request->input('comparison', []));

        return $data;
    }

    protected function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        if ($base === '') {
            $base = 'plano';
        }

        for ($suffix = 1; $suffix <= 1000; $suffix++) {
            $slug = $suffix === 1 ? $base : $base . '-' . $suffix;
            $exists = Plan::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists();

            if (!$exists) {
                return $slug;
            }
        }

        throw new \RuntimeException('Nao foi possivel gerar um slug unico para o plano.');
    }

    protected function normalizePlanMoneyInputs(Request $request): void
    {
        $merge = [];

        if ($request->has('price')) {
            $parsed = Plan::parseMoneyValue($request->input('price'));
            if ($parsed !== null) {
                $merge['price'] = $parsed;
            }
        }

        $rawPeriods = $request->input('price_periods', []);
        if (is_array($rawPeriods)) {
            $normalizedPeriods = [];
            foreach (Plan::PERIOD_KEYS as $period) {
                if (!array_key_exists($period, $rawPeriods)) {
                    continue;
                }

                $parsed = Plan::parseMoneyValue($rawPeriods[$period]);
                if ($parsed !== null) {
                    $normalizedPeriods[$period] = $parsed;
                }
            }

            if ($normalizedPeriods !== []) {
                $merge['price_periods'] = array_merge($rawPeriods, $normalizedPeriods);
            }
        }

        if ($merge !== []) {
            $request->merge($merge);
        }
    }

    protected function normalizeBenefits(mixed $raw): array
    {
        if (is_array($raw)) {
            $lines = $raw;
        } else {
            $lines = explode("\n", str_replace("\r", '', (string) $raw));
        }

        return array_values(array_filter(array_map(
            static fn ($value) => trim((string) $value),
            $lines
        ), static fn ($value) => $value !== ''));
    }

    protected function normalizePermissions(mixed $rawPermissions, array $allowed): array
    {
        if (!is_array($rawPermissions)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            $rawPermissions,
            static fn ($name) => is_string($name) && in_array($name, $allowed, true)
        )));
    }

    protected function normalizeComparison(mixed $comparison): array
    {
        if (!is_array($comparison)) {
            $comparison = [];
        }

        return [
            'connections_per_month' => isset($comparison['connections_per_month']) ? trim((string) $comparison['connections_per_month']) : null,
            'group_mentorship' => isset($comparison['group_mentorship']) ? trim((string) $comparison['group_mentorship']) : null,
            'individual_mentorship' => isset($comparison['individual_mentorship']) ? trim((string) $comparison['individual_mentorship']) : null,
            'priority_support' => $this->inputBoolean($comparison['priority_support'] ?? false),
        ];
    }

    protected function inputBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }

    protected function resolveDefaultPeriod(array $periodSettings, string $requestedPeriod): string
    {
        if (($periodSettings[$requestedPeriod]['enabled'] ?? false) === true) {
            return $requestedPeriod;
        }

        foreach (Plan::PERIOD_KEYS as $period) {
            if (($periodSettings[$period]['enabled'] ?? false) === true) {
                return $period;
            }
        }

        return 'mensal';
    }

    protected function normalizeRequestedPeriod(string $period): string
    {
        $period = strtolower(trim($period));

        if (in_array($period, ['vitalicio', 'vitalício'], true)) {
            return 'vitalicio';
        }

        return Plan::sanitizePeriod($period);
    }
}
