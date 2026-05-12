<?php

namespace App\Models\Waf;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Evento registrado pelo WAF durante a inspeção de uma requisição.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 12.1, 12.3
 */
class WafEvent extends Model
{
    public const DECISION_ALLOWED    = 'allowed';
    public const DECISION_MONITORED  = 'monitored';
    public const DECISION_CHALLENGED = 'challenged';
    public const DECISION_BLOCKED    = 'blocked';

    public const UPDATED_AT = null; // só created_at

    protected $table = 'waf_events';

    protected $fillable = [
        'uid',
        'request_id',
        'occurred_at',
        'ip',
        'country',
        'asn',
        'user_id',
        'method',
        'route',
        'path',
        'status',
        'risk_score',
        'decision',
        'rules_fired',
        'samples',
        'user_agent',
        'referrer',
        'is_false_positive',
    ];

    protected $casts = [
        'occurred_at'       => 'datetime',
        'status'            => 'integer',
        'risk_score'        => 'integer',
        'asn'               => 'integer',
        'rules_fired'       => 'array',
        'samples'           => 'array',
        'is_false_positive' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $event) {
            if (empty($event->uid)) {
                $event->uid = strtoupper(Str::ulid()->toBase32());
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* Scopes                                                              */
    /* ------------------------------------------------------------------ */

    public function scopeBlocked($query)
    {
        return $query->where('decision', self::DECISION_BLOCKED);
    }

    public function scopeChallenged($query)
    {
        return $query->where('decision', self::DECISION_CHALLENGED);
    }

    public function scopeMonitored($query)
    {
        return $query->where('decision', self::DECISION_MONITORED);
    }

    public function scopeLast24h($query)
    {
        return $query->where('occurred_at', '>=', now()->subDay());
    }

    public function scopeLast7d($query)
    {
        return $query->where('occurred_at', '>=', now()->subDays(7));
    }

    public function scopeLast30d($query)
    {
        return $query->where('occurred_at', '>=', now()->subDays(30));
    }

    /* ------------------------------------------------------------------ */
    /* Relações                                                            */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function falsePositives(): HasMany
    {
        return $this->hasMany(WafFalsePositive::class, 'event_id');
    }
}
