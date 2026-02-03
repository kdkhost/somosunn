<?php
// Script de limpeza forçada (Brute Force)
// Coloque na pasta public/ e acesse via navegador

echo "<h1>Iniciando Limpeza Forçada...</h1>";

$baseDir = __DIR__ . '/../';

// 1. Limpar View Cache
$viewPath = $baseDir . 'storage/framework/views';
echo "<h3>Limpando Views em: $viewPath</h3>";
if (is_dir($viewPath)) {
    $files = glob("$viewPath/*.php");
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            echo "Deletado: " . basename($file) . "<br>";
        }
    }
} else {
    echo "Diretório de views não encontrado!<br>";
}

// 2. Limpar Route Cache
$bootstrapCache = $baseDir . 'bootstrap/cache';
echo "<h3>Limpando Cache de Rotas em: $bootstrapCache</h3>";
$routeFiles = [
    'routes-v7.php',
    'routes.php',
    'config.php',
    'packages.php',
    'services.php'
];

foreach ($routeFiles as $rf) {
    $file = "$bootstrapCache/$rf";
    if (file_exists($file)) {
        unlink($file);
        echo "<strong>CACHE DELETADO: $rf</strong><br>";
    }
}

echo "<h2>Limpeza Concluída! Tente acessar o admin agora.</h2>";
