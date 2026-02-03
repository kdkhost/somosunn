<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$settings = App\Models\Setting::all()->pluck('value','key')->toArray();
$keys = ['logo_image','favicon_image','logo_admin','logo_auth','logo_front','preloader_image','pwa_icon_192','pwa_icon_512','pwa_splash','pwa_banner'];
foreach($keys as $k){
    echo $k . ': ' . ($settings[$k] ?? '') . PHP_EOL;
}
?>