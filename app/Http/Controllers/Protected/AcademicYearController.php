<?php

namespace App\Http\Controllers\Protected;

use Inertia\Inertia;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;

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
            'start' => 'required|integer',
            'end' => 'required|integer'
        ]);

        AcademicYear::create($validated);

        return redirect()->route('protected.academic-years.index')->with('success', 'Tahun ajaran berhasil dibuat.');
    }
}
