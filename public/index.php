<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

// EMERGENCY FIX: FORCE CACHE CLEAR BEFORE BOOT
// Isso garante que o cache limpe ANTES de dar erro
try {
    $kernel->call('view:clear');
    $kernel->call('route:clear');
} catch (\Throwable $e) {
    // ignore
}

$response = $kernel->handle(
    $request = Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
