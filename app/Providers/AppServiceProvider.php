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
        \App\Models\User::observe(\App\Observers\UserObserver::class);

        View::composer('admin.partials.navbar', \App\Http\View\Composers\NavbarComposer::class);

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
            
            // Carregar configurações sociais se existirem (sobrescreve .env)
            try {
                $socialSettings = DB::table('settings')->whereIn('key', [
                    'social_google_client_id', 'social_google_client_secret', 'social_google_redirect',
                    'social_facebook_client_id', 'social_facebook_client_secret', 'social_facebook_redirect',
                    'social_linkedin_client_id', 'social_linkedin_client_secret', 'social_linkedin_redirect'
                ])->pluck('value', 'key');

                if(isset($socialSettings['social_google_client_id']) && $socialSettings['social_google_client_id']){
                    config(['services.google.client_id' => $socialSettings['social_google_client_id']]);
                    if(isset($socialSettings['social_google_client_secret'])) config(['services.google.client_secret' => $socialSettings['social_google_client_secret']]);
                    if(isset($socialSettings['social_google_redirect'])) config(['services.google.redirect' => $socialSettings['social_google_redirect']]);
                }

                if(isset($socialSettings['social_facebook_client_id']) && $socialSettings['social_facebook_client_id']){
                    config(['services.facebook.client_id' => $socialSettings['social_facebook_client_id']]);
                    if(isset($socialSettings['social_facebook_client_secret'])) config(['services.facebook.client_secret' => $socialSettings['social_facebook_client_secret']]);
                    if(isset($socialSettings['social_facebook_redirect'])) config(['services.facebook.redirect' => $socialSettings['social_facebook_redirect']]);
                }

                if(isset($socialSettings['social_linkedin_client_id']) && $socialSettings['social_linkedin_client_id']){
                    config(['services.linkedin.client_id' => $socialSettings['social_linkedin_client_id']]);
                    if(isset($socialSettings['social_linkedin_client_secret'])) config(['services.linkedin.client_secret' => $socialSettings['social_linkedin_client_secret']]);
                    if(isset($socialSettings['social_linkedin_redirect'])) config(['services.linkedin.redirect' => $socialSettings['social_linkedin_redirect']]);
                }
            } catch (\Throwable $e) {
                // Silently fail if table doesnt exist yet
            }

        } catch (\Throwable $e) {
            Log::warning('Banco de dados indisponível: '.$e->getMessage());
            View::share('unnDbAvailable', false);
        }
    }
}
