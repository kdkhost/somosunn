<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

use App\Models\Course;
use App\Models\GatewayAccount;
use App\Models\Setting;

$course = Course::find(3);
if (!$course) {
    echo "Curso 3 nao encontrado\n";
    exit;
}

$creatorId = (int) $course->user_id;
echo "Curso: {$course->title} (id=3)\n";
echo "Creator ID: {$creatorId}\n";

$creator = \App\Models\User::find($creatorId);
echo "Creator nome: " . ($creator?->name ?? '(sem nome)') . "\n";
echo "Creator is_admin: " . ($creator?->isAdmin() ? 'SIM' : 'NAO') . "\n";
echo "\n";

echo "=== Global settings ===\n";
echo "mercadopago_enabled = " . (int) Setting::get('mercadopago_enabled', 0) . "\n";
echo "sumup_enabled       = " . (int) Setting::get('sumup_enabled', 0) . "\n";
echo "\n";

echo "=== Gateway accounts do vendedor (user_id={$creatorId}) ===\n";
$accounts = GatewayAccount::where('user_id', $creatorId)->get();
if ($accounts->isEmpty()) {
    echo "(nenhum)\n";
} else {
    foreach ($accounts as $a) {
        echo "- provider={$a->provider}  enabled=" . ($a->enabled ? '1' : '0')
            . "  public_key=" . (trim((string) $a->public_key) !== '' ? substr($a->public_key, 0, 15) . '...' : '(vazio)')
            . "  access_token=" . (trim((string) $a->access_token) !== '' ? substr($a->access_token, 0, 15) . '...' : '(vazio)')
            . "\n";
    }
}

echo "\n=== resolveForSeller({$creatorId}) [MP] ===\n";
$mp = GatewayAccount::resolveForSeller($creatorId);
foreach ($mp as $k2 => $v) {
    echo "  $k2 = " . (is_string($v) && strlen($v) > 30 ? substr($v, 0, 20) . '...' : var_export($v, true)) . "\n";
}

echo "\n=== resolveForSellerSumUp({$creatorId}) ===\n";
$su = GatewayAccount::resolveForSellerSumUp($creatorId);
foreach ($su as $k2 => $v) {
    echo "  $k2 = " . (is_string($v) && strlen($v) > 30 ? substr($v, 0, 20) . '...' : var_export($v, true)) . "\n";
}
