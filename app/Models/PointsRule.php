<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointsRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'category',
        'description',
        'points',
        'active',
        'icon',
        'sort_order',
        'repeatable',
        'max_daily',
    ];

    protected $casts = [
        'active' => 'boolean',
        'repeatable' => 'boolean',
        'points' => 'integer',
        'max_daily' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Categorias disponíveis para regras de pontuação.
     */
    public const CATEGORIES = [
        'engajamento' => [
            'label' => 'Engajamento',
            'color' => 'primary',
            'icon' => 'fas fa-heart',
        ],
        'aprendizado' => [
            'label' => 'Aprendizado',
            'color' => 'success',
            'icon' => 'fas fa-graduation-cap',
        ],
        'comunidade' => [
            'label' => 'Comunidade',
            'color' => 'info',
            'icon' => 'fas fa-users',
        ],
        'conquistas' => [
            'label' => 'Conquistas',
            'color' => 'warning',
            'icon' => 'fas fa-trophy',
        ],
        'bonus' => [
            'label' => 'Bônus',
            'color' => 'danger',
            'icon' => 'fas fa-gift',
        ],
    ];

    /**
     * Retorna regras agrupadas por categoria.
     */
    public static function grouped()
    {
        return static::orderBy('sort_order')
            ->orderBy('category')
            ->get()
            ->groupBy('category');
    }

    /**
     * Retorna a cor da categoria.
     */
    public function getCategoryColorAttribute()
    {
        return self::CATEGORIES[$this->category]['color'] ?? 'secondary';
    }

    /**
     * Retorna o label da categoria.
     */
    public function getCategoryLabelAttribute()
    {
        return self::CATEGORIES[$this->category]['label'] ?? $this->category ?? 'Outros';
    }
}