<?php

namespace App\Http\Controllers\Protected;

use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\SchoolAcademicYear;
use App\Models\Student;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\LogBatch;

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
            'sort_by' => 'sometimes|string|in:name,nisn',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        // [UBAH] Gunakan QueryBuilder
        $classroomStudents = QueryBuilder::for(
            $classroom->classroomStudents()->with(['student.parent', 'student.guardian'])
        )
            ->through([
                Filter::class, // Akan otomatis memanggil scopeQ() di ClassroomStudent
                Sort::class,   // Akan otomatis memanggil scopeSort() di ClassroomStudent
            ])
            ->paginate();

        return Inertia::render('protected/school-academic-years/classrooms/students/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomStudents' => $classroomStudents,
        ]);
    }

    public function show(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        // Gate::authorize('view', $classroomStudent);

        // Muat semua relasi dari siswa yang terkait
        $classroomStudent->load(['student.parent', 'student.guardian', 'classroom.teacher']);

        return Inertia::render('protected/school-academic-years/classrooms/students/show', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomStudent' => $classroomStudent,
        ]);
    }

    /**
     * Menampilkan form untuk menambahkan siswa ke kelas.
     */
    public function create(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        // Gate::authorize('update', $classroom);

        // 1. Ambil ID siswa yang sudah ada di kelas ini via relasi `classroomStudents`
        $existingStudentIds = $classroom->classroomStudents()->pluck('student_id');

        // 2. Ambil siswa yang ada di tahun ajaran ini, TAPI belum ada di kelas ini
        $availableStudents = $schoolAcademicYear->students()
            ->whereNotIn('id', $existingStudentIds)
            ->orderBy('name')
            ->get();

        return Inertia::render('protected/school-academic-years/classrooms/students/create', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'availableStudents' => $availableStudents,
        ]);
    }

    /**
     * Menyimpan (menautkan) siswa ke dalam kelas.
     */
    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        // Gate::authorize('update', $classroom);

        $validated = $request->validate([
            'student_id' => [
                'required',
                'ulid',
                Rule::exists('students', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
                Rule::unique('classroom_students')->where('classroom_id', $classroom->id),
            ],
        ]);

        // [UBAH] Buat record baru di tabel pivot `classroom_students`
        // menggunakan relasi `classroomStudents()`
        $classroom->classroomStudents()->create([
            'student_id' => $validated['student_id'],
        ]);

        return redirect()->route('protected.school-academic-years.classrooms.students.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    // /**
    //  * Menampilkan form untuk mengedit (mengganti) siswa dalam sebuah kelas.
    //  */
    // public function edit(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    // {
    //     // Gate::authorize('update', $classroom);

    //     // 1. Ambil ID siswa yang sudah ada di kelas ini, KECUALI siswa yang sedang diedit
    //     $existingStudentIds = $classroom->classroomStudents()
    //         ->where('id', '!=', $classroomStudent->id)
    //         ->pluck('student_id');

    //     // 2. Ambil siswa yang ada di tahun ajaran ini, TAPI belum ada di kelas lain
    //     $availableStudents = $schoolAcademicYear->students()
    //         ->whereNotIn('id', $existingStudentIds)
    //         ->orderBy('name')
    //         ->get();

    //     return Inertia::render('protected/school-academic-years/classrooms/students/edit', [
    //         'schoolAcademicYear' => $schoolAcademicYear,
    //         'classroom' => $classroom,
    //         'classroomStudent' => $classroomStudent,
    //         'availableStudents' => $availableStudents,
    //     ]);
    // }

    // /**
    //  * Memperbarui data siswa di dalam kelas.
    //  */
    // public function update(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    // {
    //     // Gate::authorize('update', $classroom);

    //     $validated = $request->validate([
    //         'student_id' => [
    //             'required',
    //             'ulid',
    //             Rule::exists('students', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
    //             // Pastikan siswa yang baru dipilih belum terdaftar di kelas ini, abaikan record saat ini
    //             Rule::unique('classroom_students')->where('classroom_id', $classroom->id)->ignore($classroomStudent->id),
    //         ],
    //     ]);

    //     $classroomStudent->update($validated);

    //     return redirect()->route('protected.school-academic-years.classrooms.students.index', [$schoolAcademicYear, $classroom])
    //         ->with('success', 'Data siswa di kelas berhasil diperbarui.');
    // }

    public function destroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        // Gate::authorize('delete', $classroomStudent);

        $classroomStudent->delete();

        return redirect()->route('protected.school-academic-years.classrooms.students.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Siswa berhasil dikeluarkan dari kelas.');
    }

    /**
     * Mengeluarkan beberapa siswa dari kelas sekaligus.
     *
     * @param Request $request
     * @param SchoolAcademicYear $schoolAcademicYear
     * @param Classroom $classroom
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        // Gate::authorize('bulkDelete', ClassroomStudent::class);

        $request->validate([
            'ids'   => ['required', 'array'],
            // Validasi bahwa ID yang dikirim adalah ID dari tabel classroom_students
            'ids.*' => ['exists:classroom_students,id'],
        ]);

        LogBatch::startBatch();

        DB::transaction(function () use ($request, $classroom) {
            ClassroomStudent::where('classroom_id', $classroom->id)
                ->whereIn('id', $request->input('ids'))
                ->delete();
        });

        LogBatch::endBatch();

        return redirect()->route('protected.school-academic-years.classrooms.students.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Siswa yang dipilih berhasil dikeluarkan dari kelas.');
    }
}
