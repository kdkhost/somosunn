<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VenueSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        if (strlen($query) < 3) {
            return response()->json(['results' => []]);
        }

        $provider = $request->input('provider', 'auto');
        $googleKey = trim((string) Setting::get('google_places_api_key', ''));
        $locationIqKey = trim((string) Setting::get('locationiq_api_key', ''));
        $limit = max(5, min(40, (int) Setting::get('venue_search_limit', 20)));

        // Coordenadas do usuario para bias
        $userLat = $request->input('lat');
        $userLon = $request->input('lon');

        // Tentar Google Places primeiro
        if ($googleKey && in_array($provider, ['auto', 'google'], true)) {
            $results = $this->searchGoogle($query, $googleKey, $limit, $userLat, $userLon);
            if (!empty($results)) {
                return response()->json(['results' => $results, 'provider' => 'google']);
            }
        }

        // Fallback LocationIQ
        if ($locationIqKey && in_array($provider, ['auto', 'locationiq'], true)) {
            $results = $this->searchLocationIq($query, $locationIqKey, $limit);
            return response()->json(['results' => $results, 'provider' => 'locationiq']);
        }

        return response()->json(['results' => [], 'provider' => 'none']);
    }

    private function searchGoogle(string $query, string $key, int $limit, $lat = null, $lon = null): array
    {
        try {
            $params = [
                'query' => $query,
                'key' => $key,
                'language' => 'pt-BR',
                'region' => 'br',
            ];

            if ($lat && $lon) {
                $params['location'] = $lat . ',' . $lon;
                $params['radius'] = 150000; // 150km em metros
            }

            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/place/textsearch/json', $params);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $results = [];

            foreach (($data['results'] ?? []) as $place) {
                if (count($results) >= $limit) break;

                $results[] = [
                    'name' => $place['name'] ?? '',
                    'address' => $place['formatted_address'] ?? '',
                    'lat' => $place['geometry']['location']['lat'] ?? null,
                    'lng' => $place['geometry']['location']['lng'] ?? null,
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            \Log::warning('Google Places search failed: ' . $e->getMessage());
            return [];
        }
    }

    private function searchLocationIq(string $query, string $key, int $limit): array
    {
        try {
            $response = Http::timeout(8)->get('https://us1.locationiq.com/v1/search', [
                'key' => $key,
                'q' => $query,
                'countrycodes' => 'br',
                'format' => 'json',
                'limit' => $limit,
                'addressdetails' => 1,
                'dedupe' => 1,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            if (isset($data['error'])) {
                return [];
            }

            $results = [];
            foreach ($data as $item) {
                $addr = $item['address'] ?? [];
                $city = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? '';
                $state = $addr['state'] ?? '';
                $road = $addr['road'] ?? '';
                $number = $addr['house_number'] ?? '';
                $neighbourhood = $addr['suburb'] ?? $addr['neighbourhood'] ?? '';
                $fullAddress = implode(', ', array_filter([$road, $number, $neighbourhood, $city, $state]));
                $shortName = $addr['amenity'] ?? $addr['tourism'] ?? $addr['leisure'] ?? $addr['shop'] ?? '';

                $results[] = [
                    'name' => $shortName ?: explode(',', $item['display_name'] ?? '')[0],
                    'address' => $fullAddress ?: ($item['display_name'] ?? ''),
                    'lat' => (float) ($item['lat'] ?? 0),
                    'lng' => (float) ($item['lon'] ?? 0),
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            \Log::warning('LocationIQ search failed: ' . $e->getMessage());
            return [];
        }
    }
}
