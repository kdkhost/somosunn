<?php

return [
    'default' => 'public',

    'cloud' => 'public',

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
            'key' => '',
            'secret' => '',
            'region' => '',
            'bucket' => '',
            'url' => null,
            'endpoint' => null,
            'use_path_style_endpoint' => false,
            'throw' => false,
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
