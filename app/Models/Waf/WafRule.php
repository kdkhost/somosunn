<?php

namespace App\Models\Waf;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Regra do WAF da Unn.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 10.2
 */
class WafRule extends Model
{
    public const ACTION_MONITOR   = 'monitor';
    public const ACTION_CHALLENGE = 'challenge';
    public const ACTION_BLOCK     = 'block';

    public const SEVERITY_INFO     = 'info';
    public const SEVERITY_LOW      = 'low';
    public const SEVERITY_MEDIUM   = 'medium';
    public const SEVERITY_HIGH     = 'high';
    public const SEVERITY_CRITICAL = 'critical';

    public const MATCHER_REGEX    = 'regex';
    public const MATCHER_LIST     = 'list';
    public const MATCHER_NUMERIC  = 'numeric';
    public const MATCHER_FUNCTION = 'function';

    protected $table = 'waf_rules';

    protected $fillable = [
        'uid',
        'name',
        'description',
        'attack_pattern',
        'scope',
        'matcher_type',
        'matcher_payload',
        'score',
        'action',
        'severity',
        'is_active',
        'quarantined',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scope'           => 'array',
        'matcher_payload' => 'array',
        'score'           => 'integer',
        'is_active'       => 'boolean',
        'quarantined'     => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $rule) {
            if (empty($rule->uid)) {
                $rule->uid = strtoupper(Str::ulid()->toBase32());
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* Scopes                                                              */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('quarantined', false);
    }

    public function scopeByAttackPattern($query, string $pattern)
    {
        return $query->where('attack_pattern', $pattern);
    }

    public function scopeQuarantined($query)
    {
        return $query->where('quarantined', true);
    }

    /* ------------------------------------------------------------------ */
    /* Relações                                                            */
    /* ------------------------------------------------------------------ */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(WafRuleVersion::class, 'rule_id')->orderBy('version');
    }
}
