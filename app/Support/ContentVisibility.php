<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class ContentVisibility
{
    protected static array $supportsVisibilityCache = [];

    public static function supportsVisibility(string $table): bool
    {
        if (array_key_exists($table, static::$supportsVisibilityCache)) {
            return static::$supportsVisibilityCache[$table];
        }

        try {
            return static::$supportsVisibilityCache[$table] = Schema::hasTable($table)
                && Schema::hasColumn($table, 'visibility');
        } catch (\Throwable $e) {
            return static::$supportsVisibilityCache[$table] = false;
        }
    }

    public static function applyPublicFilter($query, string $table)
    {
        if (!static::supportsVisibility($table)) {
            return $query;
        }

        return $query->where(function ($nested) {
            $nested->where('visibility', '!=', 'somos_unicas')
                ->orWhereNull('visibility');
        });
    }

    public static function applySomosUnicasFilter($query, string $table)
    {
        if (!static::supportsVisibility($table)) {
            return $query;
        }

        return $query->whereIn('visibility', ['somos_unicas', 'ambos']);
    }
}
