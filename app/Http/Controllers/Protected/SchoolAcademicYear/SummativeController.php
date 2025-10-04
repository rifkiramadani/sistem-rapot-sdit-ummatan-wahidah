<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Enums\DefaultSummativeTypeEnum;
use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\SchoolAcademicYear;
use App\Models\Summative;
use App\Models\SummativeType;
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

        $query = $classroomSubject->summatives()
            ->with('summativeType')
            ->orderBy('created_at', 'asc');

        $summatives = QueryBuilder::for($query)
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
        $summativeTypes = $schoolAcademicYear->summativeTypes()->orderBy('name')->get();

        return Inertia::render('protected/school-academic-years/classrooms/subjects/summatives/create', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
            'summativeTypes' => $summativeTypes,
        ]);
    }

    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        $validated = $this->validateSummative($request, $schoolAcademicYear);

        $classroomSubject->summatives()->create($validated);

        return redirect()->route('protected.school-academic-years.classrooms.subjects.summatives.index', [$schoolAcademicYear, $classroom, $classroomSubject])
            ->with('success', 'Sumatif berhasil dibuat.');
    }

    public function edit(SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject, Summative $summative)
    {
        if ($summative->classroom_subject_id !== $classroomSubject->id) {
            abort(403);
        }

        $classroomSubject->load('subject');
        $summativeTypes = $schoolAcademicYear->summativeTypes()->orderBy('name')->get();

        return Inertia::render('protected/school-academic-years/classrooms/subjects/summatives/edit', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
            'summativeTypes' => $summativeTypes,
            'summative' => $summative,
        ]);
    }

    public function update(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject, Summative $summative)
    {
        if ($summative->classroom_subject_id !== $classroomSubject->id) {
            abort(403);
        }

        $validated = $this->validateSummative($request, $schoolAcademicYear);

        $summative->update($validated);

        return redirect()->route('protected.school-academic-years.classrooms.subjects.summatives.index', [$schoolAcademicYear, $classroom, $classroomSubject])
            ->with('success', 'Sumatif berhasil diperbarui.');
    }

    public function values(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name,identifier',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $classroomSubject->load('subject');

        $summatives = QueryBuilder::for($classroomSubject->summatives()->with('summativeType'))
            ->through([
                Filter::class,
                Sort::class,
            ])
            ->paginate();

        return Inertia::render('protected/school-academic-years/classrooms/subjects/summatives/values', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
            'summatives' => $summatives,
        ]);
    }


    public function destroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject, Summative $summative)
    {
        if ($summative->classroom_subject_id !== $classroomSubject->id) {
            abort(403);
        }

        $summative->delete();

        return redirect()->route('protected.school-academic-years.classrooms.subjects.summatives.index', [$schoolAcademicYear, $classroom, $classroomSubject])
            ->with('success', 'Data sumatif berhasil dihapus.');
    }


    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
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

    /**
     * Method privat untuk menampung aturan validasi sumatif.
     */
    private function validateSummative(Request $request, SchoolAcademicYear $schoolAcademicYear): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'summative_type_id' => [
                'required',
                'ulid',
                Rule::exists('summative_types', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
            ],
            'identifier' => [
                Rule::requiredIf(function () use ($request) {
                    $type = SummativeType::find($request->input('summative_type_id'));
                    return $type && $type->name === DefaultSummativeTypeEnum::MATERI->value;
                }),
                'nullable',
                'string',
                'max:255',
            ],
            'prominent' => [
                Rule::requiredIf(function () use ($request) {
                    $type = SummativeType::find($request->input('summative_type_id'));
                    return $type && $type->name === DefaultSummativeTypeEnum::MATERI->value;
                }),
                'nullable',
                'string',
            ],
            'improvement' => [
                Rule::requiredIf(function () use ($request) {
                    $type = SummativeType::find($request->input('summative_type_id'));
                    return $type && $type->name === DefaultSummativeTypeEnum::MATERI->value;
                }),
                'nullable',
                'string',
            ],
        ]);
    }
}