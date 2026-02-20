<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group'];

    protected static bool $runtimeCacheLoaded = false;
    protected static array $runtimeCache = [];

    protected static function loadRuntimeCache(): void
    {
        if (static::$runtimeCacheLoaded) {
            return;
        }

        try {
            static::$runtimeCache = static::query()->pluck('value', 'key')->toArray();
        } catch (\Throwable $e) {
            \Log::warning('Configurações indisponíveis, cache local vazio: ' . $e->getMessage());
            static::$runtimeCache = [];
        } finally {
            static::$runtimeCacheLoaded = true;
        }
    }

    public static function get($key, $default = null)
    {
        try {
            static::loadRuntimeCache();

            if (array_key_exists($key, static::$runtimeCache)) {
                return static::$runtimeCache[$key];
            }

            return $default;
        } catch (\Throwable $e) {
            \Log::warning('Configuração indisponível, fallback aplicado: ' . $e->getMessage());
            return $default;
        }
    }

    public static function set($key, $value, $group = null)
    {
        try {
            $record = static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
            static::$runtimeCache[$key] = $record ? $record->value : $value;
            static::$runtimeCacheLoaded = true;
            return $record;
        } catch (\Throwable $e) {
            \Log::warning('Não foi possível gravar configuração: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Resolve a URL de um asset de configuração.
     *
     * @param string $key
     * @param string|null $default
     * @return string
     */
    public static function getUrl(string $key, ?string $default = ''): string
    {
        $val = static::get($key);
        if (!$val) {
            return (string) $default;
        }

        if (filter_var($val, FILTER_VALIDATE_URL)) {
            return $val;
        }

        // Se o valor já começar com 'storage/', usa direto no asset
        if (str_starts_with($val, 'storage/')) {
            return asset($val);
        }

        // Se começar com 'uploads/', é provável que esteja na raiz pública (legacy ou custom)
        if (str_starts_with($val, 'uploads/')) {
            return asset($val);
        }

        // Fallback genérico para storage (padrão Laravel)
        return asset('storage/' . $val);
    }

    public static function flushRuntimeCache(): void
    {
        static::$runtimeCacheLoaded = false;
        static::$runtimeCache = [];
    }
}
