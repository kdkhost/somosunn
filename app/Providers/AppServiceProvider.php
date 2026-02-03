<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // register bindings if necessary
    }

    public function boot()
    {
        View::share('unnDbAvailable', true);

        try {
            App::setLocale('pt_BR');
        } catch (\Throwable $e) {
            Log::warning('Falha ao ajustar locale: '.$e->getMessage());
        }

        if (App::runningInConsole()) {
            View::share('unnDbAvailable', false);
            return;
        }

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            Log::warning('Banco de dados indisponível: '.$e->getMessage());
            View::share('unnDbAvailable', false);
        }
    }
}
