<?php

namespace App\Http\Controllers\Protected;

use App\Enums\GenderEnum;
use App\Enums\PerPageEnum;
use App\Enums\ReligionEnum;
use App\Http\Controllers\Controller;
use App\Models\SchoolAcademicYear;
use App\Models\Student;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\LogBatch;

class StudentController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Gate::authorize('viewAny', Student::class);

        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name,nisn', // Kolom yang bisa di-sort
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $students = QueryBuilder::for($schoolAcademicYear->students()->with(['parent', 'guardian']))
            ->through([
                Filter::class,
                Sort::class,
            ])
            ->paginate();

        return Inertia::render('protected/school-academic-years/students/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'students' => $students,
        ]);
    }

    /**
     * Menampilkan detail data seorang siswa.
     */
    public function show(Request $request, SchoolAcademicYear $schoolAcademicYear, Student $student)
    {
        // Gate::authorize('view', $student);

        // Muat semua relasi yang dibutuhkan untuk ditampilkan
        $student->load(['parent', 'guardian']);

        return Inertia::render('protected/school-academic-years/students/show', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'student' => $student,
        ]);
    }

    public function create(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Gate::authorize('create', Student::class);

        return Inertia::render('protected/school-academic-years/students/create', [
            'schoolAcademicYear' => $schoolAcademicYear,
        ]);
    }

    /**
     * Menyimpan data Siswa, Orang Tua, dan Wali baru ke dalam database.
     */
    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Gate::authorize('create', Student::class);

        // Validasi semua data yang masuk dari form
        $validated = $request->validate([
            // Data Siswa
            'nisn' => ['required', 'numeric', Rule::unique('students')],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(GenderEnum::class)],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'religion' => ['required', Rule::enum(ReligionEnum::class)],
            'address' => ['required', 'string'],

            // Data Orang Tua
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'father_job' => ['nullable', 'string', 'max:255'],
            'mother_job' => ['nullable', 'string', 'max:255'],
            'parent_address' => ['required', 'string'],

            // Data Wali
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_job' => ['nullable', 'string', 'max:255'],
            'guardian_phone_number' => ['nullable', 'string', 'max:255'],
            'guardian_address' => ['required', 'string'],
        ]);

        // Gunakan transaksi untuk memastikan semua data berhasil disimpan
        DB::transaction(function () use ($validated, $schoolAcademicYear) {
            // 1. Buat data Siswa
            $student = $schoolAcademicYear->students()->create([
                'nisn' => $validated['nisn'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'],
                'birth_date' => $validated['birth_date'],
                'religion' => $validated['religion'],
                'address' => $validated['address'],
            ]);

            // 2. Buat data Orang Tua yang berelasi dengan siswa
            $student->parent()->create([
                'father_name' => $validated['father_name'],
                'mother_name' => $validated['mother_name'],
                'father_job' => $validated['father_job'],
                'mother_job' => $validated['mother_job'],
                'address' => $validated['parent_address'],
            ]);

            // 3. Buat data Wali yang berelasi dengan siswa
            $student->guardian()->create([
                'name' => $validated['guardian_name'],
                'job' => $validated['guardian_job'],
                'phone_number' => $validated['guardian_phone_number'],
                'address' => $validated['guardian_address'],
            ]);
        });

        return redirect()->route('protected.school-academic-years.students.index', $schoolAcademicYear)
            ->with('success', 'Data siswa berhasil dibuat.');
    }


    /**
     * Menampilkan form untuk mengedit data siswa.
     */
    public function edit(Request $request, SchoolAcademicYear $schoolAcademicYear, Student $student)
    {
        // Gate::authorize('update', $student);

        // Muat relasi parent dan guardian agar datanya bisa ditampilkan di form
        $student->load(['parent', 'guardian']);

        return Inertia::render('protected/school-academic-years/students/edit', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'student' => $student,
        ]);
    }

    /**
     * Memperbarui data siswa di database.
     */
    public function update(Request $request, SchoolAcademicYear $schoolAcademicYear, Student $student)
    {
        // Gate::authorize('update', $student);

        // Validasi data, aturan 'unique' mengabaikan data siswa saat ini
        $validated = $request->validate([
            // Data Siswa
            'nisn' => ['required', 'numeric', Rule::unique('students')->ignore($student->id)],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(GenderEnum::class)],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'religion' => ['required', Rule::enum(ReligionEnum::class)],
            'address' => ['required', 'string'],

            // Data Orang Tua
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'father_job' => ['nullable', 'string', 'max:255'],
            'mother_job' => ['nullable', 'string', 'max:255'],
            'parent_address' => ['required', 'string'],

            // Data Wali
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_job' => ['nullable', 'string', 'max:255'],
            'guardian_phone_number' => ['nullable', 'string', 'max:255'],
            'guardian_address' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($validated, $student) {
            // 1. Update data Siswa
            $student->update([
                'nisn' => $validated['nisn'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'],
                'birth_date' => $validated['birth_date'],
                'religion' => $validated['religion'],
                'address' => $validated['address'],
            ]);

            // 2. Update data Orang Tua
            $student->parent()->update([
                'father_name' => $validated['father_name'],
                'mother_name' => $validated['mother_name'],
                'father_job' => $validated['father_job'],
                'mother_job' => $validated['mother_job'],
                'address' => $validated['parent_address'],
            ]);

            // 3. Update data Wali
            $student->guardian()->update([
                'name' => $validated['guardian_name'],
                'job' => $validated['guardian_job'],
                'phone_number' => $validated['guardian_phone_number'],
                'address' => $validated['guardian_address'],
            ]);
        });

        return redirect()->route('protected.school-academic-years.students.index', $schoolAcademicYear)
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Menghapus data siswa dari database.
     */
    public function destroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Student $student)
    {
        // Gate::authorize('delete', $student);

        // Menghapus data siswa akan otomatis menghapus data parent dan guardian
        // karena onDelete('cascade') di migrasi.
        $student->delete();

        return redirect()->route('protected.school-academic-years.students.index', $schoolAcademicYear)
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Menghapus beberapa data siswa sekaligus.
     */
    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Gate::authorize('bulkDelete', Student::class);
        LogBatch::startBatch();

        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:students,id'],
        ]);

        // Menggunakan loop agar event model (seperti 'deleting') tetap terpicu
        DB::transaction(function () use ($request) {
            Student::whereIn('id', $request->input('ids'))
                ->get()
                ->each->delete();
        });

        LogBatch::endBatch();

        return redirect()->route('protected.school-academic-years.students.index', $schoolAcademicYear)
            ->with('success', 'Data siswa yang dipilih berhasil dihapus.');
    }
}
