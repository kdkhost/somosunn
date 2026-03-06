<?php

namespace App\Services;

use App\Models\Setting;

class PointsExchangeService
{
    public const BASE_POINTS_KEY = 'points_exchange_base_points';
    public const BASE_AMOUNT_KEY = 'points_exchange_base_amount';

    public function getBasePoints(): int
    {
        $value = (int) Setting::get(self::BASE_POINTS_KEY, 100);
        return $value > 0 ? $value : 100;
    }

    public function getBaseAmount(): float
    {
        $value = (float) str_replace(',', '.', (string) Setting::get(self::BASE_AMOUNT_KEY, '1.00'));
        return $value > 0 ? round($value, 2) : 1.00;
    }

    public function getPointValue(): float
    {
        return round($this->getBaseAmount() / $this->getBasePoints(), 4);
    }

    public function moneyToPoints(float $amount): int
    {
        $amount = max(0, round($amount, 2));

        if ($amount <= 0) {
            return 0;
        }

        return (int) ceil(($amount / $this->getBaseAmount()) * $this->getBasePoints());
    }

    public function pointsToMoney(int $points): float
    {
        $points = max(0, $points);

        if ($points <= 0) {
            return 0.0;
        }

        return round(($points / $this->getBasePoints()) * $this->getBaseAmount(), 2);
    }

    public function settings(): array
    {
        return [
            'base_points' => $this->getBasePoints(),
            'base_amount' => $this->getBaseAmount(),
            'point_value' => $this->getPointValue(),
        ];
    }

    public function persist(array $data): void
    {
        $basePoints = max(1, (int) ($data['base_points'] ?? $this->getBasePoints()));
        $baseAmount = max(0.01, round((float) ($data['base_amount'] ?? $this->getBaseAmount()), 2));

        Setting::set(self::BASE_POINTS_KEY, (string) $basePoints, 'points');
        Setting::set(self::BASE_AMOUNT_KEY, number_format($baseAmount, 2, '.', ''), 'points');
    }
}
