<?php

namespace App\Http\Controllers\Protected;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolAcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

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

    /**
     * Menampilkan form untuk menambahkan tahun ajaran ke sekolah.
     */
    public function create(School $school)
    {
        // Ambil ID tahun ajaran yang sudah ditautkan ke sekolah ini
        $linkedAcademicYearIds = $school->schoolAcademicYears()->pluck('academic_year_id');

        // Ambil semua tahun ajaran yang BELUM ditautkan
        $availableAcademicYears = AcademicYear::whereNotIn('id', $linkedAcademicYearIds)
            ->orderBy('start', 'desc')
            ->get();

        return Inertia::render('protected/schools/academic-years/create', [
            'school' => $school,
            'academicYears' => $availableAcademicYears,
        ]);
    }

    /**
     * Menyimpan tautan tahun ajaran baru ke sekolah.
     */
    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'ulid',
                // Pastikan academic_year_id ada di tabel academic_years
                Rule::exists('academic_years', 'id'),
                // Pastikan kombinasi school_id dan academic_year_id unik
                Rule::unique('school_academic_years')->where(function ($query) use ($school) {
                    return $query->where('school_id', $school->id);
                }),
            ],
        ]);

        $school->schoolAcademicYears()->create($validated);

        return redirect()->route('protected.schools.academic-years.index', $school)
            ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit tahun ajaran sekolah.
     */
    public function edit(School $school, SchoolAcademicYear $schoolAcademicYear)
    {
        // Otorisasi: Pastikan data yang akan diedit milik sekolah yang bersangkutan
        if ($schoolAcademicYear->school_id !== $school->id) {
            abort(403);
        }

        // Ambil ID tahun ajaran yang sudah ditautkan, KECUALI yang sedang diedit
        $linkedAcademicYearIds = $school->schoolAcademicYears()
            ->where('id', '!=', $schoolAcademicYear->id)
            ->pluck('academic_year_id');

        // Ambil tahun ajaran yang bisa dipilih (semua yang belum ditautkan + yang sedang diedit)
        $availableAcademicYears = AcademicYear::whereNotIn('id', $linkedAcademicYearIds)
            ->orderBy('start', 'desc')
            ->get();

        return Inertia::render('protected/schools/academic-years/edit', [
            'school' => $school,
            'schoolAcademicYear' => $schoolAcademicYear,
            'academicYears' => $availableAcademicYears,
        ]);
    }

    /**
     * Menampilkan detail tautan tahun ajaran sekolah.
     */
    public function show(School $school, SchoolAcademicYear $schoolAcademicYear)
    {
        // Otorisasi: Pastikan data yang akan ditampilkan milik sekolah yang bersangkutan
        if ($schoolAcademicYear->school_id !== $school->id) {
            abort(403);
        }

        // Eager load relasi academicYear agar datanya tersedia di frontend
        $schoolAcademicYear->load('academicYear');

        // Render halaman show dengan data yang diperlukan
        return Inertia::render('protected/schools/academic-years/show', [
            'school' => $school,
            'schoolAcademicYear' => $schoolAcademicYear,
        ]);
    }

    /**
     * Memperbarui tautan tahun ajaran sekolah.
     */
    public function update(Request $request, School $school, SchoolAcademicYear $schoolAcademicYear)
    {
        // Otorisasi
        if ($schoolAcademicYear->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'ulid',
                Rule::exists('academic_years', 'id'),
                // Pastikan unik, tapi abaikan record yang sedang diedit
                Rule::unique('school_academic_years')->where(function ($query) use ($school) {
                    return $query->where('school_id', $school->id);
                })->ignore($schoolAcademicYear->id),
            ],
        ]);

        $schoolAcademicYear->update($validated);

        return redirect()->route('protected.schools.academic-years.index', $school)
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }


    /**
     * Menghapus tautan tahun ajaran dari sekolah.
     */
    public function destroy(School $school, SchoolAcademicYear $schoolAcademicYear)
    {
        // Otorisasi: Pastikan data yang akan dihapus benar-benar milik sekolah yang bersangkutan
        if ($schoolAcademicYear->school_id !== $school->id) {
            abort(403, 'UNAUTHORIZED ACTION');
        }

        $schoolAcademicYear->delete();

        return redirect()->route('protected.schools.academic-years.index', $school)
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
