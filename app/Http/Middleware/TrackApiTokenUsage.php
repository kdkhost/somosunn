<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackApiTokenUsage
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $token = $request->user()?->currentAccessToken();

        if (!$token || !$token->exists || !$this->hasLastUsedIpColumn()) {
            return $response;
        }

        $token->forceFill([
            'last_used_ip' => $request->ip(),
        ])->save();

        return $response;
    }

    private function hasLastUsedIpColumn(): bool
    {
        try {
            return Schema::hasTable('personal_access_tokens')
                && Schema::hasColumn('personal_access_tokens', 'last_used_ip');
        } catch (\Throwable) {
            return false;
        }
    }
}
