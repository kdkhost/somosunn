<?php

namespace App\Models;

use App\Support\UploadStorage;
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
            \Log::warning('Configuracoes indisponiveis, cache local vazio: ' . $e->getMessage());
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
    }
}
