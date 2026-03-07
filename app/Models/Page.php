<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Page extends Model
{
    use HasFactory;

    protected static ?bool $pageTableAvailable = null;

    protected $fillable = [
        'slug',
        'title',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Busca uma page pelo slug ou retorna null.
     */
    public static function findBySlug(string $slug): ?self
    {
        if (!static::tableAvailable()) {
            return null;
        }

        return static::where('slug', $slug)->first();
    }

    /**
     * Retorna os dados de uma página pelo slug sem quebrar se a tabela não existir.
     */
    public static function dataBySlug(string $slug, array $default = []): array
    {
        $data = static::findBySlug($slug)?->data;

        return is_array($data) ? $data : $default;
    }

    /**
     * Informa se a tabela de páginas CMS está disponível na base atual.
     */
    public static function tableAvailable(): bool
    {
        if (static::$pageTableAvailable !== null) {
            return static::$pageTableAvailable;
        }

        try {
            static::$pageTableAvailable = Schema::hasTable((new static())->getTable());
        } catch (\Throwable) {
            static::$pageTableAvailable = false;
        }

        return static::$pageTableAvailable;
    }

    public static function resetTableAvailabilityCache(): void
    {
        static::$pageTableAvailable = null;
    }

    /**
     * Retorna um valor dentro de data pelo(s) path(s) fornecido(s).
     * Aceita dot-notation: get('hero.title').
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $data = $this->data ?? [];
        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }
}
