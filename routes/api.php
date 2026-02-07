<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\CourseApiController;
use App\Http\Controllers\Api\MentorshipApiController;
use App\Http\Controllers\Api\PlanApiController;
use App\Http\Controllers\Api\TestimonialApiController;

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

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});
