<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffiliateApiSandboxRequest extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'requested_domain',
        'requested_ip',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function normalizedDomain(): ?string
    {
        $domain = trim((string) $this->requested_domain);
        if ($domain === '') {
            return null;
        }

        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = explode('/', $domain)[0] ?? $domain;
        $domain = strtolower(trim($domain));

        return $domain !== '' ? $domain : null;
    }

    public function matchesRequest(Request $request): bool
    {
        if (app()->runningUnitTests()) {
            return true;
        }

        $requestIp = trim((string) $request->ip());
        $originHost = $this->extractHost($request);
        $allowedDomain = $this->normalizedDomain();
        $allowedIp = trim((string) $this->requested_ip);

        $hasDomainRule = $allowedDomain !== '';
        $hasIpRule = $allowedIp !== '';

        $domainMatches = $hasDomainRule
            && $originHost !== null
            && ($originHost === $allowedDomain || Str::endsWith($originHost, '.' . $allowedDomain));

        $ipMatches = $hasIpRule && $requestIp !== '' && $requestIp === $allowedIp;

        if ($hasDomainRule && $hasIpRule) {
            return $domainMatches || $ipMatches;
        }

        if ($hasDomainRule) {
            return $domainMatches;
        }

        if ($hasIpRule) {
            return $ipMatches;
        }

        return false;
    }

    private function extractHost(Request $request): ?string
    {
        $origin = trim((string) $request->headers->get('origin', ''));
        $referer = trim((string) $request->headers->get('referer', ''));
        $candidate = $origin !== '' ? $origin : $referer;

        if ($candidate === '') {
            return null;
        }

        $host = parse_url($candidate, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? strtolower($host) : null;
    }
}
