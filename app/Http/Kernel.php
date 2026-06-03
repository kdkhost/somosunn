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

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // Primeiro gate de segurança — ver spec waf-e-auditoria-seguranca.
        // Curto-circuita quando WAF_ENABLED=false (padrão em Fase 0).
        \App\Http\Middleware\WafMiddleware::class,
        // Rate limiting avançado por IP/UA — spec advanced-security-performance.
        // Posicionado após o WAF e antes do bloqueio de rotas sensíveis para
        // permitir bloqueio precoce de scanners e flood antes do roteamento.
        \App\Http\Middleware\AdvancedRateLimitMiddleware::class,
        // Bloqueia rotas sensíveis (install, migrations, debug) em produção
        \App\Http\Middleware\BlockSensitiveRoutesInProduction::class,
        \App\Http\Middleware\TrackServiceVisit::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\ProtectSupervisedAccess::class,
            \App\Http\Middleware\ApplyCustomMaintenanceMode::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\TrackReferralLink::class,
            \App\Http\Middleware\TrackVisitor::class,
            \App\Http\Middleware\RunInternalCron::class,
            \App\Http\Middleware\LogUserActivity::class,
            \App\Http\Middleware\EnsureLgpdConsent::class,
            // Pass-through observador para o AnomalyDetectorService.
            \App\Http\Middleware\AnomalyDetectorMiddleware::class,
        ],
        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'redirect.members.admin' => \App\Http\Middleware\RedirectMembersFromAdmin::class,
        'check.feature' => \App\Http\Middleware\CheckFeature::class,
        'check.blocked' => \App\Http\Middleware\CheckUserBlocked::class,
        'check.connection' => \App\Http\Middleware\EnsureConnectionIsAccepted::class,
        'check.plan' => \App\Http\Middleware\EnsureUserHasActivePlan::class,
        'check.marketplace.seller' => \App\Http\Middleware\CheckMarketplaceSeller::class,
        'check.sumup.permissions' => \App\Http\Middleware\CheckSumUpPermissions::class,
        'superadmin.legacy' => \App\Http\Middleware\RedirectSuperadminToLegacy::class,
        'sensitive.production' => \App\Http\Middleware\BlockSensitiveRoutesInProduction::class,
        'rate.limit.advanced' => \App\Http\Middleware\AdvancedRateLimitMiddleware::class,
        'security.headers' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
    ];
}
