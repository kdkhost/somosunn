<?php

return [
    // Disco usado para uploads (padrao: public/local). Pode ser sobrescrito por configuracoes no banco.
    'disk' => env('UPLOADS_DISK', 'public'),
    'video_max_mb' => env('VIDEO_MAX_MB', 1024),
    'document_max_mb' => env('DOCUMENT_MAX_MB', 50),
    'allowed_video_formats' => array_values(array_filter(array_map('trim', explode(',', strtolower(env('ALLOWED_VIDEO_FORMATS', 'mp4,webm,mkv')))))),
    'allowed_document_formats' => array_values(array_filter(array_map('trim', explode(',', strtolower(env('ALLOWED_DOCUMENT_FORMATS', 'pdf,docx,pptx')))))),
    'video_hls_enabled' => env('VIDEO_HLS_ENABLED', true),
    'video_hls_async' => env('VIDEO_HLS_ASYNC', true),
    'video_hls_scheduler_limit' => (int) env('VIDEO_HLS_SCHEDULER_LIMIT', 2),
    'video_ffmpeg_binary' => env('VIDEO_FFMPEG_BINARY', 'ffmpeg'),
    'video_php_binary' => env('VIDEO_PHP_BINARY', PHP_BINARY ?: 'php'),
    'video_hls_segment_seconds' => (int) env('VIDEO_HLS_SEGMENT_SECONDS', 6),
    'video_hls_crf' => (int) env('VIDEO_HLS_CRF', 25),
    'video_hls_preset' => env('VIDEO_HLS_PRESET', 'veryfast'),
];
