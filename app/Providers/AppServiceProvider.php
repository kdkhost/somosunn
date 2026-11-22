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
        $assetHelpers = app_path('Helpers/AssetHelpers.php');
        if (is_file($assetHelpers)) {
            require_once $assetHelpers;
        }

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

        RateLimiter::for('order_control_email', function ($job) {
            return Limit::perMinute(60)->by('order-control-email');
        });

        RateLimiter::for('webhook_mercadopago', function ($request) {
            return Limit::perMinute(120)->by((string) $request->ip());
        });

        RateLimiter::for('webhook_sumup', function ($request) {
            return Limit::perMinute(120)->by((string) $request->ip());
        });

        RateLimiter::for('webhook_efi', function ($request) {
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

            // Carrega configuracoes em uma unica leitura cacheada para reduzir queries no boot
            $allSettings = \App\Models\Setting::allValues();

            $cookieDomain = \App\Support\DomainManager::sessionCookieDomain();
            if (!App::runningInConsole() && $cookieDomain !== null) {
                config(['session.domain' => $cookieDomain]);
            }

            // Carregar configuracoes sociais se existirem (sobrescreve .env)
            try {
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

                $googleClientId = $resolveSetting($allSettings, ['social_google_client_id']);
                $googleClientSecret = $resolveSetting($allSettings, ['social_google_client_secret']);
                $googleRedirect = $resolveSetting($allSettings, ['social_google_redirect']);

                if ($googleClientId !== '') {
                    config(['services.google.client_id' => $googleClientId]);
                }
                if ($googleClientSecret !== '') {
                    config(['services.google.client_secret' => $googleClientSecret]);
                }
                if ($googleRedirect !== '') {
                    config(['services.google.redirect' => $googleRedirect]);
                }

                $facebookClientId = $resolveSetting($allSettings, ['social_facebook_client_id', 'social_facebook_app_id']);
                $facebookClientSecret = $resolveSetting($allSettings, ['social_facebook_client_secret', 'social_facebook_app_secret']);
                $facebookRedirect = $resolveSetting($allSettings, ['social_facebook_redirect']);

                if ($facebookClientId !== '') {
                    config(['services.facebook.client_id' => $facebookClientId]);
                }
                if ($facebookClientSecret !== '') {
                    config(['services.facebook.client_secret' => $facebookClientSecret]);
                }
                if ($facebookRedirect !== '') {
                    config(['services.facebook.redirect' => $facebookRedirect]);
                }

                $linkedinClientId = $resolveSetting($allSettings, ['social_linkedin_client_id']);
                $linkedinClientSecret = $resolveSetting($allSettings, ['social_linkedin_client_secret']);
                $linkedinRedirect = $resolveSetting($allSettings, ['social_linkedin_redirect']);

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

            // Carregar integracoes/infra (reCAPTCHA e limites de upload)
            try {
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

                $recaptchaSiteKey = trim((string) ($allSettings['recaptcha_v3_site_key'] ?? ''));
                if ($recaptchaSiteKey !== '') {
                    config(['services.recaptcha.site_key' => $recaptchaSiteKey]);
                }

                $recaptchaSecret = trim((string) ($allSettings['recaptcha_v3_secret_key'] ?? ''));
                if ($recaptchaSecret !== '') {
                    config(['services.recaptcha.v3_secret' => $recaptchaSecret]);
                }

                $recaptchaMinScoreRaw = trim((string) ($allSettings['recaptcha_v3_min_score'] ?? ''));
                if ($recaptchaMinScoreRaw !== '') {
                    $recaptchaMinScoreRaw = str_replace(',', '.', $recaptchaMinScoreRaw);
                    config(['services.recaptcha.v3_min_score' => (float) $recaptchaMinScoreRaw]);
                }

                $videoMax = trim((string) ($allSettings['video_max_mb'] ?? ''));
                if ($videoMax !== '' && is_numeric($videoMax)) {
                    config(['uploads.video_max_mb' => (int) $videoMax]);
                }

                $docMax = trim((string) ($allSettings['document_max_mb'] ?? ''));
                if ($docMax !== '' && is_numeric($docMax)) {
                    config(['uploads.document_max_mb' => (int) $docMax]);
                }

                $allowedVideosRaw = trim((string) ($allSettings['allowed_video_formats'] ?? ''));
                if ($allowedVideosRaw !== '') {
                    config(['uploads.allowed_video_formats' => $normalizeList($allowedVideosRaw)]);
                }

                $allowedDocsRaw = trim((string) ($allSettings['allowed_document_formats'] ?? ''));
                if ($allowedDocsRaw !== '') {
                    config(['uploads.allowed_document_formats' => $normalizeList($allowedDocsRaw)]);
                }

                UploadStorage::applyRuntimeConfig($allSettings);
            } catch (\Throwable $e) {
                // Silently fail if table doesnt exist yet
            }

            // Carregar configuracoes de SMTP do banco de dados
            try {
                $smtpHost = trim((string) ($allSettings['smtp_host'] ?? ''));
                if ($smtpHost !== '') {
                    $encryption = $allSettings['smtp_encryption'] ?? 'tls';
                    if ($encryption === 'null' || $encryption === '') {
                        $encryption = null;
                    }

                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.transport' => 'smtp',
                        'mail.mailers.smtp.host' => $smtpHost,
                        'mail.mailers.smtp.port' => trim((string) ($allSettings['smtp_port'] ?? '587')),
                        'mail.mailers.smtp.username' => trim((string) ($allSettings['smtp_username'] ?? '')),
                        'mail.mailers.smtp.password' => trim((string) ($allSettings['smtp_password'] ?? '')),
                        'mail.mailers.smtp.encryption' => $encryption,
                        'mail.mailers.smtp.timeout' => null,
                        'mail.mailers.smtp.auth_mode' => null,
                    ]);

                    $fromEmail = trim((string) ($allSettings['smtp_from_email'] ?? ''));
                    if ($fromEmail !== '') {
                        config(['mail.from.address' => $fromEmail]);
                    }

                    $fromName = trim((string) ($allSettings['smtp_from_name'] ?? ''));
                    if ($fromName !== '') {
                        config(['mail.from.name' => $fromName]);
                    }
                }
            } catch (\Throwable $e) {
                // Silently fail if table doesnt exist yet
            }

            // Carregar configuracoes de Pagamento (Mercado Pago + Efi Pro)
            try {
                if (!empty($allSettings['mercadopago_client_id'])) {
                    config(['payments.mercadopago.client_id' => $allSettings['mercadopago_client_id']]);
                }
                if (!empty($allSettings['mercadopago_client_secret'])) {
                    config(['payments.mercadopago.client_secret' => $allSettings['mercadopago_client_secret']]);
                }

                $mpEnv = $allSettings['mercadopago_env'] ?? 'sandbox';
                if ($mpEnv === 'production') {
                    if (!empty($allSettings['mercadopago_prod_public_key'])) {
                        config(['payments.mercadopago.public_key' => $allSettings['mercadopago_prod_public_key']]);
                    }
                    if (!empty($allSettings['mercadopago_prod_access_token'])) {
                        config(['payments.mercadopago.access_token' => $allSettings['mercadopago_prod_access_token']]);
                    }
                } else {
                    if (!empty($allSettings['mercadopago_sandbox_public_key'])) {
                        config(['payments.mercadopago.public_key' => $allSettings['mercadopago_sandbox_public_key']]);
                    }
                    if (!empty($allSettings['mercadopago_sandbox_access_token'])) {
                        config(['payments.mercadopago.access_token' => $allSettings['mercadopago_sandbox_access_token']]);
                    }
                }

                if (!empty($allSettings['mercadopago_integrator_id'])) {
                    config(['payments.mercadopago.integrator_id' => $allSettings['mercadopago_integrator_id']]);
                }
                if (!empty($allSettings['mercadopago_platform_id'])) {
                    config(['payments.mercadopago.platform_id' => $allSettings['mercadopago_platform_id']]);
                }

                if (!empty($allSettings['efi_client_id'])) {
                    config(['payments.efi.client_id' => $allSettings['efi_client_id']]);
                }
                if (!empty($allSettings['efi_client_secret'])) {
                    config(['payments.efi.client_secret' => $allSettings['efi_client_secret']]);
                }
                if (!empty($allSettings['efi_pix_key'])) {
                    config(['payments.efi.pix_key' => $allSettings['efi_pix_key']]);
                }
                if (!empty($allSettings['efi_webhook_secret'])) {
                    config(['payments.efi.webhook_secret' => $allSettings['efi_webhook_secret']]);
                }
                if (!empty($allSettings['efi_env'])) {
                    config(['payments.efi.env' => $allSettings['efi_env']]);
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
