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

$marketingFeatures = [
    'marketplace.sell',
    'courses.create',
    'events.create',
    'mentorships.create',
    'courses_access',
    'events_access',
    'mentorships_access',
];

$extra = $u->extra_features ?? [];
$extra = array_unique(array_merge($extra, $marketingFeatures));
$u->update(['extra_features' => array_values($extra)]);

echo "Features concedidas a {$u->name} (#{$u->id}):\n";
echo json_encode($u->fresh()->extra_features, JSON_PRETTY_PRINT) . "\n";
echo "canSellOnMarketplace: " . ($u->fresh()->canSellOnMarketplace() ? 'SIM' : 'NAO') . "\n";
echo "canAccessInstructorArea: " . ($u->fresh()->canAccessInstructorArea() ? 'SIM' : 'NAO') . "\n";
