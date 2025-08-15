<?php

use App\Http\Controllers\Protected\SchoolAcademicYearController;
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

    Route::prefix('schools')->name('schools.')->group(function () {
        // Rute untuk menampilkan semua sekolah (Read)
        Route::get('', [SchoolController::class, 'index'])->name('index');

        // Rute untuk menampilkan form tambah (Create)
        Route::get('/create', [SchoolController::class, 'create'])->name('create');

        // Rute untuk menyimpan data baru (Create)
        Route::post('', [SchoolController::class, 'store'])->name('store');

        Route::get('/{school}', [SchoolController::class, 'show'])->name('show');

        // Rute untuk menampilkan form edit (Update)
        Route::get('/{school}/edit', [SchoolController::class, 'edit'])->name('edit');

        // Rute untuk memperbarui data yang ada (Update)
        Route::put('/{school}', [SchoolController::class, 'update'])->name('update');

        // Rute untuk menghapus data (Delete)
        Route::delete('/{school}', [SchoolController::class, 'destroy'])->name('destroy');


        Route::prefix('/{school}/academic-years')->name('academic-years.')->group(function () {
            // Rute untuk menampilkan semua sekolah (Read)
            Route::get('', [SchoolAcademicYearController::class, 'index'])->name('index');

            Route::get('/create', [SchoolAcademicYearController::class, 'create'])->name('create');

            // Rute untuk menyimpan data baru (Create)
            Route::post('', [SchoolAcademicYearController::class, 'store'])->name('store');

            Route::get('/{schoolAcademicYear}', [SchoolAcademicYearController::class, 'show'])->name('show');


            Route::get('/{schoolAcademicYear}/edit', [SchoolAcademicYearController::class, 'edit'])->name('edit');

            // Rute untuk memperbarui data
            Route::put('/{schoolAcademicYear}', [SchoolAcademicYearController::class, 'update'])->name('update');

            Route::delete('/{schoolAcademicYear}', [SchoolAcademicYearController::class, 'destroy'])->name('destroy');
        });
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';