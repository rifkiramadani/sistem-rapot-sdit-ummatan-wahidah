<?php

namespace App\Http\Controllers\Protected;

use App\Models\SchoolAcademicYear;
use Inertia\Inertia;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Facades\LogBatch;
use App\Support\QueryBuilder; // <-- Import QueryBuilder
use App\QueryFilters\Filter;   // <-- Import Filter pipe
use App\QueryFilters\Sort;     // <-- Import Sort pipe
use App\Enums\PerPageEnum; // <-- Import Enum
use App\Models\School;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', AcademicYear::class);

        // 1. Validasi tetap di controller (kita buat lebih konsisten)
        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name,start,end',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $mainSchoolId = $this->getMainSchool()->id;

        // --- CORRECTED LOGIC ---

        // 1. Prepare the base Eloquent query with all your initial conditions.
        $baseQuery = SchoolAcademicYear::query()
            ->with('academicYear')
            ->where('school_id', $mainSchoolId);

        // 2. Now, pass the fully prepared Eloquent Builder object
        //    to your QueryBuilder.
        $schoolAcademicYears = QueryBuilder::for($baseQuery)
            ->through([
                Filter::class,
                Sort::class,
            ])
            ->paginate();

        // 3. Kembalikan response
        return Inertia::render('protected/academic-years/index', [
            'schoolAcademicYears' => $schoolAcademicYears,
        ]);
    }

    public function create()
    {
        Gate::authorize('create', AcademicYear::class);

        return Inertia::render('protected/academic-years/create');
    }

    public function store(Request $request)
    {

        Gate::authorize('create', AcademicYear::class);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{4}$/', // Aturan validasi baru contoh 2032/2033
                'unique:academic_years,name', // Tambahkan validasi unique
            ],
            'start' => 'required|date',
            'end' => [
                'required',
                'date',
                'after:start',
                function ($attribute, $value, $fail) use ($request) {
                    $startDate = \Carbon\Carbon::parse($request->start);
                    $endDate = \Carbon\Carbon::parse($value);
                    $oneYearLater = $startDate->copy()->addYear();

                    // Check if end date is more than 1 year after start date
                    if ($endDate->greaterThan($oneYearLater)) {
                        $fail('Tanggal selesai tidak boleh lebih dari 1 tahun setelah tanggal mulai.');
                    }
                },
            ],
        ]);

        // Create the academic year
        $academicYear = AcademicYear::create($validated);

        // Get the main school and create the pivot record
        $mainSchool = $this->getMainSchool();

        // Create the school_academic_years pivot record
        SchoolAcademicYear::create([
            'school_id' => $mainSchool->id,
            'academic_year_id' => $academicYear->id,
        ]);

        return redirect()->route('protected.academic-years.index')->with('success', 'Tahun ajaran berhasil dibuat dan dihubungkan dengan sekolah.');
    }

    private function getMainSchool(): School
    {
        // Ambil sekolah pertama, atau buat jika tidak ada (untuk dev/testing)
        // Gunakan findOrNew() atau first()
        return School::first() ?? School::factory()->create();
    }

    public function show(AcademicYear $academicYear)
    {
        Gate::authorize('view', $academicYear);

        $schoolId = $this->getMainSchool()->id;

        $schoolAcademicYear = $academicYear->schoolAcademicYears()
            ->where('school_id', $schoolId)
            ->first();

        return Inertia::render('protected/academic-years/show', [
            'academicYear' => $academicYear,
            'schoolAcademicYear' => $schoolAcademicYear,
        ]);
    }

    public function edit(AcademicYear $academicYear)
    {
        Gate::authorize('update', $academicYear);

        return Inertia::render('protected/academic-years/edit', [
            'academicYear' => $academicYear
        ]);
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        Gate::authorize('update', $academicYear);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{4}$/', // Aturan validasi baru contoh 2032/2033
                Rule::unique('academic_years')->ignore($academicYear->id),
            ],
            'start' => 'required|date',
            'end' => [
                'required',
                'date',
                'after:start',
                function ($attribute, $value, $fail) use ($request) {
                    $startDate = \Carbon\Carbon::parse($request->start);
                    $endDate = \Carbon\Carbon::parse($value);
                    $oneYearLater = $startDate->copy()->addYear();

                    // Check if end date is more than 1 year after start date
                    if ($endDate->greaterThan($oneYearLater)) {
                        $fail('Tanggal selesai tidak boleh lebih dari 1 tahun setelah tanggal mulai.');
                    }
                },
            ],
        ]);

        $academicYear->update($validated);

        return redirect()->route('protected.academic-years.index')->with('success', 'Tahun Ajaran Berhasil diubah.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        Gate::authorize('delete', $academicYear);

        $academicYear->delete();

        return redirect()->route('protected.academic-years.index')->with('success', 'Tahun Ajaran Berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        Gate::authorize('bulkDelete', AcademicYear::class);

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:academic_years,id']
        ]);

        LogBatch::startBatch();

        DB::transaction(function () use ($request) {
            $academicYears = AcademicYear::whereIn('id', $request->input('ids'))->get();

            foreach ($academicYears as $academicYear) {
                $academicYear->delete();
            }
        });

        LogBatch::endBatch();

        return redirect()->route('protected.academic-years.index')->with('success', 'Data Tahun Ajaran yang dipilih berhasil dihapus.');
    }
}