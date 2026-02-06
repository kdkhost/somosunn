<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use App\Services\IpInfoService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TrackVisitor
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response): void
    {
        try {
            if (app()->environment('testing')) {
                return;
            }

            if (!config('analytics.visitor_logs.enabled', true)) {
                return;
            }

            if (!$request instanceof Request) {
                return;
            }

            if (!$this->shouldTrack($request, $response)) {
                return;
            }

            if (!view()->shared('unnDbAvailable')) {
                return;
            }

            if (!Schema::hasTable('visitor_logs')) {
                return;
            }

            $ip = (string) ($request->header('CF-Connecting-IP') ?: $request->ip() ?: '');
            if ($ip === '') {
                return;
            }

            $ipHash = hash('sha256', $ip . '|' . (string) config('app.key', ''));

            $dedupeSeconds = (int) config('analytics.visitor_logs.dedupe_seconds', 60);
            if ($dedupeSeconds < 0) {
                $dedupeSeconds = 0;
            }

            try {
                $last = $request->session()->get('visitor_log.last', null);
                if (
                    is_array($last)
                    && ($last['ip_hash'] ?? null) === $ipHash
                    && ($last['path'] ?? null) === $request->path()
                    && isset($last['ts'])
                    && (time() - (int) $last['ts']) < $dedupeSeconds
                ) {
                    return;
                }

                $request->session()->put('visitor_log.last', [
                    'ip_hash' => $ipHash,
                    'path' => $request->path(),
                    'ts' => time(),
                ]);
            } catch (\Throwable $e) {
                // session pode não estar disponível em alguns contextos
            }

            $country = strtoupper(trim((string) ($request->header('CF-IPCountry') ?: '')));
            $region = null;
            $city = null;
            $latitude = null;
            $longitude = null;
            $timezone = null;

            $location = app(IpInfoService::class)->resolve($ip);
            if (is_array($location) && $location) {
                $country = $country !== '' ? $country : (string) ($location['country'] ?? '');
                $region = $location['region'] ?? null;
                $city = $location['city'] ?? null;
                $latitude = $location['latitude'] ?? null;
                $longitude = $location['longitude'] ?? null;
                $timezone = $location['timezone'] ?? null;
            }

            $storeIp = (bool) config('analytics.visitor_logs.store_ip', false);

            VisitorLog::create([
                'user_id' => auth()->id(),
                'ip_hash' => $ipHash,
                'ip' => $storeIp ? $ip : null,
                'country' => $country !== '' && $country !== 'XX' ? $country : null,
                'region' => $region,
                'city' => $city,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timezone' => $timezone,
                'method' => $request->method(),
                'path' => '/' . ltrim($request->path(), '/'),
                'url' => $request->fullUrl(),
                'referrer' => (string) $request->headers->get('referer', ''),
                'user_agent' => (string) $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::debug('TrackVisitor falhou: ' . $e->getMessage());
        }
    }

    private function shouldTrack(Request $request, $response): bool
    {
        if (!$request->isMethod('GET')) {
            return false;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return false;
        }

        if ($request->is('admin*') || $request->is('api*')) {
            return false;
        }

        if ($request->is('storage*') || $request->is('img*') || $request->is('uploads*')) {
            return false;
        }

        $path = '/' . ltrim($request->path(), '/');
        if (in_array($path, ['/favicon.ico', '/service-worker.js', '/manifest.webmanifest'], true)) {
            return false;
        }

        try {
            $status = method_exists($response, 'getStatusCode') ? (int) $response->getStatusCode() : 200;
            if ($status < 200 || $status >= 300) {
                return false;
            }

            $contentType = (string) ($response->headers->get('Content-Type') ?? '');
            if ($contentType !== '' && !str_contains(strtolower($contentType), 'text/html')) {
                return false;
            }
        } catch (\Throwable $e) {
            // se não conseguir inspecionar o response, não bloqueia
        }

        return true;
    }
}
