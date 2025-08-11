<?php

use App\Http\Controllers\Protected\SchoolController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // return Inertia::render('welcome');
    return redirect('/login');
})->name('home');

Route::prefix('protected')->name('protected.')->middleware(['auth'])->group(function () {
    Route::get('', function () {
        return Inertia::render('protected/dashboard/index');
    })->name('dashboard.index');

    // Route::get('schools', [SchoolController::class, 'index'])->name('schools.index');

    Route::prefix('schools')->group(function () {
        Route::get('', [SchoolController::class, 'index'])->name('schools.index');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
