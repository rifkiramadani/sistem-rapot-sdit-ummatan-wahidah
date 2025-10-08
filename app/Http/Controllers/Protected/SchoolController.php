<?php

namespace App\Http\Controllers\Protected;

use Inertia\Inertia;
use App\Models\School;
use App\Enums\PerPageEnum;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Spatie\Activitylog\Facades\LogBatch;
use App\Http\Requests\UpdateSchoolRequest;
use App\QueryFilters\Sort;     // <-- Import Sort pipe
use App\QueryFilters\Filter;   // <-- Import Filter pipe
use App\Support\QueryBuilder; // <-- Import QueryBuilder
use Illuminate\Database\Eloquent\Builder; // Import the Builder class
use Illuminate\Support\Facades\DB; // Impor DB facade untuk transaksi

class SchoolController extends Controller
{

    /**
     * Helper untuk mendapatkan Sekolah Utama (data pertama).
     */
    private function getMainSchool(): School
    {
        // Ambil sekolah pertama, atau buat jika tidak ada (untuk dev/testing)
        // Gunakan findOrNew() atau first()
        return School::first() ?? School::factory()->create();
    }

    /**
     * [BARU] Menampilkan halaman detail sekolah utama.
     */
    public function showMainSchool()
    {
        // Hanya ambil data sekolah. Tidak perlu memuat currentAcademicYear lagi.
        $school = $this->getMainSchool();

        // Mengarahkan ke komponen React/Inertia baru
        return Inertia::render('protected/schools/main-school-detail', [
            'school' => $school->only([
                'id',
                'name',
                'npsn',
                'address',
                'postal_code',
                'website',
                'email',
                'place_date_raport',
                'place_date_sts',
            ]),
        ]);
    }

    /**
     * [BARU] Mengupdate data sekolah utama.
     */
    public function updateMainSchool(UpdateSchoolRequest $request)
    {
        $school = $this->getMainSchool();

        $school->update($request->validated());

        return to_route('protected.schools.detail')
            ->with('success', 'Informasi Sekolah Utama berhasil diperbarui.');
    }



    // DEPRECATED
    public function index(Request $request)
    {
        Gate::authorize('viewAny', School::class);

        // 1. Validasi semua parameter request
        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name,npsn',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            // ++ Tambahkan validasi untuk filter 'q'
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $schools = QueryBuilder::for(School::with(['principal', 'currentAcademicYear']))
            ->through([
                Filter::class, // Pipe ini akan otomatis memanggil scopeQ()
                Sort::class,
            ])
            ->paginate();

        return Inertia::render('protected/schools/index', [
            'schools' => $schools,
        ]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create', School::class);
        $user = $request->user(); // <-- current authenticated user

        // $user->notify(new \App\Notifications\ReportFinished(reportId: 1));

        return Inertia::render('protected/schools/create');
    }

    /**
     * Menyimpan data sekolah baru ke dalam database.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', School::class);

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
        return redirect()->route('protected.schools.index')->with('success', 'Sekolah berhasil dibuat.');
    }

    public function show(School $school)
    {
        Gate::authorize('view', $school);

        // Anda bisa memuat relasi di sini jika perlu,
        // seperti data kepala sekolah atau tahun ajaran.
        $school->load(['principal', 'currentAcademicYear']);

        return Inertia::render('protected/schools/show', [
            'school' => $school,
        ]);
    }

    /**
     * Menampilkan form untuk mengedit data sekolah.
     * Menggunakan Route Model Binding untuk mengambil data sekolah secara otomatis.
     */
    public function edit(School $school)
    {
        Gate::authorize('update', $school);

        return Inertia::render('protected/schools/edit', [ // Pastikan nama view sudah benar
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
        Gate::authorize('update', $school);

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
        return redirect()->route('protected.schools.index')->with('success', 'Data sekolah berhasil diperbarui.');
    }

    /**
     * Menghapus data sekolah dari database.
     */
    public function destroy(School $school)
    {
        Gate::authorize('delete', $school);

        // Hapus record dari database
        $school->delete();

        // Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('protected.schools.index')->with('success', 'Data sekolah berhasil dihapus.');
    }

    /**
     * Remove multiple specified resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        Gate::authorize('bulkDelete', School::class);

        // Validasi bahwa 'ids' ada dan merupakan sebuah array
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:schools,id'], // Pastikan setiap ID ada di tabel sekolah
        ]);

        LogBatch::startBatch();

        DB::transaction(function () use ($request) {
            // 1. Ambil semua model sekolah yang akan dihapus ke dalam koleksi
            $schools = School::whereIn('id', $request->input('ids'))->get();

            // 2. Lakukan perulangan pada koleksi dan hapus satu per satu
            foreach ($schools as $school) {
                // Method delete() pada instance model akan memicu event 'deleted'
                // sehingga Spatie Activity Log akan mencatatnya secara otomatis.
                $school->delete();
            }
        });

        LogBatch::endBatch();

        return redirect()->route('protected.schools.index')->with('success', 'Data sekolah yang dipilih berhasil dihapus.');
    }
}
