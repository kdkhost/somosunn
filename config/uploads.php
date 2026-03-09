<?php

return [
    'disk' => 'public',
    'video_max_mb' => 1024,
    'document_max_mb' => 50,
    'allowed_video_formats' => ['mp4', 'webm', 'mkv'],
    'allowed_document_formats' => ['pdf', 'docx', 'pptx'],
    'video_hls_enabled' => true,
    'video_hls_async' => true,
    'video_hls_scheduler_limit' => 2,
    'video_ffmpeg_binary' => 'ffmpeg',
    'video_php_binary' => PHP_BINARY ?: 'php',
    'video_hls_segment_seconds' => 6,
    'video_hls_crf' => 25,
    'video_hls_preset' => 'veryfast',
];
