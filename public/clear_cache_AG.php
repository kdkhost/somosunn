<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->call('view:clear');
echo "View cache cleared: " . $status;
$status = $kernel->call('cache:clear');
echo "<br>General cache cleared: " . $status;
unlink(__FILE__);
