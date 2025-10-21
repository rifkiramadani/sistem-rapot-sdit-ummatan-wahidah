<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\SchoolAcademicYear;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\LogBatch;

class ClassroomController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can view the list of classrooms?
        Gate::authorize('viewAny', Classroom::class);

        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name', // Untuk saat ini, sort hanya berdasarkan nama kelas
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $classrooms = QueryBuilder::for($schoolAcademicYear->classrooms()->with('teacher'))
            ->through([
                Filter::class, // Akan otomatis memanggil scopeQ()
                Sort::class,
            ])
            ->paginate();

        return Inertia::render('protected/school-academic-years/classrooms/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classrooms' => $classrooms,
        ]);
    }

    /**
     * Menampilkan detail data sebuah kelas.
     */
    public function show(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        // Authorization: Who can view the details of this classroom?
        Gate::authorize('view', $classroom);

        // Muat relasi yang dibutuhkan untuk halaman detail
        $classroom->load(['teacher', 'classroomStudents.student'])
            ->loadCount('classroomStudents');

        return Inertia::render('protected/school-academic-years/classrooms/show', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
        ]);
    }

    public function create(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can create a new classroom?
        Gate::authorize('create', Classroom::class);

        // Ambil daftar guru di tahun ajaran ini untuk dropdown wali kelas
        $teachers = $schoolAcademicYear->teachers()->orderBy('name')->get();

        return Inertia::render('protected/school-academic-years/classrooms/create', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Menyimpan kelas baru ke dalam database.
     */
    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can create a new classroom?
        Gate::authorize('create', Classroom::class);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Pastikan nama kelas unik HANYA dalam lingkup tahun ajaran yang sama
                Rule::unique('classrooms')->where('school_academic_year_id', $schoolAcademicYear->id),
            ],
            'teacher_id' => [
                'required',
                // Pastikan guru yang dipilih ada dan termasuk dalam tahun ajaran ini
                Rule::exists('teachers', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
            ],
        ]);

        // Buat kelas baru yang berelasi dengan tahun ajaran saat ini
        $schoolAcademicYear->classrooms()->create($validated);

        return redirect()->route('protected.school-academic-years.classrooms.index', $schoolAcademicYear)
            ->with('success', 'Kelas berhasil dibuat.');
    }

    /**
     * Menampilkan form untuk mengedit kelas.
     */
    public function edit(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        // Authorization: Who can update this classroom?
        Gate::authorize('update', $classroom);

        // Ambil guru yang belum menjadi wali kelas, KECUALI wali kelas saat ini
        $assignedTeacherIds = $schoolAcademicYear->classrooms()
            ->where('id', '!=', $classroom->id) // Abaikan kelas yang sedang diedit
            ->pluck('teacher_id');

        $availableTeachers = $schoolAcademicYear->teachers()
            ->whereNotIn('id', $assignedTeacherIds)
            ->orderBy('name')
            ->get();

        return Inertia::render('protected/school-academic-years/classrooms/edit', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'teachers' => $availableTeachers,
        ]);
    }

    /**
     * Memperbarui data kelas di database.
     */
    public function update(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        // Authorization: Who can update this classroom?
        Gate::authorize('update', $classroom);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // [UBAH] Tambahkan ->ignore() untuk mengabaikan data saat ini
                Rule::unique('classrooms')
                    ->where('school_academic_year_id', $schoolAcademicYear->id)
                    ->ignore($classroom->id),
            ],
            'teacher_id' => [
                'required',
                Rule::exists('teachers', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
                // [UBAH] Tambahkan ->ignore() juga di sini jika wali kelas tidak diubah
                Rule::unique('classrooms')
                    ->where('school_academic_year_id', $schoolAcademicYear->id)
                    ->ignore($classroom->id),
            ],
        ]);

        $classroom->update($validated);

        return redirect()->route('protected.school-academic-years.classrooms.index', $schoolAcademicYear)
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * Menghapus data kelas dari database.
     */
    public function destroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        // Authorization: Who can delete this classroom?
        Gate::authorize('delete', $classroom);

        $classroom->delete();

        return redirect()->route('protected.school-academic-years.classrooms.index', $schoolAcademicYear)
            ->with('success', 'Data kelas berhasil dihapus.');
    }

    /**
     * Menghapus beberapa data kelas sekaligus.
     */
    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can bulk delete classrooms?
        Gate::authorize('bulkDelete', Classroom::class);

        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:classrooms,id'],
        ]);

        LogBatch::startBatch();

        DB::transaction(function () use ($request) {
            Classroom::whereIn('id', $request->input('ids'))->get()->each->delete();
        });

        LogBatch::endBatch();

        return redirect()->route('protected.school-academic-years.classrooms.index', $schoolAcademicYear)
            ->with('success', 'Data kelas yang dipilih berhasil dihapus.');
    }
}