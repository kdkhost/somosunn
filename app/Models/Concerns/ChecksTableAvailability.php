<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Schema;

trait ChecksTableAvailability
{
    protected static ?bool $tableAvailable = null;

    public static function tableAvailable(): bool
    {
        if (static::$tableAvailable !== null) {
            return static::$tableAvailable;
        }

        try {
            static::$tableAvailable = Schema::hasTable((new static())->getTable());
        } catch (\Throwable) {
            static::$tableAvailable = false;
        }

        return static::$tableAvailable;
    }
}
