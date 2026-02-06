<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'context',
        'question',
        'answer',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public const CONTEXT_GENERAL = 'general';
    public const CONTEXT_PREMIUM = 'premium';
    public const CONTEXT_CONTACT = 'contact';

    public static function contextOptions(): array
    {
        return [
            self::CONTEXT_GENERAL => 'Geral',
            self::CONTEXT_PREMIUM => 'Premium',
            self::CONTEXT_CONTACT => 'Contato',
        ];
    }
}

