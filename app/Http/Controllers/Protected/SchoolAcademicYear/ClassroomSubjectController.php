<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\SchoolAcademicYear;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\LogBatch;

class ClassroomSubjectController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $classroomSubjects = QueryBuilder::for($classroom->classroomSubjects()->with('subject'))
            ->through([
                Filter::class, // Akan memanggil scopeQ() di ClassroomSubject
                Sort::class,   // Akan memanggil scopeSort() di ClassroomSubject
            ])
            ->paginate();

        return Inertia::render('protected/school-academic-years/classrooms/subjects/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubjects' => $classroomSubjects,
        ]);
    }

    public function show(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        // Gate::authorize('view', $classroomSubject);

        // Muat relasi dari mata pelajaran yang terkait
        $classroomSubject->load('subject');

        return Inertia::render('protected/school-academic-years/classrooms/subjects/show', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
        ]);
    }

    /**
     * Menampilkan form untuk menambahkan mata pelajaran ke kelas.
     */
    public function create(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        // 1. Ambil ID mata pelajaran yang sudah ada di kelas ini
        $existingSubjectIds = $classroom->classroomSubjects()->pluck('subject_id');

        // 2. Ambil mapel yang ada di tahun ajaran ini, TAPI belum ada di kelas ini
        $availableSubjects = $schoolAcademicYear->subjects()
            ->whereNotIn('id', $existingSubjectIds)
            ->orderBy('name')
            ->get();

        return Inertia::render('protected/school-academic-years/classrooms/subjects/create', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'availableSubjects' => $availableSubjects,
        ]);
    }

    /**
     * Menyimpan (menautkan) mata pelajaran ke dalam kelas.
     */
    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        $validated = $request->validate([
            'subject_id' => [
                'required',
                'ulid',
                Rule::exists('subjects', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
                Rule::unique('classroom_subjects')->where('classroom_id', $classroom->id),
            ],
        ]);

        $classroom->classroomSubjects()->create($validated);

        // Redirect ke halaman index mata pelajaran di kelas tersebut
        return redirect()->route('protected.school-academic-years.classrooms.subjects.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Mata pelajaran berhasil ditambahkan ke kelas.');
    }

    // /**
    //  * Menampilkan form untuk mengedit (mengganti) mata pelajaran di kelas.
    //  */
    // public function edit(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    // {
    //     $existingSubjectIds = $classroom->classroomSubjects()
    //         ->where('id', '!=', $classroomSubject->id)
    //         ->pluck('subject_id');

    //     $availableSubjects = $schoolAcademicYear->subjects()
    //         ->whereNotIn('id', $existingSubjectIds)
    //         ->orderBy('name')
    //         ->get();

    //     return Inertia::render('protected/school-academic-years/classrooms/subjects/edit', [
    //         'schoolAcademicYear' => $schoolAcademicYear,
    //         'classroom' => $classroom,
    //         'classroomSubject' => $classroomSubject,
    //         'availableSubjects' => $availableSubjects,
    //     ]);
    // }

    // /**
    //  * Memperbarui tautan mata pelajaran di kelas.
    //  */
    // public function update(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    // {
    //     $validated = $request->validate([
    //         'subject_id' => [
    //             'required',
    //             'ulid',
    //             Rule::exists('subjects', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
    //             Rule::unique('classroom_subjects')->where('classroom_id', $classroom->id)->ignore($classroomSubject->id),
    //         ],
    //     ]);

    //     $classroomSubject->update($validated);

    //     return redirect()->route('protected.school-academic-years.classrooms.subjects.index', [$schoolAcademicYear, $classroom])
    //         ->with('success', 'Mata pelajaran berhasil diperbarui.');
    // }

    /**
     * Menghapus (melepas tautan) mata pelajaran dari kelas.
     */
    public function destroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        // Gate::authorize('delete', $classroomSubject);

        // Keamanan: pastikan record yang akan dihapus benar-benar milik kelas ini
        if ($classroomSubject->classroom_id !== $classroom->id) {
            abort(403);
        }

        $classroomSubject->delete();

        return redirect()->route('protected.school-academic-years.classrooms.subjects.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Mata pelajaran berhasil dihapus dari kelas.');
    }

    /**
     * Menghapus beberapa mata pelajaran dari kelas sekaligus.
     */
    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        // Gate::authorize('bulkDelete', ClassroomSubject::class);

        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:classroom_subjects,id'],
        ]);

        LogBatch::startBatch();

        DB::transaction(function () use ($request, $classroom) {
            ClassroomSubject::where('classroom_id', $classroom->id)
                ->whereIn('id', $request->input('ids'))
                ->get()
                ->each->delete();
        });

        LogBatch::endBatch();

        return redirect()->route('protected.school-academic-years.classrooms.subjects.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Mata pelajaran yang dipilih berhasil dihapus dari kelas.');
    }
}
