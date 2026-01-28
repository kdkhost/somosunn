<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
});

// Auth routes stub - will be wired with Laravel auth

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function() {
        return Inertia::render('Dashboard');
    });

    Route::resource('events', '\\App\\Http\\Controllers\\EventController');
    Route::resource('children', '\\App\\Http\\Controllers\\ChildController');
    Route::post('children/{child}/sponsor', '\\App\\Http\\Controllers\\SponsorController@store');
});
