<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;

final class MarketplaceFee
{
    public static function percent(): float
    {
        $raw = Setting::get('marketplace_platform_fee_percent', '0');
        $value = is_numeric($raw) ? (float) $raw : 0.0;

        if ($value < 0) {
            $value = 0.0;
        }
        if ($value > 100) {
            $value = 100.0;
        }

        return $value;
    }

    public static function amount(float $totalAmount): float
    {
        $totalAmount = (float) $totalAmount;
        if ($totalAmount <= 0) {
            return 0.0;
        }

        $percent = static::percent();
        if ($percent <= 0) {
            return 0.0;
        }

        return round($totalAmount * ($percent / 100), 2);
    }

    public static function deductionPercent(User|int|null $seller): float
    {
        $seller = is_int($seller) ? User::find($seller) : $seller;

        $percent = static::splitPercent('marketplace_split_platform_percent', 10)
            + static::splitPercent('marketplace_split_traffic_percent', 10)
            + static::splitPercent('marketplace_split_superadmin_percent', 10);

        if (!$seller) {
            return min(100, $percent);
        }

        if (!$seller->shouldChargePlatformFee()) {
            return 0.0;
        }

        return min(100, max(0, $percent));
    }

    public static function deductionAmount(float $totalAmount, User|int|null $seller): float
    {
        if ($totalAmount <= 0) {
            return 0.0;
        }

        return round($totalAmount * (static::deductionPercent($seller) / 100), 2);
    }

    private static function splitPercent(string $key, float $default): float
    {
        return max(0, (float) Setting::get($key, $default));
    }
}
