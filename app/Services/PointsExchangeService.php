<?php

namespace App\Services;

use App\Models\Setting;

class PointsExchangeService
{
    public const COIN_NAME = 'UNNBIT';
    public const BASE_POINTS_KEY = 'points_exchange_base_points';
    public const BASE_AMOUNT_KEY = 'points_exchange_base_amount';
    public const UNIT_VALUE_KEY = 'unnbit_unit_value_brl';
    public const USD_REFERENCE_RATE_KEY = 'unnbit_usd_brl_reference';
    public const MARKET_NOTE_KEY = 'unnbit_market_note';
    public const LAST_REPRICED_AT_KEY = 'unnbit_last_repriced_at';

    public function getBasePoints(): int
    {
        $value = (int) Setting::get(self::BASE_POINTS_KEY, 100);
        return $value > 0 ? $value : 100;
    }

    public function getBaseAmount(): float
    {
        $stored = $this->normalizeDecimal(Setting::get(self::BASE_AMOUNT_KEY, null), 2);
        if ($stored > 0) {
            return $stored;
        }

        return round($this->getBasePoints() * $this->getUnitValue(), 2);
    }

    public function getUnitValue(): float
    {
        $stored = $this->normalizeDecimal(Setting::get(self::UNIT_VALUE_KEY, null), 4);
        if ($stored > 0) {
            return $stored;
        }

        $baseAmount = $this->normalizeDecimal(Setting::get(self::BASE_AMOUNT_KEY, null), 2);
        $fallback = round($baseAmount / max(1, $this->getBasePoints()), 4);

        return $fallback > 0 ? $fallback : 0.01;
    }

    public function getUsdReferenceRate(): float
    {
        $value = $this->normalizeDecimal(Setting::get(self::USD_REFERENCE_RATE_KEY, '1.0000'), 4);

        return $value > 0 ? $value : 1.0000;
    }

    public function getMarketNote(): string
    {
        return trim((string) Setting::get(self::MARKET_NOTE_KEY, ''));
    }

    public function getLastRepricedAt(): ?string
    {
        $value = trim((string) Setting::get(self::LAST_REPRICED_AT_KEY, ''));

        return $value !== '' ? $value : null;
    }

    public function getPointValue(): float
    {
        return $this->getUnitValue();
    }

    public function moneyToPoints(float $amount): int
    {
        $amount = max(0, round($amount, 2));

        if ($amount <= 0) {
            return 0;
        }

        return (int) ceil($amount / $this->getUnitValue());
    }

    public function pointsToMoney(int $points): float
    {
        $points = max(0, $points);

        if ($points <= 0) {
            return 0.0;
        }

        return round($points * $this->getUnitValue(), 2);
    }

    public function valuationTable(array $units = [1, 10, 50, 100, 500, 1000]): array
    {
        return collect($units)
            ->map(fn ($unit) => max(1, (int) $unit))
            ->unique()
            ->values()
            ->map(fn (int $unit) => [
                'units' => $unit,
                'amount' => $this->pointsToMoney($unit),
            ])
            ->all();
    }

    public function settings(): array
    {
        return [
            'coin_name' => self::COIN_NAME,
            'base_points' => $this->getBasePoints(),
            'base_amount' => $this->getBaseAmount(),
            'point_value' => $this->getPointValue(),
            'unit_value_brl' => $this->getUnitValue(),
            'usd_reference_rate' => $this->getUsdReferenceRate(),
            'market_note' => $this->getMarketNote(),
            'last_repriced_at' => $this->getLastRepricedAt(),
            'valuation_table' => $this->valuationTable(),
        ];
    }

    public function persist(array $data): void
    {
        $basePoints = max(1, (int) ($data['base_points'] ?? $this->getBasePoints()));
        $unitValue = $this->resolveUnitValue($data, $basePoints);
        $baseAmount = round($basePoints * $unitValue, 2);
        $usdReferenceRate = max(0.0001, round((float) ($data['usd_reference_rate'] ?? $this->getUsdReferenceRate()), 4));
        $marketNote = trim((string) ($data['market_note'] ?? $this->getMarketNote()));

        Setting::set(self::BASE_POINTS_KEY, (string) $basePoints, 'points');
        Setting::set(self::BASE_AMOUNT_KEY, number_format($baseAmount, 2, '.', ''), 'points');
        Setting::set(self::UNIT_VALUE_KEY, number_format($unitValue, 4, '.', ''), 'points');
        Setting::set(self::USD_REFERENCE_RATE_KEY, number_format($usdReferenceRate, 4, '.', ''), 'points');
        Setting::set(self::MARKET_NOTE_KEY, $marketNote, 'points');
        Setting::set(self::LAST_REPRICED_AT_KEY, now()->toDateTimeString(), 'points');
    }

    private function resolveUnitValue(array $data, int $basePoints): float
    {
        $explicitUnitValue = isset($data['unit_value']) ? (float) $data['unit_value'] : null;
        if ($explicitUnitValue !== null && $explicitUnitValue > 0) {
            return round($explicitUnitValue, 4);
        }

        $baseAmount = isset($data['base_amount']) ? (float) $data['base_amount'] : null;
        if ($baseAmount !== null && $baseAmount > 0) {
            return round($baseAmount / max(1, $basePoints), 4);
        }

        return $this->getUnitValue();
    }

    private function normalizeDecimal(mixed $value, int $precision): float
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return 0.0;
        }

        $normalized = str_replace(['R$', ' ', "\u{00A0}"], '', $normalized);
        if (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        }

        $float = (float) $normalized;

        return $float > 0 ? round($float, $precision) : 0.0;
    }
}
