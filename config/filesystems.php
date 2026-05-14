<?php

/**
 * Helper para ler configuracao do banco com fallback seguro para env().
 * Durante migrations, artisan commands antes do DB, ou quando a tabela nao existe,
 * retorna null para que o fallback env() seja usado.
 */
$dbSetting = function (string $key, $default = null) {
    try {
        if (!class_exists(\App\Models\Setting::class)) {
            return null;
        }
        $value = \App\Models\Setting::get($key, null);
        return $value !== null && $value !== '' ? $value : null;
    } catch (\Throwable $e) {
        return null;
    }
};

$storageDriver = $dbSetting('storage_driver', 'public') ?? env('FILESYSTEM_DISK', 'public');

return [
    'default' => $storageDriver,

    'cloud' => 's3',

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'local_public' => [
            'driver' => 'local',
            'root' => is_dir(public_path('storage'))
                ? public_path('storage')
                : storage_path('app/public'),
            'url' => '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => is_dir(public_path('storage'))
                ? public_path('storage')
                : storage_path('app/public'),
            'url' => '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'uploads' => [
            'driver' => 'local',
            'root' => is_dir(public_path('storage'))
                ? public_path('storage')
                : storage_path('app/public'),
            'url' => '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => $dbSetting('storage_access_key') ?? env('AWS_ACCESS_KEY_ID'),
            'secret' => $dbSetting('storage_secret_key') ?? env('AWS_SECRET_ACCESS_KEY'),
            'region' => $dbSetting('storage_region') ?? env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => $dbSetting('storage_bucket') ?? env('AWS_BUCKET'),
            'url' => $dbSetting('storage_url') ?? env('AWS_URL'),
            'endpoint' => $dbSetting('storage_endpoint') ?? env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => (bool) ($dbSetting('storage_path_style') ?? env('AWS_USE_PATH_STYLE_ENDPOINT', true)),
            'visibility' => 'public',
            'throw' => false,
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
