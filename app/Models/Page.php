<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

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
        return static::where('slug', $slug)->first();
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
