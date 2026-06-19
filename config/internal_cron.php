<?php

return [
    'enabled' => (bool) env('INTERNAL_CRON_ENABLED', false),
    'min_interval_seconds' => (int) env('INTERNAL_CRON_MIN_INTERVAL_SECONDS', 60),
    'run_queue_worker' => (bool) env('INTERNAL_CRON_RUN_QUEUE_WORKER', true),
];
