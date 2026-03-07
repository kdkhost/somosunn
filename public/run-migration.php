<?php
/**
 * Script de migração de emergência - Apagar após o uso
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<h2>Executando Limpeza de Cache de Rotas...</h2>";
$kernel->call('route:clear');
echo nl2br($kernel->output());

echo "<h2>Executando Migrations...</h2>";
try {
    $kernel->call('migrate', ['--force' => true]);
    echo nl2br($kernel->output());
    echo '<h3>Sucesso! <a href="/somos-unicas">Clique aqui para ir para Somos Únicas</a></h3>';
} catch (\Exception $e) {
    echo "<h3>Erro:</h3>" . $e->getMessage();
}
