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

            // Carregar integrações/infra (reCAPTCHA, S3 e limites de upload) se existirem (sobrescreve .env)
            try {
                $extraSettings = DB::table('settings')->whereIn('key', [
                    // reCAPTCHA v3
                    'recaptcha_v3_site_key',
                    'recaptcha_v3_secret_key',
                    'recaptcha_v3_min_score',

                    // Upload limits
                    'video_max_mb',
                    'document_max_mb',
                    'allowed_video_formats',
                    'allowed_document_formats',
                    'uploads_storage_disk',

                    // S3 (compatível com qualquer storage S3)
                    's3_key',
                    's3_secret',
                    's3_region',
                    's3_bucket',
                    's3_url',
                    's3_endpoint',
                    's3_path_style',
                ])->pluck('value', 'key');

                $normalizeList = function ($value): array {
                    $raw = trim((string) $value);
                    if ($raw === '') {
                        return [];
                    }

                    $parts = preg_split('/[,\s;]+/', $raw) ?: [];
                    $parts = array_map(static fn ($p) => strtolower(trim((string) $p)), $parts);
                    $parts = array_values(array_filter($parts, static fn ($p) => $p !== '' && preg_match('/^[a-z0-9]+$/', $p)));
                    $parts = array_values(array_unique($parts));

                    return $parts;
                };

                // reCAPTCHA v3 (sobrescreve config/services.php)
                $recaptchaSiteKey = trim((string) ($extraSettings['recaptcha_v3_site_key'] ?? ''));
                if ($recaptchaSiteKey !== '') {
                    config(['services.recaptcha.site_key' => $recaptchaSiteKey]);
                }

                $recaptchaSecret = trim((string) ($extraSettings['recaptcha_v3_secret_key'] ?? ''));
                if ($recaptchaSecret !== '') {
                    config(['services.recaptcha.v3_secret' => $recaptchaSecret]);
                }

                $recaptchaMinScoreRaw = trim((string) ($extraSettings['recaptcha_v3_min_score'] ?? ''));
                if ($recaptchaMinScoreRaw !== '') {
                    $recaptchaMinScoreRaw = str_replace(',', '.', $recaptchaMinScoreRaw);
                    config(['services.recaptcha.v3_min_score' => (float) $recaptchaMinScoreRaw]);
                }

                // Upload limits (sobrescreve config/uploads.php)
                $videoMax = trim((string) ($extraSettings['video_max_mb'] ?? ''));
                if ($videoMax !== '' && is_numeric($videoMax)) {
                    config(['uploads.video_max_mb' => (int) $videoMax]);
                }

                $docMax = trim((string) ($extraSettings['document_max_mb'] ?? ''));
                if ($docMax !== '' && is_numeric($docMax)) {
                    config(['uploads.document_max_mb' => (int) $docMax]);
                }

                $allowedVideosRaw = trim((string) ($extraSettings['allowed_video_formats'] ?? ''));
                if ($allowedVideosRaw !== '') {
                    config(['uploads.allowed_video_formats' => $normalizeList($allowedVideosRaw)]);
                }

                $allowedDocsRaw = trim((string) ($extraSettings['allowed_document_formats'] ?? ''));
                if ($allowedDocsRaw !== '') {
                    config(['uploads.allowed_document_formats' => $normalizeList($allowedDocsRaw)]);
                }

                $uploadsDisk = trim((string) ($extraSettings['uploads_storage_disk'] ?? ''));
                if (in_array($uploadsDisk, ['public', 's3'], true)) {
                    config(['uploads.disk' => $uploadsDisk]);
                }

                // S3 (sobrescreve config/filesystems.php)
                $s3Key = trim((string) ($extraSettings['s3_key'] ?? ''));
                if ($s3Key !== '') {
                    config(['filesystems.disks.s3.key' => $s3Key]);
                }

                $s3Secret = trim((string) ($extraSettings['s3_secret'] ?? ''));
                if ($s3Secret !== '') {
                    config(['filesystems.disks.s3.secret' => $s3Secret]);
                }

                $s3Region = trim((string) ($extraSettings['s3_region'] ?? ''));
                if ($s3Region !== '') {
                    config(['filesystems.disks.s3.region' => $s3Region]);
                }

                $s3Bucket = trim((string) ($extraSettings['s3_bucket'] ?? ''));
                if ($s3Bucket !== '') {
                    config(['filesystems.disks.s3.bucket' => $s3Bucket]);
                }

                $s3Url = trim((string) ($extraSettings['s3_url'] ?? ''));
                if ($s3Url !== '') {
                    config(['filesystems.disks.s3.url' => $s3Url]);
                }

                $s3Endpoint = trim((string) ($extraSettings['s3_endpoint'] ?? ''));
                if ($s3Endpoint !== '') {
                    config(['filesystems.disks.s3.endpoint' => $s3Endpoint]);
                }

                if (array_key_exists('s3_path_style', $extraSettings)) {
                    config(['filesystems.disks.s3.use_path_style_endpoint' => (bool) ((int) $extraSettings['s3_path_style'])]);
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
