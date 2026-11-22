<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/';

    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('webhook')
                ->group(base_path('routes/webhooks.php'));

            Route::middleware(['web', 'subdomain:app'])
                ->group(base_path('routes/app.php'));

            Route::middleware(['web.public', 'subdomain:public'])
                ->group(base_path('routes/public.php'));

            if (file_exists(base_path('routes/web.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            }
        });
    }
}
