<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

if (!class_exists('Mockery') && is_dir(__DIR__ . '/../vendor/mockery/mockery/library')) {
    $loader->addPsr4('Mockery\\', __DIR__ . '/../vendor/mockery/mockery/library/Mockery');

    require_once __DIR__ . '/../vendor/mockery/mockery/library/helpers.php';
    require_once __DIR__ . '/../vendor/mockery/mockery/library/Mockery.php';
}

return $loader;
