<?php

namespace App\Http\Controllers\Protected;

use Inertia\Inertia;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Facades\LogBatch;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {

        Gate::authorize('viewAny', AcademicYear::class);

        // 1. Validasi request
        $request->validate([
            'per_page' => 'sometimes|integer|in:10,20,30,40,50,100',
            // sort_by sekarang sesuai field yang ada di tabel academic_years
            'sort_by' => 'sometimes|string|in:name,start,end',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        // 2. Ambil parameter dengan default
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'start'); // default urut dari start date
        $sortDirection = $request->input('sort_direction', 'asc');
        $searchQuery = $request->input('filter.q');

        // 3. Bangun query dasar
        $query = AcademicYear::with(['schools', 'schoolAcademicYears']);

        // 4. Filter pencarian
        $query->when($searchQuery, function (Builder $query, string $search) {
            $searchLower = strtolower($search);

            $query->where(function (Builder $q) use ($searchLower) {
                // Ganti 'start' dan 'end' dengan '"start"' dan '"end"'
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('CAST("start" AS CHAR) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('CAST("end" AS CHAR) LIKE ?', ["%{$searchLower}%"]);
            });
        });

        // 5. Sorting
        $query->orderBy($sortBy, $sortDirection);

        // 6. Pagination
        $academicYears = $query->paginate($perPage)->withQueryString();

        // dd($academicYears);

        return Inertia::render('protected/academic-years/index', [
            'academicYears' => $academicYears,
        ]);
    }

    public function create()
    {
        Gate::authorize('create', AcademicYear::class);

        return Inertia::render('protected/academic-years/create');
    }

    public function store(Request $request)
    {
        // dd($request->all());

        Gate::authorize('create', AcademicYear::class);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'regex:/^\d{4}\/\d{4}$/', // Aturan validasi baru contoh 2032/2033
                'unique:academic_years,name', // Tambahkan validasi unique
            ],
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        AcademicYear::create($validated);

        return redirect()->route('protected.academic-years.index')->with('success', 'Tahun ajaran berhasil dibuat.');
    }

    public function show(AcademicYear $academicYear)
    {
        Gate::authorize('view', $academicYear);

        return Inertia::render('protected/academic-years/show', [
            'academicYear' => $academicYear,
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
            'end' => 'required|date'
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
