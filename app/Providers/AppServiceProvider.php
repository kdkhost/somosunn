<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // register bindings if necessary
    }

    public function boot()
    {
        RateLimiter::for('invoices_email', function ($job) {
            return Limit::perHour(100);
        });

        \App\Models\User::observe(\App\Observers\UserObserver::class);

        View::composer(['admin.partials.navbar', 'admin.partials.sidebar'], \App\Http\View\Composers\NavbarComposer::class);

        View::share('unnDbAvailable', true);

        try {
            App::setLocale('pt_BR');
        } catch (\Throwable $e) {
            Log::warning('Falha ao ajustar locale: ' . $e->getMessage());
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
                    'social_google_client_id',
                    'social_google_client_secret',
                    'social_google_redirect',
                    'social_facebook_client_id',
                    'social_facebook_client_secret',
                    'social_facebook_redirect',
                    'social_linkedin_client_id',
                    'social_linkedin_client_secret',
                    'social_linkedin_redirect'
                ])->pluck('value', 'key')->toArray();

                if (isset($socialSettings['social_google_client_id']) && $socialSettings['social_google_client_id']) {
                    config(['services.google.client_id' => $socialSettings['social_google_client_id']]);
                    if (isset($socialSettings['social_google_client_secret']))
                        config(['services.google.client_secret' => $socialSettings['social_google_client_secret']]);
                    if (isset($socialSettings['social_google_redirect']))
                        config(['services.google.redirect' => $socialSettings['social_google_redirect']]);
                }

                if (isset($socialSettings['social_facebook_client_id']) && $socialSettings['social_facebook_client_id']) {
                    config(['services.facebook.client_id' => $socialSettings['social_facebook_client_id']]);
                    if (isset($socialSettings['social_facebook_client_secret']))
                        config(['services.facebook.client_secret' => $socialSettings['social_facebook_client_secret']]);
                    if (isset($socialSettings['social_facebook_redirect']))
                        config(['services.facebook.redirect' => $socialSettings['social_facebook_redirect']]);
                }

                if (isset($socialSettings['social_linkedin_client_id']) && $socialSettings['social_linkedin_client_id']) {
                    config(['services.linkedin.client_id' => $socialSettings['social_linkedin_client_id']]);
                    if (isset($socialSettings['social_linkedin_client_secret']))
                        config(['services.linkedin.client_secret' => $socialSettings['social_linkedin_client_secret']]);
                    if (isset($socialSettings['social_linkedin_redirect']))
                        config(['services.linkedin.redirect' => $socialSettings['social_linkedin_redirect']]);
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
                ])->pluck('value', 'key')->toArray();

                $normalizeList = function ($value): array {
                    $raw = trim((string) $value);
                    if ($raw === '') {
                        return [];
                    }

                    $parts = preg_split('/[,\s;]+/', $raw) ?: [];
                    $parts = array_map(static fn($p) => strtolower(trim((string) $p)), $parts);
                    $parts = array_values(array_filter($parts, static fn($p) => $p !== '' && preg_match('/^[a-z0-9]+$/', $p)));
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

            // Carregar configurações de SMTP do banco de dados (sobrescreve .env)
            try {
                $smtpSettings = DB::table('settings')->whereIn('key', [
                    'smtp_host',
                    'smtp_port',
                    'smtp_username',
                    'smtp_password',
                    'smtp_encryption',
                    'smtp_from_email',
                    'smtp_from_name',
                ])->pluck('value', 'key')->toArray();

                $smtpHost = trim((string) ($smtpSettings['smtp_host'] ?? ''));
                if ($smtpHost !== '') {
                    $encryption = $smtpSettings['smtp_encryption'] ?? 'tls';
                    if ($encryption === 'null' || $encryption === '') {
                        $encryption = null;
                    }

                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.transport' => 'smtp',
                        'mail.mailers.smtp.host' => $smtpHost,
                        'mail.mailers.smtp.port' => trim((string) ($smtpSettings['smtp_port'] ?? '587')),
                        'mail.mailers.smtp.username' => trim((string) ($smtpSettings['smtp_username'] ?? '')),
                        'mail.mailers.smtp.password' => trim((string) ($smtpSettings['smtp_password'] ?? '')),
                        'mail.mailers.smtp.encryption' => $encryption,
                        'mail.mailers.smtp.timeout' => null,
                        'mail.mailers.smtp.auth_mode' => null,
                    ]);

                    $fromEmail = trim((string) ($smtpSettings['smtp_from_email'] ?? ''));
                    if ($fromEmail !== '') {
                        config(['mail.from.address' => $fromEmail]);
                    }

                    $fromName = trim((string) ($smtpSettings['smtp_from_name'] ?? ''));
                    if ($fromName !== '') {
                        config(['mail.from.name' => $fromName]);
                    }
                }
            } catch (\Throwable $e) {
                // Silently fail if table doesnt exist yet
            }

            // Carregar configurações de Pagamento (Mercado Pago / PagSeguro)
            try {
                $paymentSettings = DB::table('settings')->whereIn('key', [
                    // Config Env
                    'mercadopago_env',
                    'pagseguro_env',
                    // Mercado Pago OAuth (App Credentials)
                    'mercadopago_client_id',
                    'mercadopago_client_secret',
                    // Mercado Pago Prod
                    'mercadopago_prod_public_key',
                    'mercadopago_prod_access_token',
                    // Mercado Pago Sandbox
                    'mercadopago_sandbox_public_key',
                    'mercadopago_sandbox_access_token',
                    // PagSeguro
                    'pagseguro_email',
                $mpPublicKeySandbox = trim((string) (($paymentSettings['gateway_mercadopago_public_key_sandbox'] ?? '') ?: ($paymentSettings['mercadopago_sandbox_public_key'] ?? '')));

                $mpAccessTokenProd = trim((string) (($paymentSettings['gateway_mercadopago_access_token_prod'] ?? '') ?: ($paymentSettings['mercadopago_prod_access_token'] ?? '')));
                $mpPublicKeyProd = trim((string) (($paymentSettings['gateway_mercadopago_public_key_prod'] ?? '') ?: ($paymentSettings['mercadopago_prod_public_key'] ?? '')));

                $mpAccessToken = $mpSandbox ? $mpAccessTokenSandbox : $mpAccessTokenProd;
                $mpPublicKey = $mpSandbox ? $mpPublicKeySandbox : $mpPublicKeyProd;

                if ($mpAccessToken) {
                    config(['payments.mercadopago.access_token' => $mpAccessToken]);
                    config(['payments.mercadopago.public_key' => $mpPublicKey]);
                    config(['payments.mercadopago.sandbox' => $mpSandbox]);
                }

                // MercadoPago OAuth (Platform Credentials)
                $mpClientId = trim((string) ($paymentSettings['mercadopago_client_id'] ?? ''));
                $mpClientSecret = trim((string) ($paymentSettings['mercadopago_client_secret'] ?? ''));
                $mpRedirectUri = trim((string) ($paymentSettings['mercadopago_redirect_uri'] ?? ''));

                if ($mpClientId !== '') {
                    config(['payments.mercadopago.client_id' => $mpClientId]);
                }
                if ($mpClientSecret !== '') {
                    config(['payments.mercadopago.client_secret' => $mpClientSecret]);
                }
                if ($mpRedirectUri !== '') {
                    config(['payments.mercadopago.redirect_uri' => $mpRedirectUri]);
                }

                // PagSeguro
                $psEnv = trim((string) ($paymentSettings['pagseguro_env'] ?? ''));
                $psSandbox = (bool) ($paymentSettings['gateway_pagseguro_sandbox'] ?? 0);
                if ($psEnv !== '') {
                    $psSandbox = $psEnv === 'sandbox';
                }

                $psTokenSandbox = trim((string) (($paymentSettings['gateway_pagseguro_token_sandbox'] ?? '') ?: ($paymentSettings['pagseguro_sandbox_token'] ?? '')));
                $psTokenProd = trim((string) (($paymentSettings['gateway_pagseguro_token_prod'] ?? '') ?: ($paymentSettings['pagseguro_prod_token'] ?? '')));
                $psToken = $psSandbox ? $psTokenSandbox : $psTokenProd;

                $psEmail = trim((string) (($paymentSettings['gateway_pagseguro_email'] ?? '') ?: ($paymentSettings['pagseguro_email'] ?? '')));

                if ($psToken && $psEmail) {
                    config(['payments.pagseguro.email' => $psEmail]);
                    config(['payments.pagseguro.token' => $psToken]);
                    config(['payments.pagseguro.sandbox' => $psSandbox]);
                }

            } catch (\Throwable $e) {
                // Silently fail if table doesnt exist yet
            }

        } catch (\Throwable $e) {
            Log::warning('Banco de dados indisponível: ' . $e->getMessage());
            View::share('unnDbAvailable', false);
        }
    }
}
