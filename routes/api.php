<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\CourseApiController;
use App\Http\Controllers\Api\MentorshipApiController;
use App\Http\Controllers\Api\PlanApiController;
use App\Http\Controllers\Api\TestimonialApiController;
use App\Http\Controllers\Api\AffiliateApiController;

Route::prefix('v1')->group(function () {
    Route::get('/health', [HealthController::class, 'show']);

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::get('/events', [EventApiController::class, 'index']);
    Route::get('/events/{event}', [EventApiController::class, 'show']);

    Route::get('/courses', [CourseApiController::class, 'index']);
    Route::get('/courses/{course}', [CourseApiController::class, 'show']);

    Route::get('/mentorships', [MentorshipApiController::class, 'index']);
    Route::get('/mentorships/{mentorship}', [MentorshipApiController::class, 'show']);

    Route::get('/plans', [PlanApiController::class, 'index']);
    Route::get('/plans/{plan}', [PlanApiController::class, 'show']);

    Route::get('/testimonials', [TestimonialApiController::class, 'index']);

    Route::middleware(['auth:sanctum', \App\Http\Middleware\TrackApiTokenUsage::class])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::prefix('/affiliate')->group(function () {
            Route::get('/overview', [AffiliateApiController::class, 'overview']);
            Route::get('/materials', [AffiliateApiController::class, 'materials']);
            Route::get('/offers', [AffiliateApiController::class, 'offers']);
            Route::get('/landing-page', [AffiliateApiController::class, 'landingPage']);
            Route::get('/analytics', [AffiliateApiController::class, 'analytics']);
        });

        Route::middleware([\App\Http\Middleware\EnsureAffiliateSandboxApproved::class])->prefix('/sandbox/affiliate')->group(function () {
            Route::get('/overview', [AffiliateApiController::class, 'overview']);
            Route::get('/materials', [AffiliateApiController::class, 'materials']);
            Route::get('/offers', [AffiliateApiController::class, 'offers']);
            Route::get('/landing-page', [AffiliateApiController::class, 'landingPage']);
            Route::get('/analytics', [AffiliateApiController::class, 'analytics']);
        });
    });
});

// Webhooks (Public) - Removed throttle to allow gateway callbacks and easier testing
Route::prefix('v1/webhooks')->withoutMiddleware([\Illuminate\Routing\Middleware\ThrottleRequests::class . ':api'])->group(function () {
    Route::match(['get', 'post'], '/mercadopago', [App\Http\Controllers\PaymentWebhookController::class, 'mercadopago'])
        ->defaults('seller_id', 'platform')
        ->name('api.webhooks.mercadopago');
    Route::match(['get', 'post'], '/pagseguro', [App\Http\Controllers\PaymentWebhookController::class, 'pagSeguro'])
        ->name('api.webhooks.pagseguro');
    Route::post('/sumup', [App\Http\Controllers\PaymentWebhookController::class, 'sumup'])
        ->name('api.webhooks.sumup');
});
