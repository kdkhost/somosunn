<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Diagnostic Info v2</h1>";
echo "PHP Version: " . PHP_VERSION . "<br>";
$userReflector = new ReflectionClass(App\Models\User::class);
echo "User Model Path: " . $userReflector->getFileName() . "<br>";
$userContent = file_get_contents($userReflector->getFileName());

if (str_contains($userContent, 'canMessageUser')) {
    echo "<b style='color:green'>User model HAS canMessageUser method</b><br>";
} else {
    echo "<b style='color:red'>User model MISSING canMessageUser method!</b><br>";
}

if (str_contains($userContent, 'hasPendingConnectionWith')) {
    echo "<b style='color:green'>User model HAS hasPendingConnectionWith method</b><br>";
} else {
    echo "<b style='color:red'>User model MISSING hasPendingConnectionWith method!</b><br>";
}

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset executed.<br>";
}
echo "<h2>Storage Check</h2>";
$storagePath = storage_path('app/public');
echo "Storage public path: " . $storagePath . "<br>";
if (is_dir($storagePath)) {
    echo "Storage directory exists.<br>";
} else {
    echo "Storage directory MISSING!<br>";
}

$publicStoragePath = public_path('storage');
echo "Public storage symlink path: " . $publicStoragePath . "<br>";
if (is_link($publicStoragePath)) {
    echo "Public storage symlink exists.<br>";
} else {
    echo "Public storage symlink MISSING or not a link!<br>";
}
?>