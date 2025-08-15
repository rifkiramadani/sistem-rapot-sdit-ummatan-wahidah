<?php

namespace App\Http\Controllers\Protected;

use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolAcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SchoolAcademicYearController extends Controller
{
    public function index(Request $request, School $school)
    {
        Gate::authorize('viewAny', SchoolAcademicYear::class);

        // 1. Validasi semua parameter request
        $request->validate([
            'per_page' => ['sometimes', 'integer', Rule::in(PerPageEnum::values())],
            // Sesuaikan kolom yang bisa di-sort
            'sort_by' => 'sometimes|string|in:name,start,end',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        // 2. Ambil parameter dengan nilai default
        $perPage = $request->input('per_page', PerPageEnum::DEFAULT->value);
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
     * Menampilkan detail tautan tahun ajaran sekolah.
     */
    public function show(School $school, SchoolAcademicYear $schoolAcademicYear)
    {
        Gate::authorize('show', $schoolAcademicYear);

        // Eager load relasi academicYear agar datanya tersedia di frontend
        $schoolAcademicYear->load('academicYear');

        // Render halaman show dengan data yang diperlukan
        return Inertia::render('protected/schools/academic-years/show', [
            'school' => $school,
            'schoolAcademicYear' => $schoolAcademicYear,
        ]);
    }

    /**
     * Menampilkan form untuk menambahkan tahun ajaran ke sekolah.
     */
    public function create(School $school)
    {
        Gate::authorize('create', SchoolAcademicYear::class);

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
        Gate::authorize('create', SchoolAcademicYear::class);

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
        Gate::authorize('update', $schoolAcademicYear);

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
     * Memperbarui tautan tahun ajaran sekolah.
     */
    public function update(Request $request, School $school, SchoolAcademicYear $schoolAcademicYear)
    {
        Gate::authorize('update', $schoolAcademicYear);

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
        Gate::authorize('delete', $schoolAcademicYear);

        $schoolAcademicYear->delete();

        return redirect()->route('protected.schools.academic-years.index', $school)
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function bulkDestroy(Request $request, School $school)
    {
        Gate::authorize('bulkDelete', SchoolAcademicYear::class);

        // Validasi
        $request->validate([
            'ids'   => ['required', 'array'],
            // Pastikan setiap ID ada dan milik sekolah yang benar
            'ids.*' => ['exists:school_academic_years,id'],
        ]);

        DB::transaction(function () use ($request, $school) {
            // 1. Ambil semua model yang akan dihapus
            $academicYears = SchoolAcademicYear::where('school_id', $school->id)
                ->whereIn('id', $request->input('ids'))
                ->get();

            // 2. Lakukan perulangan dan hapus satu per satu
            foreach ($academicYears as $academicYear) {
                // Perintah ini akan memicu event 'deleted' secara otomatis
                $academicYear->delete();
            }
        });

        return redirect()->route('protected.schools.academic-years.index', $school)->with('success', 'Tahun ajaran yang dipilih berhasil dihapus.');
    }
}
