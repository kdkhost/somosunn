<?php

namespace App\Support;

use App\Models\Setting;

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
}

