<?php

namespace App\Models\Waf;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de WAF_Event marcado como falso positivo pelo superadmin.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 14.6
 */
class WafFalsePositive extends Model
{
    public const UPDATED_AT = null; // só created_at

    protected $table = 'waf_false_positives';

    protected $fillable = [
        'event_id',
        'rule_id',
        'reviewed_by',
        'note',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(WafEvent::class, 'event_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(WafRule::class, 'rule_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
