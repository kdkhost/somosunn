<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Diagnostic Info</h1>";
echo "PHP Version: " . PHP_VERSION . "<br>";
$reflector = new ReflectionClass(App\Http\Controllers\SocialController::class);
echo "SocialController Path: " . $reflector->getFileName() . "<br>";
$content = file_get_contents($reflector->getFileName());
if (str_contains($content, 'Connection::getPendingBetween')) {
    echo "<b style='color:green'>SocialController IS UPDATED with Connection::getPendingBetween</b><br>";
} else {
    echo "<b style='color:red'>SocialController IS NOT UPDATED! Still using old methods.</b><br>";
}

$userReflector = new ReflectionClass(App\Models\User::class);
echo "User Model Path: " . $userReflector->getFileName() . "<br>";
$userContent = file_get_contents($userReflector->getFileName());
if (str_contains($userContent, 'hasPendingConnectionWith')) {
    echo "<b style='color:green'>User model HAS hasPendingConnectionWith</b><br>";
} else {
    echo "<b style='color:red'>User model MISSING hasPendingConnectionWith</b><br>";
}

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset executed.<br>";
}
?>