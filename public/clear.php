<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('view:clear');
$kernel->call('optimize:clear');
echo "Cache cleared success";
unlink(__FILE__);
