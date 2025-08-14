<?php

namespace App\Http\Controllers\Protected;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Database\Eloquent\Builder; // Import the Builder class
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

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

    public function create(Request $request)
    {
        return Inertia::render('protected/schools/create');
    }

    /**
     * Menyimpan data sekolah baru ke dalam database.
     */
    public function store(Request $request)
    {
        // Validasi data yang masuk dari form
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:schools,name',
            'address' => 'required|string',
            'npsn' => 'nullable|numeric', // ++ Perubahan di sini
            'postal_code' => 'nullable|numeric', // ++ Perubahan di sini
            'website' => 'nullable|string|url|max:255',
            'email' => 'nullable|string|email|max:255',
            'place_date_raport' => 'nullable|string|max:255',
            'place_date_sts' => 'nullable|string|max:255',
        ]);

        // Buat record baru di database
        School::create($validated);

        // Redirect kembali ke halaman index dengan pesan sukses
        return Redirect::route('protected.schools.index')->with('success', 'Sekolah berhasil dibuat.');
    }

    /**
     * Menampilkan form untuk mengedit data sekolah.
     * Menggunakan Route Model Binding untuk mengambil data sekolah secara otomatis.
     */
    public function edit(School $school)
    {
        return Inertia::render('Protected/Schools/Edit', [ // Pastikan nama view sudah benar
            'school' => $school,
            // Di sini Anda juga bisa meneruskan data lain seperti daftar
            // Kepala Sekolah atau Tahun Ajaran untuk komponen <Select>
        ]);
    }

    /**
     * Memperbarui data sekolah yang ada di database.
     */
    public function update(Request $request, School $school)
    {
        // Validasi data, dengan aturan 'unique' yang mengabaikan data saat ini
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('schools')->ignore($school->id),
            ],
            'address' => 'required|string',
            'npsn' => 'nullable|numeric', // ++ Perubahan di sini
            'postal_code' => 'nullable|numeric', // ++ Perubahan di sini
            'website' => 'nullable|string|url|max:255',
            'email' => 'nullable|string|email|max:255',
            'place_date_raport' => 'nullable|string|max:255',
            'place_date_sts' => 'nullable|string|max:255',
        ]);

        // Update record di database
        $school->update($validated);

        // Redirect kembali ke halaman index dengan pesan sukses
        return Redirect::route('protected.schools.index')->with('success', 'Data sekolah berhasil diperbarui.');
    }

    /**
     * Menghapus data sekolah dari database.
     */
    public function destroy(School $school)
    {
        // Hapus record dari database
        $school->delete();

        // Redirect kembali ke halaman index dengan pesan sukses
        return Redirect::route('protected.schools.index')->with('success', 'Data sekolah berhasil dihapus.');
    }
}
