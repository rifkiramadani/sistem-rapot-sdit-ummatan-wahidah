<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // return Inertia::render('welcome');
    return redirect('/login');
})->name('home');

Route::prefix('protected')->middleware(['auth'])->group(function () {
    Route::get('', function () {
        return Inertia::render('protected/dashboard/index');
    })->name('protected.dashboard.index');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
