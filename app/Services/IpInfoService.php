<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpInfoService
{
    public function resolve(string $ip): array
    {
        $token = (string) config('services.ipinfo.token', '');
        if ($token === '') {
            return [];
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return [];
        }

        $cacheKey = 'ipinfo:' . sha1($ip);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($ip, $token) {
            try {
                $resp = Http::timeout(5)->get("https://ipinfo.io/{$ip}/json", [
                    'token' => $token,
                ]);

                if (!$resp->ok()) {
                    return [];
                }

                $payload = $resp->json();
                if (!is_array($payload)) {
                    return [];
                }

                $loc = (string) ($payload['loc'] ?? '');
                $lat = null;
                $lon = null;
                if ($loc !== '' && str_contains($loc, ',')) {
                    [$latRaw, $lonRaw] = array_map('trim', explode(',', $loc, 2));
                    if (is_numeric($latRaw) && is_numeric($lonRaw)) {
                        $lat = (float) $latRaw;
                        $lon = (float) $lonRaw;
                    }
                }

                return [
                    'country' => $payload['country'] ?? null,
                    'region' => $payload['region'] ?? null,
                    'city' => $payload['city'] ?? null,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'timezone' => $payload['timezone'] ?? null,
                ];
            } catch (\Throwable $e) {
                Log::warning('IpInfo resolve falhou: ' . $e->getMessage());
                return [];
            }
        });
    }
}

