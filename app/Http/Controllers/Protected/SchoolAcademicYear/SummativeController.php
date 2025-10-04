<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Enums\DefaultSummativeTypeEnum;
use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\SchoolAcademicYear;
use App\Models\StudentSummative;
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
        $classroomSubject->load('subject');

        // 1. Ambil semua siswa di kelas ini
        $students = $classroom->classroomStudents()->with('student')->get()->pluck('student');

        // 2. Ambil semua sumatif untuk mata pelajaran ini, diurutkan
        $summatives = $classroomSubject->summatives()->with('summativeType')->orderBy('created_at', 'asc')->get();

        // 3. Ambil semua nilai siswa yang relevan dalam satu query
        $studentIds = $students->pluck('id');
        $summativeIds = $summatives->pluck('id');

        $scores = StudentSummative::whereIn('student_id', $studentIds)
            ->whereIn('summative_id', $summativeIds)
            ->get()
            ->keyBy(function ($item) {
                return $item->student_id . '-' . $item->summative_id;
            });

        // 4. Transformasi data menjadi struktur yang diinginkan frontend
        $studentData = $students->map(function ($student) use ($summatives, $scores) {
            $studentSummatives = [];
            $allScores = []; // Untuk menghitung Nilai Rapor (NR)

            // Kelompokkan sumatif berdasarkan jenisnya
            $summativesByType = $summatives->groupBy('summativeType.name');

            foreach ($summativesByType as $typeName => $typeSummatives) {
                $values = $typeSummatives->map(function ($summative) use ($student, $scores) {
                    $score = $scores->get($student->id . '-' . $summative->id);
                    return [
                        'id' => $summative->id,
                        'name' => $summative->name,
                        'identifier' => $summative->identifier,
                        'score' => $score ? (int) $score->value : null,
                    ];
                });

                $validScores = $values->pluck('score')->filter(fn($s) => !is_null($s));
                $mean = $validScores->avg() ?? 0;
                $allScores[] = $mean;

                $studentSummatives[$typeName] = [
                    'values' => $values,
                    'mean' => round($mean, 1),
                ];
            }

            // Menentukan deskripsi
            $materiScores = collect($studentSummatives[DefaultSummativeTypeEnum::MATERI->value]['values'] ?? []);
            $highestScore = $materiScores->whereNotNull('score')->sortByDesc('score')->first();
            $lowestScore = $materiScores->whereNotNull('score')->sortBy('score')->first();

            $highestSummative = $highestScore ? $summatives->find($highestScore['id']) : null;
            $lowestSummative = $lowestScore ? $summatives->find($lowestScore['id']) : null;

            return [
                'id' => $student->id,
                'nisn' => $student->nisn,
                'nomorInduk' => $student->id_number,
                'name' => $student->name,
                'nr' => round(collect($allScores)->avg() ?? 0),
                'summatives' => $studentSummatives,
                'description' => [
                    'Materi Unggul' => $highestSummative->name ?? null,
                    'Materi Kurang' => $lowestSummative->name ?? null,
                    'Materi Paling Menonjol' => $highestSummative->prominent ?? null,
                    'Materi Yang Perlu Ditingkatkan' => $lowestSummative->improvement ?? null,
                ],
            ];
        });

        return Inertia::render('protected/school-academic-years/classrooms/subjects/summatives/values', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
            'studentSummativeValues' => $studentData, // <-- Kirim data yang sudah di-transformasi
        ]);
    }

    public function updateValue(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        // 1. Validasi data yang masuk
        $validated = $request->validate([
            'student_id' => ['required', 'ulid', Rule::exists('students', 'id')],
            'summative_id' => [
                'required',
                'ulid',
                // Pastikan summative_id yang dikirim benar-benar milik classroomSubject ini
                Rule::exists('summatives', 'id')->where('classroom_subject_id', $classroomSubject->id)
            ],
            'value' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        // 2. Gunakan updateOrCreate untuk efisiensi
        // Mencari berdasarkan student_id dan summative_id
        // Memperbarui atau membuat dengan 'value' yang baru
        $studentSummative = StudentSummative::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'summative_id' => $validated['summative_id'],
            ],
            [
                'value' => $validated['value'],
            ]
        );

        // 3. Kirim respons JSON yang berhasil
        return response()->json([
            'message' => 'Nilai berhasil disimpan.',
            'data' => $studentSummative,
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