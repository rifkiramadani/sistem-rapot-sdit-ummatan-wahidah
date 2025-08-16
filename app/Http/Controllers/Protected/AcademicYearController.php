<?php

namespace App\Http\Controllers\Protected;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
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
                // Cari berdasarkan name atau format tahun
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('CAST(start AS CHAR) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('CAST(end AS CHAR) LIKE ?', ["%{$searchLower}%"]);
            });
        });

        // 5. Sorting
        $query->orderBy($sortBy, $sortDirection);

        // 6. Pagination
        $academicYears = $query->paginate($perPage)->withQueryString();

        return Inertia::render('protected/academic-years/index', [
            'academicYears' => $academicYears,
        ]);
    }
}
