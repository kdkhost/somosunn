<?php

namespace App\Models;

use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'unn:settings:all:v1';
    private const CACHE_TTL_SECONDS = 300;

    protected $fillable = ['key', 'value', 'group'];

    protected static bool $runtimeCacheLoaded = false;
    protected static array $runtimeCache = [];

    protected static function booted(): void
    {
        static::saved(static function (): void {
            static::flushRuntimeCache();
        });

        static::deleted(static function (): void {
            static::flushRuntimeCache();
        });
    }

    protected static function loadRuntimeCache(): void
    {
        if (static::$runtimeCacheLoaded) {
            return;
        }

        try {
            $cached = null;
            if (!app()->runningUnitTests()) {
                try {
                    $cached = Cache::get(self::CACHE_KEY);
                } catch (\Throwable) {
                    $cached = null;
                }
            }

            if (is_array($cached)) {
                static::$runtimeCache = $cached;
            } else {
                static::$runtimeCache = static::query()->pluck('value', 'key')->toArray();

                if (!app()->runningUnitTests()) {
                    try {
                        Cache::put(self::CACHE_KEY, static::$runtimeCache, self::CACHE_TTL_SECONDS);
                    } catch (\Throwable) {
                        // Cache persistente indisponivel; mantem cache em memoria
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Configuracoes indisponiveis, cache local vazio: ' . $e->getMessage());
            static::$runtimeCache = [];
        } finally {
            static::$runtimeCacheLoaded = true;
        }
    }

    /**
     * Retorna todas as configuracoes em array [key => value] utilizando o cache em memoria/Laravel.
     */
    public static function allValues(): array
    {
        static::loadRuntimeCache();

        return static::$runtimeCache;
    }

    public static function get($key, $default = null)
    {
        try {
            static::loadRuntimeCache();

            if (array_key_exists($key, static::$runtimeCache)) {
                return static::$runtimeCache[$key];
            }

            if (static::$runtimeCacheLoaded) {
                $freshValue = static::query()->where('key', $key)->value('value');

                if ($freshValue !== null) {
                    static::$runtimeCache[$key] = $freshValue;

                    return $freshValue;
                }
            }

            return $default;
        } catch (\Throwable $e) {
            \Log::warning('Configuracao indisponivel, fallback aplicado: ' . $e->getMessage());

            return $default;
        }
    }

    public static function set($key, $value, $group = null)
    {
        try {
            $record = static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
            static::$runtimeCache[$key] = $record ? $record->value : $value;
            static::$runtimeCacheLoaded = true;

            try {
                Cache::forget(self::CACHE_KEY);
            } catch (\Throwable) {
                // Ignora erro de backend de cache
            }

            return $record;
        } catch (\Throwable $e) {
            \Log::warning('Nao foi possivel gravar configuracao: ' . $e->getMessage());

            return null;
        }
    }

    public static function getUrl(string $key, ?string $default = ''): string
    {
        $value = (string) static::get($key, '');

        return (string) (UploadStorage::url($value, $default) ?? $default);
    }

    public static function flushRuntimeCache(): void
    {
        static::$runtimeCacheLoaded = false;
        static::$runtimeCache = [];

        try {
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable) {
            // Ignora erro caso cache ainda nao esteja disponivel durante boot
        }
    }
}
