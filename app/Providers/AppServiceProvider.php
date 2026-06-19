<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 */

namespace App\Providers;

use App\Support\UploadStorage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // ========================================================
        // Bindings de interfaces para os services do modulo
        // advanced-security-performance.
        //
        // Services stateless sao registrados como singleton para
        // economizar instanciacao por request. RateLimiterInterface,
        // HealthCheckInterface, LogRotatorInterface e CspConfigInterface
        // nao recebem bind aqui porque suas implementacoes concretas
        // sao, respectivamente, um Middleware (resolvido pelo router),
        // um Controller (resolvido pelo router) e um Command
        // (resolvido pelo console kernel) - bind direto causaria
        // dependencia circular ou resolucao incorreta.
        // ========================================================

        $this->app->singleton(
            \App\Contracts\QueueManagerInterface::class,
            \App\Services\QueueManagerService::class
        );

        $this->app->singleton(
            \App\Contracts\ImageProcessorInterface::class,
            \App\Services\ImageProcessorService::class
        );

        $this->app->singleton(
            \App\Contracts\PresignedUrlInterface::class,
            \App\Services\PresignedUrlService::class
        );

        $this->app->singleton(
            \App\Contracts\AdvancedCacheInterface::class,
            \App\Services\AdvancedCacheManager::class
        );

        $this->app->singleton(
            \App\Contracts\AuditLogInterface::class,
            \App\Services\AuditLogService::class
        );

        $this->app->singleton(
            \App\Contracts\BackupInterface::class,
            \App\Services\BackupService::class
        );

        $this->app->singleton(
            \App\Contracts\AnomalyDetectorInterface::class,
            \App\Services\AnomalyDetectorService::class
        );

        // Sem bind: RateLimiterInterface (implementacao em
        // App\Http\Middleware\AdvancedRateLimitMiddleware - resolvida
        // pelo router HTTP).
        //
        // Sem bind: HealthCheckInterface (implementacao em
        // App\Http\Controllers\HealthController - resolvido pelo router).
        //
        // Sem bind: LogRotatorInterface (implementacao em
        // App\Console\Commands\LogsCleanup - resolvido pelo console kernel).
        //
        // Sem bind: CspConfigInterface (sem implementacao concreta dedicada
        // ainda; sera registrada quando criada).
    }

    public function boot()
    {
        $this->blockDestructiveDatabaseCommandsInProduction();

        Model::preventLazyLoading(!App::isProduction());
        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation): void {
            Log::warning('Carregamento N+1 detectado', [
                'model' => $model::class,
                'relation' => $relation,
                'route' => request()?->route()?->getName(),
            ]);
        });

        if (config('database.performance.log_slow_queries', true)) {
            DB::listen(function (QueryExecuted $query): void {
                $threshold = max(100, (int) config('database.performance.slow_query_ms', 1000));
                if ($query->time < $threshold) {
                    return;
                }

                Log::warning('Consulta lenta detectada', [
                    'connection' => $query->connectionName,
                    'duration_ms' => round($query->time, 2),
                    'sql' => $query->sql,
                    'route' => request()?->route()?->getName(),
                ]);
            });
        }

        // Paginação: usar Bootstrap-5 como padrão (AdminLTE usa Bootstrap)
        // O painel Tailwind usa ->links() que herda este padrão mas funciona OK
        // pois Bootstrap-5 pagination é compatível com ambos os temas
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Corrige erro do tipo "enum" nas migrations usando Doctrine DBAL (executa logo no início)
        try {
            if (
                class_exists('Doctrine\\DBAL\\Types\\Type') &&
                \Illuminate\Support\Facades\DB::getDefaultConnection() &&
                \Illuminate\Support\Facades\DB::connection()->getDoctrineSchemaManager()
            ) {
                \Illuminate\Support\Facades\DB::connection()
                    ->getDoctrineSchemaManager()
                    ->getDatabasePlatform()
                    ->registerDoctrineTypeMapping('enum', 'string');
            }
        } catch (\Throwable $e) {
            // Ignora se não estiver usando DBAL
        }

        RateLimiter::for('invoices_email', function ($job) {
            return Limit::perHour(100);
        });

        RateLimiter::for('webhook_mercadopago', function ($request) {
            return Limit::perMinute(120)->by((string) $request->ip());
        });

        RateLimiter::for('event_reservations', function ($request) {
            $event = $request->route('event');
            $eventKey = is_object($event) && method_exists($event, 'getRouteKey')
                ? (string) $event->getRouteKey()
                : (string) $event;

            $identity = trim((string) (
                $request->input('email')
                ?: $request->input('cpf')
                ?: $request->input('document')
                ?: 'guest'
            ));
            $identity = strtolower((string) preg_replace('/[^a-z0-9@._-]+/i', '', $identity));
            $identity = $identity !== '' ? $identity : 'guest';
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(6)->by('event-reservation:' . sha1($eventKey . '|' . $identity . '|' . $ip)),
                Limit::perMinute(30)->by('event-reservation-ip:' . sha1($eventKey . '|' . $ip)),
            ];
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
            UploadStorage::applyRuntimeConfig();
        }

        UploadStorage::applyRuntimeConfig();

        try {
            DB::connection()->getPdo();

            // Carregar configurações sociais se existirem (sobrescreve .env)
            try {
                $socialSettings = DB::table('settings')->whereIn('key', [
                    'social_login_enabled',
                    'social_google_client_id',
                    'social_google_client_secret',
                    'social_google_redirect',
                    'social_facebook_client_id',
                    'social_facebook_client_secret',
                    'social_facebook_app_id',
                    'social_facebook_app_secret',
                    'social_facebook_redirect',
                    'social_linkedin_client_id',
                    'social_linkedin_client_secret',
                    'social_linkedin_redirect'
                ])->pluck('value', 'key')->toArray();

                $resolveSetting = static function (array $settings, array $keys, string $default = ''): string {
                    foreach ($keys as $key) {
                        if (!array_key_exists($key, $settings)) {
                            continue;
                        }

                        $value = trim((string) $settings[$key]);
                        if ($value !== '') {
                            return $value;
                        }
                    }

                    return $default;
                };

                $googleClientId = $resolveSetting($socialSettings, ['social_google_client_id']);
                $googleClientSecret = $resolveSetting($socialSettings, ['social_google_client_secret']);
                $googleRedirect = $resolveSetting($socialSettings, ['social_google_redirect']);

                if ($googleClientId !== '') {
                    config(['services.google.client_id' => $googleClientId]);
                }
                if ($googleClientSecret !== '') {
                    config(['services.google.client_secret' => $googleClientSecret]);
                }
                if ($googleRedirect !== '') {
                    config(['services.google.redirect' => $googleRedirect]);
                }

                $facebookClientId = $resolveSetting($socialSettings, ['social_facebook_client_id', 'social_facebook_app_id']);
                $facebookClientSecret = $resolveSetting($socialSettings, ['social_facebook_client_secret', 'social_facebook_app_secret']);
                $facebookRedirect = $resolveSetting($socialSettings, ['social_facebook_redirect']);

                if ($facebookClientId !== '') {
                    config(['services.facebook.client_id' => $facebookClientId]);
                }
                if ($facebookClientSecret !== '') {
                    config(['services.facebook.client_secret' => $facebookClientSecret]);
                }
                if ($facebookRedirect !== '') {
                    config(['services.facebook.redirect' => $facebookRedirect]);
                }

                $linkedinClientId = $resolveSetting($socialSettings, ['social_linkedin_client_id']);
                $linkedinClientSecret = $resolveSetting($socialSettings, ['social_linkedin_client_secret']);
                $linkedinRedirect = $resolveSetting($socialSettings, ['social_linkedin_redirect']);

                if ($linkedinClientId !== '') {
                    config(['services.linkedin.client_id' => $linkedinClientId]);
                }
                if ($linkedinClientSecret !== '') {
                    config(['services.linkedin.client_secret' => $linkedinClientSecret]);
                }
                if ($linkedinRedirect !== '') {
                    config(['services.linkedin.redirect' => $linkedinRedirect]);
                }
            } catch (\Throwable $e) {
                // Silently fail if table doesnt exist yet
            }

            // Carregar integrações/infra (reCAPTCHA e limites de upload) se existirem (sobrescreve .env)
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


                UploadStorage::applyRuntimeConfig($extraSettings);
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

            // Carregar configurações de Pagamento (Mercado Pago)
            try {
                $paymentSettings = DB::table('settings')->whereIn('key', [
                    // Config Env
                    'mercadopago_env',
                    // Mercado Pago OAuth (App Credentials)
                    'mercadopago_client_id',
                    'mercadopago_client_secret',
                    // Mercado Pago Prod
                    'mercadopago_prod_public_key',
                    'mercadopago_prod_access_token',
                    // Mercado Pago Sandbox
                    'mercadopago_sandbox_public_key',
                    'mercadopago_sandbox_access_token',
                    // Generic Overrides (Marketplace Sync)
                    'mercadopago_public_key',
                    'mercadopago_access_token',
                    // Rastreamento de qualidade / integrador
                    'mercadopago_integrator_id',
                    'mercadopago_platform_id',
                ])->pluck('value', 'key')->toArray();

                // 1. Mercado Pago App Credentials (OAuth) - Always needed for splits
                if (!empty($paymentSettings['mercadopago_client_id'])) {
                    config(['payments.mercadopago.client_id' => $paymentSettings['mercadopago_client_id']]);
                }
                if (!empty($paymentSettings['mercadopago_client_secret'])) {
                    config(['payments.mercadopago.client_secret' => $paymentSettings['mercadopago_client_secret']]);
                }

                // 2. Mercado Pago Platform Access (Check Environment)
                $mpEnv = $paymentSettings['mercadopago_env'] ?? 'sandbox';
                if ($mpEnv === 'production') {
                    if (!empty($paymentSettings['mercadopago_prod_public_key'])) {
                        config(['payments.mercadopago.public_key' => $paymentSettings['mercadopago_prod_public_key']]);
                    }
                    if (!empty($paymentSettings['mercadopago_prod_access_token'])) {
                        config(['payments.mercadopago.access_token' => $paymentSettings['mercadopago_prod_access_token']]);
                    }
                } else {
                    // Sandbox
                    if (!empty($paymentSettings['mercadopago_sandbox_public_key'])) {
                        config(['payments.mercadopago.public_key' => $paymentSettings['mercadopago_sandbox_public_key']]);
                    }
                    if (!empty($paymentSettings['mercadopago_sandbox_access_token'])) {
                        config(['payments.mercadopago.access_token' => $paymentSettings['mercadopago_sandbox_access_token']]);
                    }
                }

                // 3. Integrador / Rastreamento de qualidade
                if (!empty($paymentSettings['mercadopago_integrator_id'])) {
                    config(['payments.mercadopago.integrator_id' => $paymentSettings['mercadopago_integrator_id']]);
                }
                if (!empty($paymentSettings['mercadopago_platform_id'])) {
                    config(['payments.mercadopago.platform_id' => $paymentSettings['mercadopago_platform_id']]);
                }

            } catch (\Throwable $e) {
                // Silently fail if table doesnt exist yet
            }

        } catch (\Throwable $e) {
            Log::warning('Banco de dados indisponível: ' . $e->getMessage());
            View::share('unnDbAvailable', false);
            UploadStorage::applyRuntimeConfig();
        }
    }

    private function blockDestructiveDatabaseCommandsInProduction(): void
    {
        if (!App::runningInConsole() || !App::isProduction()) {
            return;
        }

        $command = (string) ($_SERVER['argv'][1] ?? '');
        $blockedCommands = [
            'migrate:rollback',
            'migrate:reset',
            'migrate:fresh',
            'db:wipe',
        ];

        if (!in_array($command, $blockedCommands, true)) {
            return;
        }

        if ((string) env('ALLOW_PRODUCTION_DESTRUCTIVE_DB_COMMANDS') === 'SOMOS_UNN_CONFIRMAR_DESTRUICAO') {
            Log::critical('Comando destrutivo de banco liberado em producao por confirmacao explicita.', [
                'command' => $command,
            ]);

            return;
        }

        Log::critical('Comando destrutivo de banco bloqueado em producao.', [
            'command' => $command,
        ]);

        throw new \RuntimeException(
            'Comando bloqueado em producao. Defina ALLOW_PRODUCTION_DESTRUCTIVE_DB_COMMANDS=SOMOS_UNN_CONFIRMAR_DESTRUICAO apenas se tiver backup validado.'
        );
    }
}
