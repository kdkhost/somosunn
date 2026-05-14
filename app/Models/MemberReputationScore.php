<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Score de reputacao persistido para cada membro.
 * Atualizado diariamente via command e imediatamente via eventos.
 */
class MemberReputationScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'overall_score',
        'delivery_rate',
        'relationship_score',
        'interaction_score',
        'engagement_score',
        'has_seller_store',
        'last_login_at',
        'decay_started_at',
        'calculated_at',
    ];

    protected $casts = [
        'overall_score' => 'integer',
        'delivery_rate' => 'float',
        'relationship_score' => 'float',
        'interaction_score' => 'float',
        'engagement_score' => 'float',
        'has_seller_store' => 'boolean',
        'last_login_at' => 'datetime',
        'decay_started_at' => 'datetime',
        'calculated_at' => 'datetime',
    ];

    /**
     * Relacao com o usuario dono deste score.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
