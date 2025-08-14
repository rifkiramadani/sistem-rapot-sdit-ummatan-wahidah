<?php

namespace App\Http\Controllers\Protected;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Database\Eloquent\Builder; // Import the Builder class
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        // 1. Validasi semua parameter request
        $request->validate([
            'per_page' => 'sometimes|integer|in:10,20,30,40,50,100',
            'sort_by' => 'sometimes|string|in:name,npsn',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            // ++ Tambahkan validasi untuk filter 'q'
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        // 2. Ambil parameter dengan nilai default
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        // ++ Ambil nilai filter 'q' dari request
        $searchQuery = $request->input('filter.q');

        // 3. Bangun query dasar
        $query = School::with(['principal', 'currentAcademicYear']);

        // ++ 4. Terapkan filtering (pencarian) jika ada searchQuery
        $query->when($searchQuery, function (Builder $query, string $search) {
            $searchLower = strtolower($search);

            $query->where(function (Builder $q) use ($searchLower) {
                // ++ Gunakan whereRaw dengan LOWER() untuk pencarian case-insensitive
                // Ini akan cocok dengan 'sekolah', 'Sekolah', atau 'SEKOLAH'
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhere('npsn', 'like', "%{$searchLower}%"); // NPSN biasanya angka, tapi ini untuk konsistensi
            });
        });

        // 5. Terapkan sorting ke query
        $query->orderBy($sortBy, $sortDirection);

        // 6. Lakukan paginasi dan tambahkan semua parameter query string ke link paginasi
        $schools = $query->paginate($perPage)->withQueryString();

        return Inertia::render('protected/schools/index', [
            'schools' => $schools,
        ]);
    }
}
