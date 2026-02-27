<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    use HasFactory;

    protected $table = 'site_contents';

    protected $fillable = [
        'slug',
        'key',
        'value',
        'type',
    ];

    public static function getValue(string $slug, string $key, ?string $default = null): ?string
    {
        try {
            $value = static::query()
                ->where('slug', $slug)
                ->where('key', $key)
                ->value('value');

            return $value !== null ? (string) $value : $default;
        } catch (\Throwable $e) {
            \Log::warning('SiteContent unavailable, fallback applied: ' . $e->getMessage());
            return $default;
        }
    }

    public static function putValue(string $slug, string $key, ?string $value, string $type = 'text'): self
    {
        return static::query()->updateOrCreate(
            ['slug' => $slug, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    public static function resolve(string $path, ?string $default = null): ?string
    {
        $parts = explode('.', $path, 2);
        if (count($parts) === 2) {
            return static::getValue($parts[0], $parts[1], $default);
        }

        return static::getValue('global', $path, $default);
    }
}
