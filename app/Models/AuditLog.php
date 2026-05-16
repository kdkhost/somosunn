<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 *
 * Sistema UNN - Model AuditLog
 *
 * Representa um registro da tabela audit_logs (eventos de auditoria
 * do sistema). A tabela possui apenas created_at (sem updated_at),
 * por isso $timestamps=false.
 *
 * Campos JSON: old_values, new_values, metadata.
 * Relacionamentos: user() (BelongsTo User) e target() (MorphTo).
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 6.1, 6.2, 6.5
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int                            $id
 * @property int|null                       $user_id
 * @property string                         $ip_address
 * @property string|null                    $user_agent
 * @property string                         $action
 * @property string|null                    $target_type
 * @property int|null                       $target_id
 * @property array<string, mixed>|null      $old_values
 * @property array<string, mixed>|null      $new_values
 * @property string|null                    $request_id
 * @property array<string, mixed>|null      $metadata
 * @property \Illuminate\Support\Carbon     $created_at
 */
class AuditLog extends Model
{
    /**
     * Tabela associada ao model.
     */
    protected $table = 'audit_logs';

    /**
     * A tabela possui apenas created_at (gerenciado manualmente
     * via DEFAULT CURRENT_TIMESTAMP no banco e pelo service),
     * sem updated_at.
     */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'action',
        'target_type',
        'target_id',
        'old_values',
        'new_values',
        'request_id',
        'metadata',
        'created_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'user_id' => 'integer',
        'target_id' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Usuario responsavel pelo evento (pode ser null para
     * eventos do sistema/CLI).
     *
     * @return BelongsTo<\App\Models\User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Entidade alvo do evento via morphTo (target_type, target_id).
     *
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, self>
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Filtra registros por user_id.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtra registros por action.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Filtra registros por entidade alvo (target_type e opcionalmente target_id).
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByTarget(Builder $query, string $targetType, ?int $targetId = null): Builder
    {
        $query->where('target_type', $targetType);

        if ($targetId !== null) {
            $query->where('target_id', $targetId);
        }

        return $query;
    }

    /**
     * Filtra registros entre duas datas (created_at).
     *
     * @param  Builder<self>                                              $query
     * @param  \DateTimeInterface|\Illuminate\Support\Carbon|string|null  $from
     * @param  \DateTimeInterface|\Illuminate\Support\Carbon|string|null  $to
     * @return Builder<self>
     */
    public function scopeBetween(Builder $query, $from = null, $to = null): Builder
    {
        if ($from !== null) {
            $query->where('created_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }
}
