<?php

namespace App\Http\Controllers\Protected;

use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\SchoolAcademicYear;
use App\Models\Student;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClassroomStudentController extends Controller
{
    /**
     * Menampilkan daftar siswa dalam sebuah kelas.
     */
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        // Gate::authorize('view', $classroom);

        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name,nisn', // Kolom sort berasal dari tabel students
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        // Ambil ID semua siswa yang terdaftar di kelas ini
        $studentIds = $classroom->classroomStudents()->pluck('student_id');

        // Buat query langsung ke model Student, difilter berdasarkan ID yang didapat
        // Ini memungkinkan kita untuk menggunakan kembali scopeQ dan Sort pada model Student
        $studentsQuery = Student::whereIn('id', $studentIds)->with(['parent', 'guardian']);

        $students = QueryBuilder::for($studentsQuery)
            ->through([
                Filter::class,
                Sort::class,
            ])
            ->paginate();

        return Inertia::render('protected/school-academic-years/classrooms/students/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'students' => $students, // Data yang dikirim adalah paginasi dari model Student
        ]);
    }
}
