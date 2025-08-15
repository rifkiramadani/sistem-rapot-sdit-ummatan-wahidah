<?php

use Inertia\Inertia;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Protected\SchoolController;
use App\Http\Controllers\Protected\AcademicYearController;

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

    // ROUTE FOR ACADEMIC YEAR
    Route::prefix('academic-years')->group(function () {
        Route::get('', [AcademicYearController::class, 'index'])->name('academic-years.index');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
