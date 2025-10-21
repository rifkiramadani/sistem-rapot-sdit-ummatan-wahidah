<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Enums\GenderEnum;
use App\Enums\PerPageEnum;
use App\Enums\ReligionEnum;
use App\Http\Controllers\Controller;
use App\Models\SchoolAcademicYear;
use App\Models\Student;
use App\Models\Subject;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\LogBatch;

class StudentController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can view the list of students?
        Gate::authorize('viewAny', Student::class);

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
        // Authorization: Who can view the details of this student?
        Gate::authorize('view', $student);

        // Muat semua relasi yang dibutuhkan untuk ditampilkan
        $student->load(['parent', 'guardian']);

        return Inertia::render('protected/school-academic-years/students/show', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'student' => $student,
        ]);
    }

    public function create(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can create a new student?
        Gate::authorize('create', Student::class);

        return Inertia::render('protected/school-academic-years/students/create', [
            'schoolAcademicYear' => $schoolAcademicYear,
        ]);
    }

    /**
     * Menyimpan data Siswa, Orang Tua, dan Wali baru ke dalam database.
     */
    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can create a new student?
        Gate::authorize('create', Student::class);

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
        // Authorization: Who can update this student?
        Gate::authorize('update', $student);

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
        // Authorization: Who can update this student?
        Gate::authorize('update', $student);

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
        // Authorization: Who can delete this student?
        Gate::authorize('delete', $student);

        // Menghapus data siswa akan otomatis menghapus data parent dan guardian
        // karena onDelete('cascade') di migrasi.
        $student->delete();

        return redirect()->route('protected.school-academic-years.students.index', $schoolAcademicYear)
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Menampilkan mata pelajaran yang diambil oleh seorang siswa.
     */
    public function subjects(Request $request, SchoolAcademicYear $schoolAcademicYear, Student $student)
    {
        // Authorization: Who can view this student's subjects?
        Gate::authorize('view', $student);

        // Load student dengan relasi yang dibutuhkan
        $student->load(['classroomStudents.classroom.classroomSubjects.subject']);

        // Collect subjects dari semua classroom dimana student terdaftar
        $subjects = collect();

        foreach ($student->classroomStudents as $classroomStudent) {
            if ($classroomStudent->classroom) {
                foreach ($classroomStudent->classroom->classroomSubjects as $classroomSubject) {
                    if ($classroomSubject->subject) {
                        $subjects->push($classroomSubject->subject);
                    }
                }
            }
        }

        // Remove duplicates dan sort by name
        $subjects = $subjects->unique('id')->sortBy('name')->values();

        $schoolAcademicYear->load('academicYear');

        return Inertia::render('protected/school-academic-years/students/subjects/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'student' => $student,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Menampilkan detail mata pelajaran seorang siswa.
     */
    public function subjectDetail(Request $request, SchoolAcademicYear $schoolAcademicYear, Student $student, Subject $subject)
    {
        // Authorization: Who can view this student's subject details?
        Gate::authorize('view', $student);

        // Load relasi yang dibutuhkan
        $student->load(['classroomStudents.classroom.classroomSubjects' => function ($query) use ($subject) {
            $query->where('subject_id', $subject->id);
        }]);

        // Load student summatives untuk subject ini melalui classroom subject
        $studentSummatives = $student->studentSummatives()
            ->whereHas('summative.classroomSubject', function ($query) use ($subject) {
                $query->where('subject_id', $subject->id);
            })
            ->with(['summative.classroomSubject.subject'])
            ->get();

        $schoolAcademicYear->load('academicYear');

        return Inertia::render('protected/school-academic-years/students/subjects/show', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'student' => $student,
            'subject' => $subject,
            'studentSummatives' => $studentSummatives,
        ]);
    }

    /**
     * Menghapus beberapa data siswa sekaligus.
     */
    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can bulk delete students?
        Gate::authorize('bulkDelete', Student::class);


        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:students,id'],
        ]);

        LogBatch::startBatch();

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
