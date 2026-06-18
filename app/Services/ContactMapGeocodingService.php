<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ContactMapGeocodingService
{
    public function resolve(?string $address): ?array
    {
        $address = trim((string) $address);
        if ($address === '') {
            return null;
        }

        $cacheKey = 'contact-map:geocode:' . sha1($address);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($address) {
            return $this->resolveWithGoogle($address)
                ?? $this->resolveWithLocationIq($address)
                ?? $this->resolveWithNominatim($address);
        });
    }

    private function resolveWithGoogle(string $address): ?array
    {
        $googleKey = trim((string) Setting::get('google_places_api_key', ''));
        if ($googleKey === '') {
            return null;
        }

        try {
            $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
                'query' => $address,
                'key' => $googleKey,
                'language' => 'pt-BR',
                'region' => 'br',
            ]);

            if (! $response->successful()) {
                return null;
            }

            $result = $response->json('results.0');
            if (! is_array($result)) {
                return null;
            }

            $lat = $result['geometry']['location']['lat'] ?? null;
            $lng = $result['geometry']['location']['lng'] ?? null;

            return $this->normalizeCoordinates($lat, $lng);
        } catch (\Throwable $e) {
            \Log::warning('Contact map Google geocoding failed: ' . $e->getMessage());

            return null;
        }
    }

    private function resolveWithLocationIq(string $address): ?array
    {
        $locationIqKey = trim((string) Setting::get('locationiq_api_key', ''));
        if ($locationIqKey === '') {
            return null;
        }

        try {
            $response = Http::timeout(10)->get('https://us1.locationiq.com/v1/search', [
                'key' => $locationIqKey,
                'q' => $address,
                'countrycodes' => 'br',
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 1,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $result = $response->json('0');
            if (! is_array($result)) {
                return null;
            }

            return $this->normalizeCoordinates($result['lat'] ?? null, $result['lon'] ?? null);
        } catch (\Throwable $e) {
            \Log::warning('Contact map LocationIQ geocoding failed: ' . $e->getMessage());

            return null;
        }
    }

    private function resolveWithNominatim(string $address): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => $this->buildUserAgent(),
                    'Accept-Language' => 'pt-BR',
                    'Referer' => url('/contato'),
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'countrycodes' => 'br',
                    'q' => $address,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $result = $response->json('0');
            if (! is_array($result)) {
                return null;
            }

            return $this->normalizeCoordinates($result['lat'] ?? null, $result['lon'] ?? null);
        } catch (\Throwable $e) {
            \Log::warning('Contact map Nominatim geocoding failed: ' . $e->getMessage());

            return null;
        }
    }

    private function normalizeCoordinates($lat, $lng): ?array
    {
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        return [
            'lat' => (float) $lat,
            'lng' => (float) $lng,
        ];
    }

    private function buildUserAgent(): string
    {
        $appName = trim((string) config('app.name', 'SomosUNN'));
        $appUrl = trim((string) config('app.url', 'https://somosunn.com.br'));
        $email = trim((string) Setting::get('company_email', 'contato@somosunn.com.br'));

        return $appName . '/contact-map (' . $appUrl . '; ' . $email . ')';
    }
}
