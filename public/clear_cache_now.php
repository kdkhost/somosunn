<?php
// Script temporário para limpar o cache de views no servidor
define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<h1>Limpando caches...</h1>";

try {
    $kernel->call('view:clear');
    echo "<p>View cache: OK</p>";

    $kernel->call('cache:clear');
    echo "<p>General cache: OK</p>";

    $kernel->call('config:clear');
    echo "<p>Config cache: OK</p>";

    $kernel->call('route:clear');
    echo "<p>Route cache: OK</p>";

} catch (\Exception $e) {
    echo "<p>Erro: " . $e->getMessage() . "</p>";
}

echo "<p>Concluído.</p>";
