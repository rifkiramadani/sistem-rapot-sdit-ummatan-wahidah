<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomSubject; // <-- Import
use App\Models\SchoolAcademicYear;
use App\Models\Summative;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\LogBatch;

class SummativeController extends Controller
{
    // [UBAH] Ganti type-hint parameter terakhir ke ClassroomSubject
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name,identifier',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $classroomSubject->load('subject');

        // Sekarang, query summatives dari model Subject yang benar
        $summatives = QueryBuilder::for($classroomSubject->summatives()->with('summativeType'))
            ->through([
                Filter::class,
                Sort::class,
            ])
            ->paginate();

        return Inertia::render('protected/school-academic-years/classrooms/subjects/summatives/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
            'summatives' => $summatives,
        ]);
    }

    public function create(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        $classroomSubject->load('subject');
        // Ambil semua jenis sumatif yang tersedia di tahun ajaran ini
        $summativeTypes = $schoolAcademicYear->summativeTypes()->orderBy('name')->get();

        return Inertia::render('protected/school-academic-years/classrooms/subjects/summatives/create', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
            'summativeTypes' => $summativeTypes,
        ]);
    }

    /**
     * Menyimpan sumatif baru ke database.
     */
    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'identifier' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'summative_type_id' => [
                'required',
                'ulid',
                Rule::exists('summative_types', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
            ],
        ]);

        // Buat sumatif baru yang berelasi dengan mata pelajaran ini
        $classroomSubject->summatives()->create($validated);

        return redirect()->route('protected.school-academic-years.classrooms.subjects.summatives.index', [$schoolAcademicYear, $classroom, $classroomSubject])
            ->with('success', 'Sumatif berhasil dibuat.');
    }

    public function destroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject, Summative $summative)
    {
        // Gate::authorize('delete', $summative);

        // Keamanan: pastikan sumatif yang akan dihapus milik classroomSubject yang benar
        if ($summative->classroom_subject_id !== $classroomSubject->id) {
            abort(403);
        }

        $summative->delete();

        return redirect()->route('protected.school-academic-years.classrooms.subjects.summatives.index', [$schoolAcademicYear, $classroom, $classroomSubject])
            ->with('success', 'Data sumatif berhasil dihapus.');
    }

    /**
     * Menghapus beberapa data sumatif sekaligus.
     */
    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        // Gate::authorize('bulkDelete', Summative::class);

        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:summatives,id'],
        ]);

        LogBatch::startBatch();

        DB::transaction(function () use ($request, $classroomSubject) {
            Summative::where('classroom_subject_id', $classroomSubject->id)
                ->whereIn('id', $request->input('ids'))
                ->get()
                ->each->delete();
        });

        LogBatch::endBatch();

        return redirect()->route('protected.school-academic-years.classrooms.subjects.summatives.index', [$schoolAcademicYear, $classroom, $classroomSubject])
            ->with('success', 'Data sumatif yang dipilih berhasil dihapus.');
    }
}