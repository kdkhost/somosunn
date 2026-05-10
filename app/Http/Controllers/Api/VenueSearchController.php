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

        $provider = $request->input('provider', Setting::get('venue_search_provider', 'auto'));
        $tomtomKey    = trim((string) Setting::get('tomtom_api_key', ''));
        $googleKey    = trim((string) Setting::get('google_places_api_key', ''));
        $locationIqKey = trim((string) Setting::get('locationiq_api_key', ''));
        $limit        = max(5, min(40, (int) Setting::get('venue_search_limit', 20)));
        $radiusKm     = max(10, min(500, (int) Setting::get('venue_search_radius_km', 150)));

        // Coordenadas do usuario para bias
        $userLat = $request->input('lat');
        $userLon = $request->input('lon');

        // 1) TomTom com bias (melhor cobertura de SMBs no Brasil)
        if ($tomtomKey && in_array($provider, ['auto', 'tomtom'], true)) {
            $results = $this->searchTomTom($query, $tomtomKey, $limit, $userLat, $userLon, $radiusKm);
            if (!empty($results)) {
                return response()->json(['results' => $results, 'provider' => 'tomtom']);
            }
            // Relaxar bias: tenta sem lat/lon para achar o estabelecimento em outro estado
            if ($userLat && $userLon) {
                $results = $this->searchTomTom($query, $tomtomKey, $limit);
                if (!empty($results)) {
                    return response()->json(['results' => $results, 'provider' => 'tomtom', 'out_of_radius' => true]);
                }
            }
        }

        // 2) Google Places com bias
        if ($googleKey && in_array($provider, ['auto', 'google'], true)) {
            $results = $this->searchGoogle($query, $googleKey, $limit, $userLat, $userLon, $radiusKm);
            if (!empty($results)) {
                return response()->json(['results' => $results, 'provider' => 'google']);
            }
            // Relaxar bias
            if ($userLat && $userLon) {
                $results = $this->searchGoogle($query, $googleKey, $limit);
                if (!empty($results)) {
                    return response()->json(['results' => $results, 'provider' => 'google', 'out_of_radius' => true]);
                }
            }
        }

        // 3) LocationIQ (fallback)
        if ($locationIqKey && in_array($provider, ['auto', 'locationiq'], true)) {
            $results = $this->searchLocationIq($query, $locationIqKey, $limit);
            if (!empty($results)) {
                return response()->json(['results' => $results, 'provider' => 'locationiq']);
            }
        }

        return response()->json(['results' => [], 'provider' => 'none']);
    }

    /**
     * TomTom Search API — excelente cobertura para SMBs brasileiros.
     * Docs: https://developer.tomtom.com/search-api/documentation
     */
    private function searchTomTom(string $query, string $key, int $limit, $lat = null, $lon = null, int $radiusKm = 150): array
    {
        try {
            $params = [
                'key'        => $key,
                'countrySet' => 'BR',
                'limit'      => $limit,
                'language'   => 'pt-BR',
                'typeahead'  => 'true',
            ];

            if ($lat && $lon && is_numeric($lat) && is_numeric($lon)) {
                $params['lat']    = $lat;
                $params['lon']    = $lon;
                // TomTom: radius em metros. Limite pratico ~200km
                $params['radius'] = min(200000, max(1000, $radiusKm * 1000));
            }

            $url = 'https://api.tomtom.com/search/2/search/' . rawurlencode($query) . '.json';
            $response = Http::timeout(8)->get($url, $params);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $results = [];

            foreach (($data['results'] ?? []) as $item) {
                if (count($results) >= $limit) break;

                $poi      = $item['poi']      ?? [];
                $address  = $item['address']  ?? [];
                $position = $item['position'] ?? [];

                $name = $poi['name'] ?? ($address['freeformAddress'] ?? '');
                if ($name === '') continue;

                $results[] = [
                    'name'    => $name,
                    'address' => $address['freeformAddress'] ?? '',
                    'lat'     => isset($position['lat']) ? (float) $position['lat'] : null,
                    'lng'     => isset($position['lon']) ? (float) $position['lon'] : null,
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            \Log::warning('TomTom search failed: ' . $e->getMessage());
            return [];
        }
    }

    private function searchGoogle(string $query, string $key, int $limit, $lat = null, $lon = null, int $radiusKm = 150): array
    {
        try {
            $params = [
                'query'    => $query,
                'key'      => $key,
                'language' => 'pt-BR',
                'region'   => 'br',
            ];

            if ($lat && $lon) {
                $params['location'] = $lat . ',' . $lon;
                $params['radius']   = min(50000, max(1000, $radiusKm * 1000)); // Google cap 50km
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
                    'name'    => $place['name'] ?? '',
                    'address' => $place['formatted_address'] ?? '',
                    'lat'     => $place['geometry']['location']['lat'] ?? null,
                    'lng'     => $place['geometry']['location']['lng'] ?? null,
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
                'key'            => $key,
                'q'              => $query,
                'countrycodes'   => 'br',
                'format'         => 'json',
                'limit'          => $limit,
                'addressdetails' => 1,
                'dedupe'         => 1,
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
                $addr          = $item['address'] ?? [];
                $city          = $addr['city']  ?? $addr['town']  ?? $addr['village'] ?? '';
                $state         = $addr['state'] ?? '';
                $road          = $addr['road']  ?? '';
                $number        = $addr['house_number'] ?? '';
                $neighbourhood = $addr['suburb'] ?? $addr['neighbourhood'] ?? '';
                $fullAddress   = implode(', ', array_filter([$road, $number, $neighbourhood, $city, $state]));
                $shortName     = $addr['amenity'] ?? $addr['tourism'] ?? $addr['leisure'] ?? $addr['shop'] ?? '';

                $results[] = [
                    'name'    => $shortName ?: explode(',', $item['display_name'] ?? '')[0],
                    'address' => $fullAddress ?: ($item['display_name'] ?? ''),
                    'lat'     => (float) ($item['lat'] ?? 0),
                    'lng'     => (float) ($item['lon'] ?? 0),
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            \Log::warning('LocationIQ search failed: ' . $e->getMessage());
            return [];
        }
    }
}
