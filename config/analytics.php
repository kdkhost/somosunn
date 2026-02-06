<?php

return [
    'visitor_logs' => [
        'enabled' => (bool) env('VISITOR_LOGS_ENABLED', true),
        'store_ip' => (bool) env('VISITOR_LOGS_STORE_IP', false),
        'dedupe_seconds' => (int) env('VISITOR_LOGS_DEDUPE_SECONDS', 60),
    ],
];

