<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$id = (int) App\Models\Setting::get('platform_marketing_user_id', 0);
if (!$id) {
    echo "Nenhum marketing manager definido\n";
    exit;
}

$u = App\Models\User::find($id);
if (!$u) {
    echo "User #{$id} nao encontrado\n";
    exit;
}

echo "=== Marketing Manager ===\n";
echo "ID: {$u->id}\n";
echo "Nome: {$u->name}\n";
echo "Email: {$u->email}\n";
echo "Role: {$u->role}\n";
echo "Level: {$u->level}\n";
echo "Plan: " . ($u->plan?->name ?? '(sem plano)') . "\n";
echo "Plan ID: " . ($u->plan_id ?? 'null') . "\n";
echo "canSellOnMarketplace: " . ($u->canSellOnMarketplace() ? 'SIM' : 'NAO') . "\n";
echo "canAccessInstructorArea: " . ($u->canAccessInstructorArea() ? 'SIM' : 'NAO') . "\n";
echo "isAdmin: " . ($u->isAdmin() ? 'SIM' : 'NAO') . "\n";

// Check extra_features
$extra = $u->extra_features ?? [];
echo "extra_features: " . json_encode($extra) . "\n";
