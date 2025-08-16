<?php

use App\Http\Controllers\Protected\SchoolAcademicYearController;
use App\Http\Controllers\Protected\SchoolController;
use App\Http\Controllers\Protected\TeacherController;
use App\Models\SchoolAcademicYear;
use Inertia\Inertia;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Protected\AcademicYearController;

Route::get('/', function () {
    return Inertia::render('welcome');
    // return redirect('/login');
})->name('home');

Route::prefix('protected')->name('protected.')->middleware(['auth'])->group(function () {
    Route::get('', function () {
        return Inertia::render('protected/dashboard/index');
    })->name('dashboard.index');

    Route::prefix('schools')->name('schools.')->group(function () {
        Route::get('', [SchoolController::class, 'index'])->name('index');
        Route::get('/create', [SchoolController::class, 'create'])->name('create');
        Route::post('', [SchoolController::class, 'store'])->name('store');
        Route::get('/{school}', [SchoolController::class, 'show'])->name('show');
        Route::get('/{school}/edit', [SchoolController::class, 'edit'])->name('edit');
        Route::put('/{school}', [SchoolController::class, 'update'])->name('update');
        Route::delete('/{school}', [SchoolController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-destroy', [SchoolController::class, 'bulkDestroy'])->name('bulk-destroy');

        Route::prefix('/{school}/academic-years')->name('academic-years.')->group(function () {
            Route::get('', [SchoolAcademicYearController::class, 'index'])->name('index');
            Route::get('/create', [SchoolAcademicYearController::class, 'create'])->name('create');
            Route::post('', [SchoolAcademicYearController::class, 'store'])->name('store');
            Route::get('/{schoolAcademicYear}', [SchoolAcademicYearController::class, 'show'])->name('show');
            Route::get('/{schoolAcademicYear}/edit', [SchoolAcademicYearController::class, 'edit'])->name('edit');
            Route::put('/{schoolAcademicYear}', [SchoolAcademicYearController::class, 'update'])->name('update');
            Route::delete('/{schoolAcademicYear}', [SchoolAcademicYearController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-destroy', [SchoolAcademicYearController::class, 'bulkDestroy'])->name('bulk-destroy');
        });
    });

    Route::prefix('{schoolAcademicYear}')->name('school-academic-years.')->group(function () {
        Route::get('', function (SchoolAcademicYear $schoolAcademicYear) {
            return Inertia::render('protected/school-academic-years/dashboard/index', [
                'schoolAcademicYear' => $schoolAcademicYear
            ]);
        })->name('dashboard.index');

        Route::prefix('/teachers')->name('teachers.')->group(function () {
            Route::get('', [TeacherController::class, 'index'])->name('index');
        });
    });

    // ROUTE FOR ACADEMIC YEAR
    Route::prefix('academic-years')->group(function () {
        Route::get('', [AcademicYearController::class, 'index'])->name('academic-years.index');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
