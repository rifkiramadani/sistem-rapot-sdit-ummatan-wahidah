<?php

namespace App\Http\Controllers\Protected;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolAcademicYearController extends Controller
{
    public function index(Request $request, School $school)
    {
        // 1. Validasi semua parameter request
        $request->validate([
            'per_page' => 'sometimes|integer|in:10,20,30,40,50,100',
            // Sesuaikan kolom yang bisa di-sort
            'sort_by' => 'sometimes|string|in:name,start,end',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        // 2. Ambil parameter dengan nilai default
        $perPage = $request->input('per_page', 10);
        // Default sort adalah 'start' (tanggal mulai), dari yang terbaru
        $sortBy = $request->input('sort_by', 'start');
        $sortDirection = $request->input('sort_direction', 'desc');
        $searchQuery = $request->input('filter.q');

        // 3. Bangun query dasar dari relasi
        $query = $school->schoolAcademicYears()
            ->with('academicYear');

        // 4. Terapkan filtering (pencarian) jika ada searchQuery
        $query->when($searchQuery, function ($query, $search) {
            $searchLower = strtolower($search);
            // Gunakan whereHas untuk filter pada relasi 'academicYear'
            $query->whereHas('academicYear', function ($subQuery) use ($searchLower) {
                // Gunakan whereRaw untuk pencarian case-insensitive pada nama tahun ajaran
                $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
            });
        });

        // 5. Terapkan sorting pada kolom dari tabel relasi
        // Kita perlu JOIN untuk bisa mengurutkan berdasarkan kolom di tabel academic_years
        $query->join('academic_years', 'school_academic_years.academic_year_id', '=', 'academic_years.id')
            ->orderBy('academic_years.' . $sortBy, $sortDirection)
            // Penting: Pilih kolom dari tabel asli untuk menghindari konflik
            ->select('school_academic_years.*');


        // 6. Lakukan paginasi dan tambahkan semua parameter query string
        $schoolAcademicYears = $query->paginate($perPage)->withQueryString();

        return Inertia::render('protected/schools/academic-years/index', [
            'school' => $school,
            'schoolAcademicYears' => $schoolAcademicYears,
        ]);
    }
}
