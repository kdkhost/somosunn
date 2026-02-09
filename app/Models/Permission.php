<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'label', 'category', 'sort_order'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Retorna permissões agrupadas por categoria.
     */
    public static function grouped()
    {
        return static::orderBy('sort_order')
            ->get()
            ->groupBy(function ($permission) {
                return $permission->category ?? 'Outros';
            });
    }
}
