<?php

return [
    'title' => 'SOMOS UNN',
    'title_prefix' => '',
    'title_postfix' => '',

    'menu' => [
        ['text' => 'Dashboard', 'url' => 'dashboard', 'icon' => 'fas fa-tachometer-alt'],
        ['text' => 'Events', 'url' => 'events', 'icon' => 'fas fa-calendar'],
        ['text' => 'Children', 'url' => 'children', 'icon' => 'fas fa-child'],
    ],

    'plugins' => [
        'Datatables' => ['active' => true],
    ],
];