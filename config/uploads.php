<?php

return [
    'video_max_mb' => env('VIDEO_MAX_MB', 1024),
    'document_max_mb' => env('DOCUMENT_MAX_MB', 50),
    'allowed_video_formats' => explode(',', env('ALLOWED_VIDEO_FORMATS', 'mp4,webm,mkv')),
    'allowed_document_formats' => explode(',', env('ALLOWED_DOCUMENT_FORMATS', 'pdf,docx,pptx')),
];