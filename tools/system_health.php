<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SYSTEM HEALTH CHECK ===\n\n";

// Disk
echo "--- DISCO ---\n";
$diskTotal = disk_total_space('/home/somosunn/');
$diskFree = disk_free_space('/home/somosunn/');
$diskUsed = $diskTotal - $diskFree;
echo "Total: " . round($diskTotal / 1024 / 1024 / 1024, 2) . " GB\n";
echo "Usado: " . round($diskUsed / 1024 / 1024 / 1024, 2) . " GB\n";
echo "Livre: " . round($diskFree / 1024 / 1024 / 1024, 2) . " GB\n";
echo "Uso: " . round(($diskUsed / $diskTotal) * 100, 1) . "%\n\n";

// DB
echo "--- BANCO DE DADOS ---\n";
$dbSize = DB::select("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.tables WHERE table_schema = ?", [DB::getDatabaseName()]);
echo "Tamanho: " . round($dbSize[0]->size, 2) . " MB\n";
$tableCount = DB::select("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = ?", [DB::getDatabaseName()]);
echo "Tabelas: " . $tableCount[0]->cnt . "\n\n";

// Users
echo "--- USUARIOS ---\n";
$totalUsers = \App\Models\User::count();
$activeUsers = \App\Models\User::whereNotNull('plan_id')->count();
$recentUsers = \App\Models\User::where('created_at', '>', now()->subDays(30))->count();
echo "Total: {$totalUsers}\n";
echo "Com plano ativo: {$activeUsers}\n";
echo "Novos (30 dias): {$recentUsers}\n\n";

// Orders
echo "--- PEDIDOS ---\n";
$totalOrders = \App\Models\Order::count();
$paidOrders = \App\Models\Order::where('status', 'paid')->count();
$pendingOrders = \App\Models\Order::where('status', 'pending')->count();
echo "Total: {$totalOrders}\n";
echo "Pagos: {$paidOrders}\n";
echo "Pendentes: {$pendingOrders}\n\n";

// Performance
echo "--- PERFORMANCE ---\n";
$phpVersion = phpversion();
$memoryLimit = ini_get('memory_limit');
$maxExecTime = ini_get('max_execution_time');
echo "PHP: {$phpVersion}\n";
echo "Memory Limit: {$memoryLimit}\n";
echo "Max Execution: {$maxExecTime}s\n";

// Estimativa de capacidade
$memLimitMB = (int) str_replace('M', '', $memoryLimit);
$estimatedConcurrent = max(10, (int) ($memLimitMB / 32)); // ~32MB por request
echo "\n--- CAPACIDADE ESTIMADA ---\n";
echo "Usuarios simultaneos (estimado): ~{$estimatedConcurrent}\n";
echo "Tipo: Hospedagem compartilhada\n";
echo "Recomendação: até " . min(100, $estimatedConcurrent * 2) . " usuarios online simultaneamente\n";
