<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Historico diario do score de reputacao de cada membro.
 * Usado para exibir grafico de evolucao nos ultimos 6 meses.
 */
class MemberReputationHistory extends Model
{
    use HasFactory;

    /**
     * Desabilita updated_at pois a tabela so tem created_at.
     */
    const UPDATED_AT = null;

    protected $table = 'member_reputation_history';

    protected $fillable = [
        'user_id',
        'overall_score',
        'recorded_at',
    ];

    protected $casts = [
        'overall_score' => 'integer',
        'recorded_at' => 'date',
    ];

    /**
     * Relacao com o usuario dono deste historico.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
