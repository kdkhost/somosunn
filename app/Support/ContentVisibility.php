<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class ContentVisibility
{
    protected static array $columnSupportCache = [];

    public static function supportsColumn(string $table, string $column): bool
    {
        $cacheKey = $table . ':' . $column;

        if (array_key_exists($cacheKey, static::$columnSupportCache)) {
            return static::$columnSupportCache[$cacheKey];
        }

        try {
            return static::$columnSupportCache[$cacheKey] = Schema::hasTable($table)
                && Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return static::$columnSupportCache[$cacheKey] = false;
        }
    }

    public static function supportsVisibility(string $table): bool
    {
        return static::supportsColumn($table, 'visibility');
    }

    public static function applyPublicFilter($query, string $table)
    {
        $supportsVisibility = static::supportsColumn($table, 'visibility');
        $supportsLegacyFlag = static::supportsColumn($table, 'is_somos_unicas');

        if (!$supportsVisibility && !$supportsLegacyFlag) {
            return $query;
        }

        return $query->where(function ($nested) use ($supportsVisibility, $supportsLegacyFlag) {
            if ($supportsVisibility) {
                $nested->whereIn('visibility', ['ambos', 'somos_unn']);

                if ($supportsLegacyFlag) {
                    $nested->orWhere(function ($legacy) {
                        $legacy->where(function ($missingVisibility) {
                            $missingVisibility->whereNull('visibility')
                                ->orWhere('visibility', '');
                        })->where(function ($legacyFlag) {
                            $legacyFlag->where('is_somos_unicas', 0)
                                ->orWhereNull('is_somos_unicas');
                        });
                    });
                } else {
                    $nested->orWhereNull('visibility')
                        ->orWhere('visibility', '');
                }

                return;
            }

            $nested->where('is_somos_unicas', 0)
                ->orWhereNull('is_somos_unicas');
        });
    }

    public static function applySomosUnicasFilter($query, string $table)
    {
        $supportsVisibility = static::supportsColumn($table, 'visibility');
        $supportsLegacyFlag = static::supportsColumn($table, 'is_somos_unicas');

        if (!$supportsVisibility && !$supportsLegacyFlag) {
            return $query;
        }

        return $query->where(function ($nested) use ($supportsVisibility, $supportsLegacyFlag) {
            if ($supportsVisibility) {
                $nested->whereIn('visibility', ['somos_unicas', 'ambos']);

                if ($supportsLegacyFlag) {
                    $nested->orWhere(function ($legacy) {
                        $legacy->where(function ($missingVisibility) {
                            $missingVisibility->whereNull('visibility')
                                ->orWhere('visibility', '');
                        })->where('is_somos_unicas', 1);
                    });
                }

                return;
            }

            $nested->where('is_somos_unicas', 1);
        });
    }
}
