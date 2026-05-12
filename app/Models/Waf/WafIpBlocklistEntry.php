<?php

namespace App\Models\Waf;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entrada de IP/CIDR na IP_Blocklist do WAF.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 11.3, 11.4, 11.5, 11.6
 */
class WafIpBlocklistEntry extends Model
{
    public const SOURCE_MANUAL            = 'manual';
    public const SOURCE_AUTO_RISK_SCORE   = 'auto_risk_score';
    public const SOURCE_AUTO_BRUTE_FORCE  = 'auto_brute_force';
    public const SOURCE_AUTO_SSRF         = 'auto_ssrf';

    protected $table = 'waf_ip_blocklist';

    protected $fillable = [
        'cidr',
        'ip_start',
        'ip_end',
        'reason',
        'expires_at',
        'source',
        'auto_generated',
        'created_by',
    ];

    protected $casts = [
        'expires_at'     => 'datetime',
        'auto_generated' => 'boolean',
    ];

    /* ------------------------------------------------------------------ */
    /* Scopes                                                              */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
