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
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\LogBatch;

class ClassroomSubjectController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        Gate::authorize('viewAny', ClassroomSubject::class);

        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $classroomSubjects = QueryBuilder::for($classroom->classroomSubjects()->with('subject'))
            ->through([
            Filter::class,
            Sort::class,
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
        // Authorization: Who can view the details of this classroom subject?
        Gate::authorize('view', $classroomSubject);

        // Muat relasi dari mata pelajaran yang terkait
        // ✅ Tambahkan loadCount agar frontend menerima `summatives_count`
        $classroomSubject->load('subject')
            ->loadCount('summatives');

        return Inertia::render('protected/school-academic-years/classrooms/subjects/show', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
        ]);
    }

    public function create(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        Gate::authorize('create', ClassroomSubject::class);

        if (!ClassroomSubject::canBeCreatedBy($request->user(), $classroom)) {
            abort(403, 'You are not authorized to create classroom subjects in this classroom.');
        }

        $existingSubjectIds = $classroom->classroomSubjects()->pluck('subject_id');

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

    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        Gate::authorize('create', ClassroomSubject::class);

        if (!ClassroomSubject::canBeCreatedBy($request->user(), $classroom)) {
            abort(403, 'You are not authorized to create classroom subjects in this classroom.');
        }

        $validated = $request->validate([
            'subject_id' => [
                'required',
                'ulid',
                Rule::exists('subjects', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
                Rule::unique('classroom_subjects')->where('classroom_id', $classroom->id),
            ],
        ]);

        $classroom->classroomSubjects()->create($validated);

        return redirect()->route('protected.school-academic-years.classrooms.subjects.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Mata pelajaran berhasil ditambahkan ke kelas.');
    }

    public function destroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        Gate::authorize('delete', $classroomSubject);

        if ($classroomSubject->classroom_id !== $classroom->id) {
            abort(403);
        }

        $classroomSubject->delete();

        return redirect()->route('protected.school-academic-years.classrooms.subjects.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Mata pelajaran berhasil dihapus dari kelas.');
    }

    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        Gate::authorize('bulkDelete', ClassroomSubject::class);

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
