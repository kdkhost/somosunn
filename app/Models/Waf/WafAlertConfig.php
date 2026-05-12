<?php

namespace App\Models\Waf;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Configuração de canal de alerta do WAF.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 16.1, 16.2, 16.3, 16.4, 16.5
 */
class WafAlertConfig extends Model
{
    public const CHANNEL_EMAIL   = 'email';
    public const CHANNEL_WEBHOOK = 'webhook';

    public const TRIGGER_BLOCK_SPIKE       = 'block_spike';
    public const TRIGGER_AUTO_BLOCK        = 'auto_block';
    public const TRIGGER_CRITICAL_FINDING  = 'critical_finding';
    public const TRIGGER_IP_REPUTATION     = 'ip_reputation';

    protected $table = 'waf_alerts_config';

    protected $fillable = [
        'channel',
        'target',
        'trigger',
        'threshold',
        'silence_until',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'threshold'     => 'array',
        'silence_until' => 'datetime',
        'is_active'     => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('silence_until')->orWhere('silence_until', '<=', now());
            });
    }

    public function scopeByTrigger($query, string $trigger)
    {
        return $query->where('trigger', $trigger);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
