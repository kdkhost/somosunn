<?php

namespace App\Http\Middleware;

use App\Services\AffiliateTrackingService;
use Closure;
use Illuminate\Http\Request;

class TrackReferralLink
{
    public function __construct(
        private readonly AffiliateTrackingService $tracking
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            if ($request->hasSession()) {
                $this->tracking->captureIncomingReferral($request);
            }
        } catch (\Throwable) {
        }

        return $next($request);
    }

    public function terminate($request, $response): void
    {
        try {
            if ($request instanceof Request && $request->hasSession()) {
                $this->tracking->trackPageView($request, $response);
            }
        } catch (\Throwable) {
        }
    }
}
