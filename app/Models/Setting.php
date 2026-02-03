<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key','value','group'];

    public static function get($key, $default = null)
    {
        try {
            $s = static::where('key', $key)->first();
            return $s ? $s->value : $default;
        } catch (\Throwable $e) {
            \Log::warning('Configuração indisponível, fallback aplicado: '.$e->getMessage());
            return $default;
        }
    }

    public static function set($key, $value, $group = null)
    {
        try {
            return static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        } catch (\Throwable $e) {
            \Log::warning('Não foi possível gravar configuração: '.$e->getMessage());
            return null;
        }
    }
}
