<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Enums\PerPageEnum;
use App\Enums\RoleEnum; // ++ 1. Import RoleEnum
use App\Http\Controllers\Controller;
use App\Models\Role; // ++ 2. Import model Role
use App\Models\SchoolAcademicYear;
use App\Models\Teacher;
use App\Models\User;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB; // <-- Import DB
use Illuminate\Support\Facades\Hash; // <-- Import Hash
use Spatie\Activitylog\Facades\LogBatch;

class TeacherController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // 1. Validasi semua parameter request
        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            // Sesuaikan kolom yang bisa di-sort untuk model Teacher
            'sort_by' => 'sometimes|string|in:name,niy',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $teachers = QueryBuilder::for($schoolAcademicYear->teachers()->with('user'))
            ->through([
                Filter::class,
                Sort::class,
            ])
            ->paginate();

        // 7. Kembalikan response ke view Inertia
        return Inertia::render('protected/school-academic-years/teachers/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'teachers' => $teachers,
        ]);
    }

    public function show(Request $request, SchoolAcademicYear $schoolAcademicYear, Teacher $teacher)
    {
        // Gate::authorize('view', $teacher);

        // Muat relasi 'user' untuk menampilkan data user seperti email
        $teacher->load('user');

        return Inertia::render('protected/school-academic-years/teachers/show', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'teacher' => $teacher,
        ]);
    }

    public function create(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Tidak perlu lagi mengambil data user
        return Inertia::render('protected/school-academic-years/teachers/create', [
            'schoolAcademicYear' => $schoolAcademicYear,
        ]);
    }

    /**
     * Metode store sekarang akan membuat User dan Teacher sekaligus.
     */
    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Validasi data baru, termasuk email dan password
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'niy' => ['required', 'numeric', Rule::unique('teachers')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Gunakan transaksi database untuk memastikan kedua data berhasil dibuat
        DB::transaction(function () use ($validated, $schoolAcademicYear) {
            // ++ 3. Cari ID untuk peran 'teacher' menggunakan Enum
            $teacherRole = Role::where('name', RoleEnum::TEACHER->value)->firstOrFail();

            // ++ 4. Buat User baru dengan role_id yang sudah ditemukan
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $teacherRole->id, // Tetapkan role ID
            ]);

            // 5. Buat Teacher baru dan tautkan dengan user_id yang baru dibuat
            $schoolAcademicYear->teachers()->create([
                'name' => $validated['name'],
                'niy' => $validated['niy'],
                'user_id' => $user->id,
            ]);
        });

        return redirect()->route('protected.school-academic-years.teachers.index', $schoolAcademicYear)
            ->with('success', 'Guru berhasil dibuat beserta akunnya.');
    }

    /**
     * Menampilkan form untuk mengedit data guru.
     */
    public function edit(Request $request, SchoolAcademicYear $schoolAcademicYear, Teacher $teacher)
    {
        // Muat relasi 'user' agar email bisa ditampilkan di form
        $teacher->load('user');

        return Inertia::render('protected/school-academic-years/teachers/edit', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'teacher' => $teacher,
        ]);
    }

    /**
     * Memperbarui data guru di database.
     */
    public function update(Request $request, SchoolAcademicYear $schoolAcademicYear, Teacher $teacher)
    {
        // Validasi data. Perhatikan aturan 'unique' yang mengabaikan data saat ini.
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'niy' => ['required', 'numeric', Rule::unique('teachers')->ignore($teacher->id)],
            // Password bersifat opsional saat update
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated, $teacher) {
            // 1. Update data di tabel Teacher
            $teacher->update([
                'name' => $validated['name'],
                'niy' => $validated['niy'],
            ]);

            // 2. Update data nama di tabel User agar sinkron
            $teacher->user->update([
                'name' => $validated['name'],
            ]);

            // 3. Jika password diisi, update password user
            if (!empty($validated['password'])) {
                $teacher->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }
        });

        return redirect()->route('protected.school-academic-years.teachers.index', $schoolAcademicYear)
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    /**
     * Menghapus data guru dari database.
     */
    public function destroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Teacher $teacher)
    {
        // Gate::authorize('delete', $teacher);

        $teacher->user()->delete();

        return redirect()->route('protected.school-academic-years.teachers.index', $schoolAcademicYear)
            ->with('success', 'Data guru berhasil dihapus.');
    }

    /**
     * Menghapus beberapa data guru sekaligus.
     */
    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Gate::authorize('bulkDelete', Teacher::class);

        // Validasi bahwa 'ids' ada, merupakan sebuah array, dan setiap ID ada di tabel teachers
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:teachers,id'],
        ]);

        LogBatch::startBatch();

        DB::transaction(function () use ($request) {
            // Ambil semua model teacher yang akan dihapus, beserta relasi user-nya
            $teachers = Teacher::with('user')->whereIn('id', $request->input('ids'))->get();

            // Lakukan perulangan dan hapus user yang terkait
            foreach ($teachers as $teacher) {
                if ($teacher->user) {
                    $teacher->user->delete();
                } else {
                    // Fallback jika karena suatu hal user tidak ada, hapus teacher saja
                    $teacher->delete();
                }
            }
        });

        LogBatch::endBatch();

        return redirect()->route('protected.school-academic-years.teachers.index', $schoolAcademicYear)
            ->with('success', 'Data guru yang dipilih berhasil dihapus.');
    }
}