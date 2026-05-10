<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

use App\Models\User;
use App\Models\Setting;

echo "=== USUARIOS ADMIN/SUPERADMIN ===\n";
$admins = User::query()
    ->whereIn('role', ['admin', 'superadmin'])
    ->orWhere('level', 'admin')
    ->orderBy('id')
    ->get(['id', 'name', 'email', 'role', 'level']);

foreach ($admins as $u) {
    echo "#{$u->id} | {$u->name} | {$u->email} | role={$u->role} | level={$u->level}\n";
}

echo "\n=== BUSCA POR MARCELO BRAD ===\n";
$marcelo = User::where('email', 'marcelobradrj@gmail.com')->first();
if ($marcelo) {
    echo "id={$marcelo->id}, name={$marcelo->name}, email={$marcelo->email}, role={$marcelo->role}, level={$marcelo->level}\n";
} else {
    echo "(nao encontrado)\n";
}

echo "\n=== SETTINGS RELEVANTES ===\n";
$keys = [
    'platform_owner_user_id',
    'platform_admin_user_id',
    'platform_marketing_user_id',
    'platform_fee_percent',
    'platform_fee_owner_percent',
    'platform_fee_admin_percent',
    'platform_fee_marketing_percent',
    'seller_fee_percent',
];
foreach ($keys as $k2) {
    echo $k2 . ' = ' . (Setting::get($k2, '(missing)')) . "\n";
}

echo "\n=== COLUNAS na tabela users ===\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
echo implode(', ', $columns) . "\n";
