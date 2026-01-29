<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

// Auth routes - will be wired with Laravel auth (Breeze)

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function() {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('events', '\\App\\Http\\Controllers\\EventController');
    Route::resource('children', '\\App\\Http\\Controllers\\ChildController');
    Route::post('children/{child}/sponsor', '\\App\\Http\\Controllers\\SponsorController@store');
});
