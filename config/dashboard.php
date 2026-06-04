<?php

return [
    'cache_store' => env('DASHBOARD_CACHE_STORE', env('CACHE_STORE')),
    'cache_ttl_seconds' => (int) env('DASHBOARD_CACHE_TTL_SECONDS', 30),
    'refresh_interval_ms' => (int) env('DASHBOARD_REFRESH_INTERVAL_MS', 30000),
    'warm_chunk' => (int) env('DASHBOARD_CACHE_WARM_CHUNK', 100),
];
