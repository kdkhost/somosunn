<?php

namespace App\Models\Waf;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Histórico append-only de alterações em waf_rules.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 10.7, 15.7, Property 20
 */
class WafRuleVersion extends Model
{
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_TOGGLED = 'toggled';

    public $timestamps = false;

    protected $table = 'waf_rule_versions';

    protected $fillable = [
        'rule_id',
        'version',
        'snapshot',
        'actor_id',
        'action',
        'created_at',
    ];

    protected $casts = [
        'snapshot'   => 'array',
        'version'    => 'integer',
        'created_at' => 'datetime',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(WafRule::class, 'rule_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
