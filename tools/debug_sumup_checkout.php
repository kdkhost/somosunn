<?php
// Debug: por que SumUp nao aparece no checkout
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use App\Services\SumUpService;

echo "=== DEBUG SUMUP NO CHECKOUT ===\n\n";

$service = app(SumUpService::class);

echo "1. sumup_enabled (setting): " . Setting::get('sumup_enabled', 'NULL') . "\n";
echo "2. sumup_api_key vazio? " . (empty(Setting::get('sumup_api_key')) ? 'SIM' : 'NAO') . "\n";
echo "3. sumup_merchant_code vazio? " . (empty(Setting::get('sumup_merchant_code')) ? 'SIM' : 'NAO') . "\n";
echo "4. SumUpService->isEnabled(): " . ($service->isEnabled() ? 'true' : 'false') . "\n";

echo "\n5. isAllowedForUser('member'): " . ($service->isAllowedForUser('member') ? 'true' : 'false') . "\n";
echo "6. sumup_allow_members: " . Setting::get('sumup_allow_members', 'NULL') . "\n";

echo "\n7. isAllowedForProduct('course'): " . ($service->isAllowedForProduct('course') ? 'true' : 'false') . "\n";
echo "8. sumup_allow_courses: " . Setting::get('sumup_allow_courses', 'NULL') . "\n";

echo "\n9. isAmountAllowed(147.90): " . ($service->isAmountAllowed(147.90) ? 'true' : 'false') . "\n";
echo "10. sumup_minimum_amount: " . Setting::get('sumup_minimum_amount', 'NULL') . "\n";
echo "11. sumup_maximum_amount: " . Setting::get('sumup_maximum_amount', 'NULL') . "\n";

echo "\n=== shouldShowSumUp(147.90, 'course', 'member') ===\n";
$show = $service->isEnabled()
        && $service->isAllowedForUser('member')
        && $service->isAllowedForProduct('course')
        && $service->isAmountAllowed(147.90);
echo "Resultado: " . ($show ? 'MOSTRA SumUp' : 'NAO MOSTRA SumUp') . "\n";
