<?php

namespace App\Models\Waf;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entrada de IP/CIDR na IP_Allowlist do WAF.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 9.8, 11.3
 */
class WafIpAllowlistEntry extends Model
{
    protected $table = 'waf_ip_allowlist';

    protected $fillable = [
        'cidr',
        'ip_start',
        'ip_end',
        'reason',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
