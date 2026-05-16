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
 * Sistema UNN - Model AnomalyEvent
 *
 * Representa um registro da tabela anomaly_events (eventos
 * anomalos detectados pelo AnomalyDetectorService: failed_logins,
 * upload_flood, invalid_webhooks). A tabela possui apenas
 * created_at (sem updated_at), por isso $timestamps=false.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 11.1, 11.2, 11.3, 11.4, 11.6
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                            $id
 * @property string                         $type
 * @property string|null                    $source_ip
 * @property int|null                       $source_user_id
 * @property string|null                    $source_identifier
 * @property int                            $threshold_value
 * @property int                            $actual_value
 * @property int                            $window_minutes
 * @property \Illuminate\Support\Carbon|null $notified_at
 * @property bool                           $auto_blocked
 * @property \Illuminate\Support\Carbon     $created_at
 */
class AnomalyEvent extends Model
{
    /**
     * Tabela associada ao model.
     */
    protected $table = 'anomaly_events';

    /**
     * A tabela controla apenas created_at, sem updated_at.
     */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'source_ip',
        'source_user_id',
        'source_identifier',
        'threshold_value',
        'actual_value',
        'window_minutes',
        'notified_at',
        'auto_blocked',
        'created_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'auto_blocked' => 'boolean',
        'notified_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Filtra anomalias por tipo (failed_logins, upload_flood, invalid_webhooks).
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Filtra anomalias geradas a partir do mesmo source (ip,
     * user_id ou identifier). Use null para ignorar um campo.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeBySource(
        Builder $query,
        ?string $sourceIp = null,
        ?int $sourceUserId = null,
        ?string $sourceIdentifier = null
    ): Builder {
        if ($sourceIp !== null) {
            $query->where('source_ip', $sourceIp);
        }

        if ($sourceUserId !== null) {
            $query->where('source_user_id', $sourceUserId);
        }

        if ($sourceIdentifier !== null) {
            $query->where('source_identifier', $sourceIdentifier);
        }

        return $query;
    }

    /**
     * Filtra anomalias criadas a partir de um instante (created_at >= $since).
     *
     * @param  Builder<self>                                              $query
     * @param  \DateTimeInterface|\Illuminate\Support\Carbon|string|null  $since
     * @return Builder<self>
     */
    public function scopeRecent(Builder $query, $since): Builder
    {
        if ($since !== null) {
            $query->where('created_at', '>=', $since);
        }

        return $query;
    }

    /**
     * Filtra apenas anomalias ja notificadas.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeNotified(Builder $query): Builder
    {
        return $query->whereNotNull('notified_at');
    }

    /**
     * Filtra apenas anomalias ainda nao notificadas.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeNotNotified(Builder $query): Builder
    {
        return $query->whereNull('notified_at');
    }
}
