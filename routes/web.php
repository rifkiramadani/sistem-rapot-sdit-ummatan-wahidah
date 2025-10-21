<?php

use Inertia\Inertia;
use App\Models\AcademicYear;
use App\Models\SchoolAcademicYear;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Protected\SchoolController;
use App\Http\Controllers\Protected\SchoolAcademicYear\StudentController;
use App\Http\Controllers\Protected\SubjectController;
use App\Http\Controllers\Protected\SchoolAcademicYear\TeacherController;
use App\Http\Controllers\Protected\SchoolAcademicYear\ClassroomController;
use App\Http\Controllers\Protected\AcademicYearController;
use App\Http\Controllers\Protected\SchoolAcademicYear\ClassroomStudentController;
use App\Http\Controllers\Protected\SchoolAcademicYear\ClassroomSubjectController;
use App\Http\Controllers\Protected\SchoolAcademicYearController;
use App\Http\Controllers\Protected\SchoolAcademicYear\SummativeController;
use App\Http\Controllers\Protected\DashboardController;

Route::get('/', function () {
    return Inertia::render('welcome');
    // return redirect('/login');
})->name('home');

Route::prefix('protected')->name('protected.')->middleware(['auth'])->group(function () {
    Route::get('', [DashboardController::class, 'index'])->name('dashboard.index');

    // ROUTE FOR ACADEMIC YEAR
    Route::prefix('academic-years')->name('academic-years.')->group(function () {
        Route::get('', [AcademicYearController::class, 'index'])->name('index');
        Route::get('/create', [AcademicYearController::class, 'create'])->name('create');
        Route::post('', [AcademicYearController::class, 'store'])->name('store');
        Route::get('/{academicYear}', [AcademicYearController::class, 'show'])->name('show');
        Route::get('/{academicYear}/edit', [AcademicYearController::class, 'edit'])->name('edit');
        Route::put('/{academicYear}', [AcademicYearController::class, 'update'])->name('update');
        Route::delete('/{academicYear}', [AcademicYearController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-destroy', [AcademicYearController::class, 'bulkDestroy'])->name('bulk-destroy');
    });

    Route::prefix('schools')->name('schools.')->group(function () {
        // [BARU] Rute untuk School Detail Page yang baru
        Route::get('/detail', [SchoolController::class, 'showMainSchool'])->name('detail');
        Route::put('/detail', [SchoolController::class, 'updateMainSchool'])->name('update.detail');

        // DEPRECATED
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
            // Route::get('/{schoolAcademicYear}/edit', [SchoolAcademicYearController::class, 'edit'])->name('edit');
            // Route::put('/{schoolAcademicYear}', [SchoolAcademicYearController::class, 'update'])->name('update');
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

        // ROUTE FOR PROFILE SETTINGS
        Route::prefix('/settings')->name('settings.')->group(function () {
            Route::get('/profile', function (SchoolAcademicYear $schoolAcademicYear) {
                return Inertia::render('settings/profile', [
                    'schoolAcademicYear' => $schoolAcademicYear
                ]);
            })->name('profile.edit');
        });

        Route::prefix('/teachers')->name('teachers.')->group(function () {
            Route::get('', [TeacherController::class, 'index'])->name('index');
            Route::get('/create', [TeacherController::class, 'create'])->name('create');
            Route::post('', [TeacherController::class, 'store'])->name('store');
            Route::get('/{teacher}', [TeacherController::class, 'show'])->name('show');
            Route::get('/{teacher}/edit', [TeacherController::class, 'edit'])->name('edit');
            Route::put('/{teacher}', [TeacherController::class, 'update'])->name('update');
            Route::delete('/{teacher}', [TeacherController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-destroy', [TeacherController::class, 'bulkDestroy'])->name('bulk-destroy');
        });

        // ROUTE FOR SCHOOL ACADEMIC YEAR SUBJECTS
        Route::prefix('/subjects')->name('subjects.')->group(function () {
            Route::get('/', [SubjectController::class, 'index'])->name('index');
            Route::get('/create', [SubjectController::class, 'create'])->name('create');
            Route::post('/', [SubjectController::class, 'store'])->name('store');
            Route::get('/{subject}', [SubjectController::class, 'show'])->name('show');
            Route::get('/{subject}/edit', [SubjectController::class, 'edit'])->name('edit');
            Route::put('/{subject}', [SubjectController::class, 'update'])->name('update');
            Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-destroy', [SubjectController::class, 'bulkDestroy'])->name('bulk-destroy');
        });

        Route::prefix('/classrooms')->name('classrooms.')->group(function () {
            Route::get('', [ClassroomController::class, 'index'])->name('index');
            Route::get('/create', [ClassroomController::class, 'create'])->name('create');
            Route::post('', [ClassroomController::class, 'store'])->name('store');
            Route::get('/{classroom}', [ClassroomController::class, 'show'])->name('show');
            Route::get('/{classroom}/edit', [ClassroomController::class, 'edit'])->name('edit');
            Route::put('/{classroom}', [ClassroomController::class, 'update'])->name('update');
            Route::delete('/{classroom}', [ClassroomController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-destroy', [ClassroomController::class, 'bulkDestroy'])->name('bulk-destroy');

            Route::prefix('/{classroom}/students')->name('students.')->group(function () {
                Route::get('', [ClassroomStudentController::class, 'index'])->name('index');
                Route::get('/create', [ClassroomStudentController::class, 'create'])->name('create');
                Route::post('', [ClassroomStudentController::class, 'store'])->name('store');
                Route::get('/{classroomStudent}', [ClassroomStudentController::class, 'show'])->name('show');
                // Route::get('/{classroomStudent}/edit', [ClassroomStudentController::class, 'edit'])->name('edit');
                // Route::put('/{classroomStudent}', [ClassroomStudentController::class, 'update'])->name('update');
                Route::delete('/{classroomStudent}', [ClassroomStudentController::class, 'destroy'])->name('destroy');
                Route::post('/bulk-destroy', [ClassroomStudentController::class, 'bulkDestroy'])->name('bulk-destroy');
                Route::get('/{classroomStudent}/summatives', [ClassroomStudentController::class, 'summatives'])->name('summatives');
                Route::get('/{classroomStudent}/summatives/export-word', [ClassroomStudentController::class, 'exportSummativesWord'])->name('summatives.export-word');
                Route::get('/{classroomStudent}/export-cover', [ClassroomStudentController::class, 'exportReportCover'])->name('export-cover');
                Route::get('/{classroomStudent}/export-transfer-certificate', [ClassroomStudentController::class, 'exportTransferCertificate'])->name('export-transfer-certificate');
                Route::get('/{classroomStudent}/export-report-card', [ClassroomStudentController::class, 'exportReportCard'])->name('export-report-card');
                Route::get('/{classroomStudent}/export-sts', [ClassroomStudentController::class, 'exportSts'])->name('export-sts');
            });

            Route::prefix('/{classroom}/subjects')->name('subjects.')->group(function () {
                Route::get('', [ClassroomSubjectController::class, 'index'])->name('index');
                Route::get('/create', [ClassroomSubjectController::class, 'create'])->name('create');
                Route::post('', [ClassroomSubjectController::class, 'store'])->name('store');
                Route::get('/{classroomSubject}', [ClassroomSubjectController::class, 'show'])->name('show');
                // Route::get('/{classroomSubject}/edit', [ClassroomSubjectController::class, 'edit'])->name('edit');
                // Route::put('/{classroomSubject}', [ClassroomSubjectController::class, 'update'])->name('update');
                Route::delete('/{classroomSubject}', [ClassroomSubjectController::class, 'destroy'])->name('destroy');
                Route::post('/bulk-destroy', [ClassroomSubjectController::class, 'bulkDestroy'])->name('bulk-destroy');

                Route::prefix('/{classroomSubject}/summatives')->name('summatives.')->group(function () {
                    Route::get('', [SummativeController::class, 'index'])->name('index');
                    Route::get('/create', [SummativeController::class, 'create'])->name('create');
                    Route::post('', [SummativeController::class, 'store'])->name('store');
                    // Route::get('/{summative}', [SummativeController::class, 'show'])->name('show');
                    Route::get('/{summative}/edit', [SummativeController::class, 'edit'])->name('edit');
                    Route::put('/{summative}', [SummativeController::class, 'update'])->name('update');
                    Route::get('/values', [SummativeController::class, 'values'])->name('values');
                    Route::post('update-value', [SummativeController::class, 'updateValue'])
                        ->name('update-value');
                    Route::get('/export-word', [SummativeController::class, 'exportWord'])->name('export-word');
                });
            });
        });

        Route::prefix('/students')->name('students.')->group(function () {
            Route::get('', [StudentController::class, 'index'])->name('index');
            Route::get('/create', [StudentController::class, 'create'])->name('create');
            Route::post('', [StudentController::class, 'store'])->name('store');
            Route::get('/{student}', [StudentController::class, 'show'])->name('show');
            Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit');
            Route::put('/{student}', [StudentController::class, 'update'])->name('update');
            Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-destroy', [StudentController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::get('/{student}/subjects', [StudentController::class, 'subjects'])->name('subjects');
            Route::get('/{student}/subjects/{subject}', [StudentController::class, 'subjectDetail'])->name('subjects.detail');
        });
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
