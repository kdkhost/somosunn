<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrackServiceVisit::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\ApplyCustomMaintenanceMode::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\TrackReferralLink::class,
            \App\Http\Middleware\TrackVisitor::class,
            \App\Http\Middleware\RunInternalCron::class,
            \App\Http\Middleware\LogUserActivity::class,
        ],
        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'redirect.members.admin' => \App\Http\Middleware\RedirectMembersFromAdmin::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'check.feature' => \App\Http\Middleware\CheckFeature::class,
        'check.connection' => \App\Http\Middleware\EnsureConnectionIsAccepted::class,
        'check.plan' => \App\Http\Middleware\EnsureUserHasActivePlan::class,
        'check.marketplace.seller' => \App\Http\Middleware\CheckMarketplaceSeller::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}
