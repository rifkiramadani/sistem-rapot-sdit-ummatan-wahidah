<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Enums\DefaultSummativeTypeEnum;
use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\School;
use App\Models\SchoolAcademicYear;
use App\Models\Student;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Spatie\Activitylog\Facades\LogBatch;

class ClassroomStudentController extends Controller
{
    /**
     * Menampilkan daftar siswa dalam sebuah kelas.
     */
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        Gate::authorize('viewAny', ClassroomStudent::class);

        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name,nisn',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        // Check if current user is a teacher - they should only see their classroom students
        $user = $request->user();
        $isTeacher = $user && $user->role && $user->role->name === \App\Enums\RoleEnum::TEACHER->value;

        // Teachers can only access their own classrooms
        if ($isTeacher) {
            $teacherRecord = $user->teacher()
                ->where('school_academic_year_id', $schoolAcademicYear->id)
                ->first();

            if (!$teacherRecord || $teacherRecord->id !== $classroom->teacher_id) {
                // Teacher is not assigned to this classroom
                abort(403, 'You are not authorized to access this classroom.');
            }
        }

        // [UBAH] Gunakan QueryBuilder
        $classroomStudents = QueryBuilder::for(
            $classroom->classroomStudents()->with(['student.parent', 'student.guardian'])
        )
            ->through([
                Filter::class, // Akan otomatis memanggil scopeQ() di ClassroomStudent
                Sort::class,   // Akan otomatis memanggil scopeSort() di ClassroomStudent
            ])
            ->paginate();

        return Inertia::render('protected/school-academic-years/classrooms/students/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomStudents' => $classroomStudents,
            'isTeacher' => $isTeacher,
        ]);
    }

    public function show(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        Gate::authorize('view', $classroomStudent);

        // Muat semua relasi dari siswa yang terkait
        $classroomStudent->load(['student.parent', 'student.guardian', 'classroom.teacher']);

        return Inertia::render('protected/school-academic-years/classrooms/students/show', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomStudent' => $classroomStudent,
        ]);
    }

    /**
     * Menampilkan form untuk menambahkan siswa ke kelas.
     */
    public function create(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        Gate::authorize('create', ClassroomStudent::class);

        // Additional check: Verify user can create classroom students in this specific classroom
        if (!ClassroomStudent::canBeCreatedBy($request->user(), $classroom)) {
            abort(403, 'You are not authorized to add students to this classroom.');
        }

        // 1. Ambil ID siswa yang sudah ada di kelas ini via relasi `classroomStudents`
        $existingStudentIds = $classroom->classroomStudents()->pluck('student_id');

        // 2. Ambil siswa yang ada di tahun ajaran ini, TAPI belum ada di kelas ini
        $availableStudents = $schoolAcademicYear->students()
            ->whereNotIn('id', $existingStudentIds)
            ->orderBy('name')
            ->get();

        return Inertia::render('protected/school-academic-years/classrooms/students/create', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'availableStudents' => $availableStudents,
        ]);
    }

    /**
     * Menyimpan (menautkan) siswa ke dalam kelas.
     */
    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        Gate::authorize('create', ClassroomStudent::class);

        // Additional check: Verify user can create classroom students in this specific classroom
        if (!ClassroomStudent::canBeCreatedBy($request->user(), $classroom)) {
            abort(403, 'You are not authorized to add students to this classroom.');
        }

        $validated = $request->validate([
            'student_id' => [
                'required',
                'ulid',
                Rule::exists('students', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
                Rule::unique('classroom_students')->where('classroom_id', $classroom->id),
            ],
        ]);

        // [UBAH] Buat record baru di tabel pivot `classroom_students`
        // menggunakan relasi `classroomStudents()`
        $classroom->classroomStudents()->create([
            'student_id' => $validated['student_id'],
        ]);

        return redirect()->route('protected.school-academic-years.classrooms.students.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    // /**
    //  * Menampilkan form untuk mengedit (mengganti) siswa dalam sebuah kelas.
    //  */
    // public function edit(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    // {
    //     // Gate::authorize('update', $classroom);

    //     // 1. Ambil ID siswa yang sudah ada di kelas ini, KECUALI siswa yang sedang diedit
    //     $existingStudentIds = $classroom->classroomStudents()
    //         ->where('id', '!=', $classroomStudent->id)
    //         ->pluck('student_id');

    //     // 2. Ambil siswa yang ada di tahun ajaran ini, TAPI belum ada di kelas lain
    //     $availableStudents = $schoolAcademicYear->students()
    //         ->whereNotIn('id', $existingStudentIds)
    //         ->orderBy('name')
    //         ->get();

    //     return Inertia::render('protected/school-academic-years/classrooms/students/edit', [
    //         'schoolAcademicYear' => $schoolAcademicYear,
    //         'classroom' => $classroom,
    //         'classroomStudent' => $classroomStudent,
    //         'availableStudents' => $availableStudents,
    //     ]);
    // }

    // /**
    //  * Memperbarui data siswa di dalam kelas.
    //  */
    // public function update(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    // {
    //     // Gate::authorize('update', $classroom);

    //     $validated = $request->validate([
    //         'student_id' => [
    //             'required',
    //             'ulid',
    //             Rule::exists('students', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
    //             // Pastikan siswa yang baru dipilih belum terdaftar di kelas ini, abaikan record saat ini
    //             Rule::unique('classroom_students')->where('classroom_id', $classroom->id)->ignore($classroomStudent->id),
    //         ],
    //     ]);

    //     $classroomStudent->update($validated);

    //     return redirect()->route('protected.school-academic-years.classrooms.students.index', [$schoolAcademicYear, $classroom])
    //         ->with('success', 'Data siswa di kelas berhasil diperbarui.');
    // }

    public function destroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        Gate::authorize('delete', $classroomStudent);

        $classroomStudent->delete();

        return redirect()->route('protected.school-academic-years.classrooms.students.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Siswa berhasil dikeluarkan dari kelas.');
    }

    /**
     * Menampilkan daftar nilai sumatif siswa untuk semua mata pelajaran di kelasnya.
     */
    public function summatives(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        Gate::authorize('viewSummatives', $classroomStudent);
        $schoolAcademicYear->load('academicYear');
        // Load relasi yang dibutuhkan
        $classroomStudent->load(['student', 'classroom']);

        // Ambil semua classroom subjects dari kelas ini
        $classroomSubjects = $classroom->classroomSubjects()
            ->with('subject')
            ->get();

        // Kumpulkan semua summatives untuk siswa ini dari semua mata pelajaran di kelasnya
        $allSummatives = collect();

        foreach ($classroomSubjects as $classroomSubject) {
            // Ambil summatives untuk classroom subject ini
            $summatives = $classroomSubject->summatives()
                ->with(['summativeType', 'studentSummatives' => function ($query) use ($classroomStudent) {
                    $query->where('student_id', $classroomStudent->student_id);
                }])
                ->get();

            foreach ($summatives as $summative) {
                // Cari student summative untuk siswa ini
                $studentSummative = $summative->studentSummatives
                    ->where('student_id', $classroomStudent->student_id)
                    ->first();

                $allSummatives->push([
                    'id' => $summative->id,
                    'name' => $summative->name,
                    'identifier' => $summative->identifier,
                    'description' => $summative->description,
                    'type' => $summative->summativeType->name ?? 'Tidak ada',
                    'subject' => $classroomSubject->subject->name,
                    'student_value' => $studentSummative?->value,
                    'student_summative_id' => $studentSummative?->id,
                    'classroom_subject_id' => $classroomSubject->id,
                ]);
            }
        }

        // Sort by subject name then summative name
        $sortedSummatives = $allSummatives->sortBy(['subject', 'name'])->values();

        return Inertia::render('protected/school-academic-years/classrooms/students/summatives/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomStudent' => $classroomStudent,
            'summatives' => $sortedSummatives,
        ]);
    }

    /**
     * Mengeluarkan beberapa siswa dari kelas sekaligus.
     *
     * @param Request $request
     * @param SchoolAcademicYear $schoolAcademicYear
     * @param Classroom $classroom
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        Gate::authorize('bulkDelete', ClassroomStudent::class);

        $request->validate([
            'ids'   => ['required', 'array'],
            // Validasi bahwa ID yang dikirim adalah ID dari tabel classroom_students
            'ids.*' => ['exists:classroom_students,id'],
        ]);

        LogBatch::startBatch();

        DB::transaction(function () use ($request, $classroom) {
            ClassroomStudent::where('classroom_id', $classroom->id)
                ->whereIn('id', $request->input('ids'))
                ->delete();
        });

        LogBatch::endBatch();

        return redirect()->route('protected.school-academic-years.classrooms.students.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Siswa yang dipilih berhasil dikeluarkan dari kelas.');
    }

    /**
     * Export student summatives to Word document.
     */
    public function exportSummativesWord(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        Gate::authorize('exportDocuments', $classroomStudent);

        try {
            Log::info('Memulai ekspor Word nilai sumatif siswa.', [
                'classroomStudentId' => $classroomStudent->id,
                'studentName' => $classroomStudent->student->name,
                'className' => $classroom->name,
            ]);

            // Load data yang dibutuhkan
            $schoolAcademicYear->load('academicYear');
            $classroomStudent->load(['student', 'classroom']);

            // Ambil semua classroom subjects dari kelas ini
            $classroomSubjects = $classroom->classroomSubjects()
                ->with('subject')
                ->get();

            // Kumpulkan semua summatives untuk siswa ini
            $allSummatives = collect();
            foreach ($classroomSubjects as $classroomSubject) {
                $summatives = $classroomSubject->summatives()
                    ->with(['summativeType', 'studentSummatives' => function ($query) use ($classroomStudent) {
                        $query->where('student_id', $classroomStudent->student_id);
                    }])
                    ->get();

                foreach ($summatives as $summative) {
                    $studentSummative = $summative->studentSummatives
                        ->where('student_id', $classroomStudent->student_id)
                        ->first();

                    $allSummatives->push([
                        'id' => $summative->id,
                        'name' => $summative->name,
                        'identifier' => $summative->identifier,
                        'description' => $summative->description,
                        'type' => $summative->summativeType->name ?? 'Tidak ada',
                        'subject' => $classroomSubject->subject->name,
                        'student_value' => $studentSummative?->value,
                        'classroom_subject_id' => $classroomSubject->id,
                    ]);
                }
            }

            // Sort by subject name then summative name
            $sortedSummatives = $allSummatives->sortBy(['subject', 'name'])->values();

            // Cek jika data kosong
            if ($sortedSummatives->isEmpty()) {
                Log::warning('Ekspor Word dibatalkan: Tidak ada data sumatif untuk siswa.', [
                    'classroomStudentId' => $classroomStudent->id,
                ]);
                return redirect()->route('protected.school-academic-years.classrooms.students.summatives', [$schoolAcademicYear, $classroom, $classroomStudent])
                    ->with('error', 'Tidak ada data nilai untuk diekspor.');
            }

            // Generate Word document
            return $this->generateStudentSummativesDocument(
                $sortedSummatives,
                $classroomStudent,
                $classroom,
                $schoolAcademicYear
            );
        } catch (\Exception $e) {
            Log::error('Gagal membuat dokumen Word nilai sumatif siswa!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'classroomStudentId' => $classroomStudent->id,
            ]);

            report($e);
            return redirect()->back()->with('error', 'Gagal membuat dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Generate Word document for student summatives.
     */
    private function generateStudentSummativesDocument($summatives, $classroomStudent, $classroom, $schoolAcademicYear)
    {
        try {
            Log::info('Memulai pembuatan dokumen Word untuk nilai sumatif siswa.', [
                'totalSummatives' => $summatives->count(),
            ]);

            // 1. Muat template
            $templatePath = storage_path('app/templates/Template_Rapor.docx');

            // Jika template tidak ada, buat template sederhana
            if (!file_exists($templatePath)) {
                Log::error('Template Rapor tidak ditemukan!', ['path' => $templatePath]);
                throw new \Exception('File template Template_Rapor.docx tidak ditemukan di server.');
            }

            $templateProcessor = new TemplateProcessor($templatePath);

            // 2. Isi placeholder data sekolah
            $school = $schoolAcademicYear->school;
            $school->load('principal');
            $templateProcessor->setValue('nama_sekolah', $school->name ?? '');
            $templateProcessor->setValue('npsn', $school->npsn ?? '');
            $templateProcessor->setValue('alamat_sekolah', $school->address ?? '');

            // 3. Isi placeholder data siswa
            $student = $classroomStudent->student;
            $parent = $student->parent;
            $genderLabels = [
                \App\Enums\GenderEnum::MALE->value => 'Laki-laki',
                \App\Enums\GenderEnum::FEMALE->value => 'Perempuan',
            ];
            $religionLabels = [
                \App\Enums\ReligionEnum::MUSLIM->value => 'Islam',
                \App\Enums\ReligionEnum::CHRISTIAN->value => 'Kristen Protestan',
                \App\Enums\ReligionEnum::CATHOLIC->value => 'Kristen Katolik',
                \App\Enums\ReligionEnum::HINDU->value => 'Hindu',
                \App\Enums\ReligionEnum::BUDDHIST->value => 'Buddha',
                \App\Enums\ReligionEnum::OTHER->value => 'Lainnya',
            ];
            $birthDate = \Carbon\Carbon::parse($student->birth_date)->locale('id')->translatedFormat('d F Y');

            $templateProcessor->setValue('nama_siswa', $student->name ?? '');
            $templateProcessor->setValue('nisn', $student->nisn ?? '');
            $templateProcessor->setValue('jenis_kelamin', $genderLabels[$student->gender->value] ?? '');
            $templateProcessor->setValue('tempat_lahir', $student->birth_place ?? '');
            $templateProcessor->setValue('tanggal_lahir', $birthDate);
            $templateProcessor->setValue('agama', $religionLabels[$student->religion->value] ?? '');

            // 4. Isi placeholder data akademik
            $templateProcessor->setValue('nama_kelas', $classroom->name ?? '');
            $templateProcessor->setValue('semester', $schoolAcademicYear->academicYear->name ?? '');
            $templateProcessor->setValue('tahun_ajaran', $schoolAcademicYear->year);

            // 5. Process nilai-nilai untuk semester yang dipilih
            $this->processSummativeGradesForRapor($templateProcessor, $summatives, $classroomStudent, $classroom, $schoolAcademicYear->academicYear);

            // 6. Isi placeholder data orang tua
            $templateProcessor->setValue('nama_ayah', $parent->father_name ?? '');
            $templateProcessor->setValue('pekerjaan_ayah', $parent->father_job ?? '');
            $templateProcessor->setValue('nama_ibu', $parent->mother_name ?? '');
            $templateProcessor->setValue('pekerjaan_ibu', $parent->mother_job ?? '');

            // 7. Isi placeholder data kepala sekolah
            $principal = $school->principal;
            if ($principal) {
                $templateProcessor->setValue('nama_kepala_sekolah', $principal->name ?? '');
                $templateProcessor->setValue('nip_kepala_sekolah', $principal->employee_id ?? $principal->nip ?? '');
            } else {
                $templateProcessor->setValue('nama_kepala_sekolah', '');
                $templateProcessor->setValue('nip_kepala_sekolah', '');
            }

            // Note: Using Template_Rapor.docx which has predefined table structure
            // The table will be populated by the processSummativeGradesForRapor method

            // 8. Simpan dan download
            $studentName = str_replace([' ', '/', '\\'], '_', $classroomStudent->student->name);
            $semesterName = str_replace([' ', '/', '\\'], '_', $schoolAcademicYear->academicYear->name);
            $filename = 'Rapor_Nilai_' . $studentName . '_' . $semesterName . '.docx';
            $tempPath = storage_path('app/temp');
            File::ensureDirectoryExists($tempPath);
            $tempFilePath = $tempPath . '/' . uniqid('nilai_sumatif_siswa_', true) . '.docx';
            $templateProcessor->saveAs($tempFilePath);

            Log::info('Dokumen siswa berhasil disimpan.', [
                'tempFilePath' => $tempFilePath,
                'filename' => $filename
            ]);

            return response()
                ->download($tempFilePath, $filename)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Gagal membuat dokumen Word siswa!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Create simple template for student summatives if template doesn't exist.
     */
    private function createSimpleStudentTemplate($templatePath)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Header
        $section->addText('REKAP NILAI SUMATIF SISWA', ['bold' => true, 'size' => 16], ['alignment' => 'center']);
        $section->addTextBreak();

        // Student info table
        $infoTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 50]);

        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('Nama Siswa:', ['bold' => true]);
        $infoTable->addCell(6000)->addText('${nama_siswa}');

        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('NISN:', ['bold' => true]);
        $infoTable->addCell(6000)->addText('${nisn}');

        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('Kelas:', ['bold' => true]);
        $infoTable->addCell(6000)->addText('${nama_kelas}');

        $infoTable->addRow();
        $infoTable->addCell(2000)->addText('Tahun Ajaran:', ['bold' => true]);
        $infoTable->addCell(6000)->addText('${tahun_ajaran}');

        $section->addTextBreak();

        // Placeholder for scores table
        $section->addText('${tabel_nilai}');

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($templatePath);
    }

    /**
     * Export student report card cover page.
     */
    public function exportReportCover(SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        Gate::authorize('exportDocuments', $classroomStudent);

        try {
            Log::info('Memulai ekspor Sampul Rapor.', [
                'classroomStudentId' => $classroomStudent->id,
                'studentName' => $classroomStudent->student->name,
                'className' => $classroom->name,
            ]);

            // Load data yang dibutuhkan
            $schoolAcademicYear->load(['academicYear', 'school']);
            $classroomStudent->load(['student.parent', 'student.guardian']);

            // Get school data
            $school = $schoolAcademicYear->school;
            $school->load('principal');

            // Generate Word document
            return $this->generateReportCoverDocument(
                $classroomStudent,
                $classroom,
                $schoolAcademicYear,
                $school
            );
        } catch (\Exception $e) {
            Log::error('Gagal membuat Sampul Rapor!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'classroomStudentId' => $classroomStudent->id,
            ]);

            report($e);
            return redirect()->back()->with('error', 'Gagal membuat Sampul Rapor: ' . $e->getMessage());
        }
    }

    /**
     * Generate Word document for report card cover.
     */
    private function generateReportCoverDocument($classroomStudent, $classroom, $schoolAcademicYear, $school)
    {
        try {
            Log::info('Memulai pembuatan dokumen Sampul Rapor.');

            // 1. Muat template
            $templatePath = storage_path('app/templates/Sampul_Rapor.docx');

            if (!file_exists($templatePath)) {
                Log::error('Template Sampul Rapor tidak ditemukan!', ['path' => $templatePath]);
                throw new \Exception('File template Sampul Rapor tidak ditemukan di server.');
            }

            $templateProcessor = new TemplateProcessor($templatePath);

            // 2. Siapkan data siswa
            $student = $classroomStudent->student;
            $parent = $student->parent;
            $guardian = $student->guardian;

            // Gender labels - handle enum properly
            $genderLabels = [
                \App\Enums\GenderEnum::MALE->value => 'Laki-laki',
                \App\Enums\GenderEnum::FEMALE->value => 'Perempuan',
            ];
            $religionLabels = [
                \App\Enums\ReligionEnum::MUSLIM->value => 'Islam',
                \App\Enums\ReligionEnum::CHRISTIAN->value => 'Kristen Protestan',
                \App\Enums\ReligionEnum::CATHOLIC->value => 'Kristen Katolik',
                \App\Enums\ReligionEnum::HINDU->value => 'Hindu',
                \App\Enums\ReligionEnum::BUDDHIST->value => 'Buddha',
                \App\Enums\ReligionEnum::OTHER->value => 'Lainnya',
            ];

            // Format tanggal lahir
            $birthDate = \Carbon\Carbon::parse($student->birth_date)->locale('id')->translatedFormat('d F Y');

            // 3. Isi placeholder data sekolah
            $templateProcessor->setValue('nama_sekolah', $school->name ?? '');
            $templateProcessor->setValue('npsn', $school->npsn ?? '');
            $templateProcessor->setValue('alamat_sekolah', $school->address ?? '');
            $templateProcessor->setValue('kode_pos', $school->postal_code ?? '');
            $templateProcessor->setValue('website', $school->website ?? '');
            $templateProcessor->setValue('email', $school->email ?? '');

            // --- TAMBAHKAN BARIS-BARIS DI BAWAH INI ---
            $templateProcessor->setValue('desa_kelurahan', $school->village ?? '');
            $templateProcessor->setValue('kecamatan', $school->district ?? '');
            $templateProcessor->setValue('kabupaten_kota', $school->city ?? '');
            $templateProcessor->setValue('provinsi', $school->province ?? '');
            // --- BATAS TAMBAHAN ---

            // 4. Isi placeholder data siswa
            $templateProcessor->setValue('nama_siswa', $student->name ?? '');
            $templateProcessor->setValue('nisn', $student->nisn ?? '');
            $templateProcessor->setValue('nis', '-'); // NIS jika ada
            $templateProcessor->setValue('jenis_kelamin', $genderLabels[$student->gender->value] ?? '');
            $templateProcessor->setValue('tempat_lahir', $student->birth_place ?? '');
            $templateProcessor->setValue('tanggal_lahir', $birthDate);
            $templateProcessor->setValue('agama', $religionLabels[$student->religion->value] ?? '');
            $templateProcessor->setValue('last_education', $student->last_education ?? '');
            $templateProcessor->setValue('alamat_siswa', $student->address ?? '');

            // 5. Isi placeholder data orang tua
            $templateProcessor->setValue('nama_ayah', $parent->father_name ?? '');
            $templateProcessor->setValue('pekerjaan_ayah', $parent->father_job ?? '');
            $templateProcessor->setValue('nama_ibu', $parent->mother_name ?? '');
            $templateProcessor->setValue('pekerjaan_ibu', $parent->mother_job ?? '');
            $templateProcessor->setValue('alamat_orang_tua', $parent->address ?? '');

            // 6. Isi placeholder data wali
            $templateProcessor->setValue('nama_wali', $guardian->name ?? '');
            $templateProcessor->setValue('pekerjaan_wali', $guardian->job ?? '');
            $templateProcessor->setValue('no_telp_wali', $guardian->phone_number ?? '');
            $templateProcessor->setValue('alamat_wali', $guardian->address ?? '');

            // 7. Isi placeholder data akademik
            $templateProcessor->setValue('nama_kelas', $classroom->name ?? '');
            $templateProcessor->setValue('tahun_ajaran', $schoolAcademicYear->year . ' Semester ' . $schoolAcademicYear->academicYear->name);

            // 8. Isi placeholder data kepala sekolah
            $principal = $school->principal;
            if ($principal) {
                $templateProcessor->setValue('nama_kepala_sekolah', $principal->name ?? '');
                $templateProcessor->setValue('nip_kepala_sekolah', $principal->employee_id ?? $principal->nip ?? '');
            } else {
                $templateProcessor->setValue('nama_kepala_sekolah', '');
                $templateProcessor->setValue('nip_kepala_sekolah', '');
            }

            // 9. Handle foto siswa (jika ada)
            $this->handleStudentPhoto($templateProcessor, $student);

            // 10. Handle tanda tangan kepala sekolah (placeholder)
            $templateProcessor->setValue('tanda_tangan_kepala_sekolah', '[Tanda Tangan Kepala Sekolah]');

            // 11. Generate nama file
            $studentName = str_replace([' ', '/', '\\'], '_', $student->name);
            $yearName = str_replace([' ', '/', '\\'], '_', $schoolAcademicYear->year);
            $filename = 'Sampul_Rapor_' . $studentName . '_' . $yearName . '.docx';
            $tempPath = storage_path('app/temp');
            File::ensureDirectoryExists($tempPath);
            $tempFilePath = $tempPath . '/' . uniqid('sampul_rapor_', true) . '.docx';
            $templateProcessor->saveAs($tempFilePath);

            Log::info('Sampul Rapor berhasil disimpan.', [
                'tempFilePath' => $tempFilePath,
                'filename' => $filename
            ]);

            return response()
                ->download($tempFilePath, $filename)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Gagal membuat Sampul Rapor!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle student photo embedding in template.
     */
    private function handleStudentPhoto($templateProcessor, $student)
    {
        try {
            // Check if student has photo path
            $photoPath = null;

            // Try different possible photo field names
            $possiblePhotoFields = ['photo', 'avatar', 'picture', 'image'];
            foreach ($possiblePhotoFields as $field) {
                if (isset($student->$field) && !empty($student->$field)) {
                    $photoPath = $student->$field;
                    break;
                }
            }

            if ($photoPath) {
                $fullPhotoPath = storage_path("app/public/{$photoPath}");

                if (file_exists($fullPhotoPath)) {
                    // Embed photo into template
                    $templateProcessor->setImageValue('foto_siswa', $fullPhotoPath);
                    return;
                }
            }

            // If no photo found, set placeholder text
            $templateProcessor->setValue('foto_siswa', '[Foto Siswa]');
        } catch (\Exception $e) {
            Log::warning('Gagal memproses foto siswa, menggunakan placeholder.', [
                'error' => $e->getMessage(),
                'studentId' => $student->id,
            ]);
            $templateProcessor->setValue('foto_siswa', '[Foto Siswa]');
        }
    }

    /**
     * Export School Transfer Certificate.
     */
    public function exportTransferCertificate(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        Gate::authorize('exportDocuments', $classroomStudent);

        try {
            Log::info('Memulai ekspor Surat Keterangan Pindah Sekolah.', [
                'classroomStudentId' => $classroomStudent->id,
                'studentName' => $classroomStudent->student->name,
            ]);

            $validated = $request->validate([
                'transfer_date' => 'required|date',
                'transfer_reason' => 'required|string|max:500',
                'destination_school' => 'nullable|string|max:200',
                'destination_city' => 'nullable|string|max:100',
            ]);

            // Load data yang dibutuhkan
            $schoolAcademicYear->load(['academicYear', 'school']);
            $classroomStudent->load(['student.parent', 'student.guardian']);
            $school = $schoolAcademicYear->school;
            $school->load('principal');

            return $this->generateTransferCertificateDocument(
                $classroomStudent,
                $classroom,
                $schoolAcademicYear,
                $school,
                $validated
            );
        } catch (\Exception $e) {
            Log::error('Gagal membuat Surat Keterangan Pindah Sekolah!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'classroomStudentId' => $classroomStudent->id,
            ]);

            report($e);
            return redirect()->back()->with('error', 'Gagal membuat Surat Keterangan Pindah Sekolah: ' . $e->getMessage());
        }
    }

    /**
     * Generate Word document for Transfer Certificate.
     */
    private function generateTransferCertificateDocument($classroomStudent, $classroom, $schoolAcademicYear, $school, $transferData)
    {
        try {
            Log::info('Memulai pembuatan dokumen Surat Keterangan Pindah Sekolah.');

            // 1. Muat template
            $templatePath = storage_path('app/templates/TEMPLATE_KETERANGAN_PINDAH_SEKOLAH.docx');

            if (!file_exists($templatePath)) {
                Log::error('Template Surat Keterangan Pindah Sekolah tidak ditemukan!', ['path' => $templatePath]);
                throw new \Exception('File template Surat Keterangan Pindah Sekolah tidak ditemukan di server.');
            }

            $templateProcessor = new TemplateProcessor($templatePath);

            // 2. Siapkan data siswa
            $student = $classroomStudent->student;
            $parent = $student->parent;
            $guardian = $student->guardian;

            // Gender labels
            $genderLabels = [
                \App\Enums\GenderEnum::MALE->value => 'Laki-laki',
                \App\Enums\GenderEnum::FEMALE->value => 'Perempuan',
            ];
            $religionLabels = [
                \App\Enums\ReligionEnum::MUSLIM->value => 'Islam',
                \App\Enums\ReligionEnum::CHRISTIAN->value => 'Kristen Protestan',
                \App\Enums\ReligionEnum::CATHOLIC->value => 'Kristen Katolik',
                \App\Enums\ReligionEnum::HINDU->value => 'Hindu',
                \App\Enums\ReligionEnum::BUDDHIST->value => 'Buddha',
                \App\Enums\ReligionEnum::OTHER->value => 'Lainnya',
            ];

            // Format tanggal
            $birthDate = \Carbon\Carbon::parse($student->birth_date)->locale('id')->translatedFormat('d F Y');
            $transferDate = \Carbon\Carbon::parse($transferData['transfer_date'])->locale('id')->translatedFormat('d F Y');

            // 3. Isi placeholder data sekolah
            $templateProcessor->setValue('nama_sekolah', $school->name ?? '');
            $templateProcessor->setValue('npsn', $school->npsn ?? '');
            $templateProcessor->setValue('alamat_sekolah', $school->address ?? '');
            $templateProcessor->setValue('kota_sekolah', $school->city ?? '');
            $templateProcessor->setValue('provinsi_sekolah', $school->province ?? '');

            // 4. Isi placeholder data siswa
            $templateProcessor->setValue('nama_siswa', $student->name ?? '');
            $templateProcessor->setValue('nisn', $student->nisn ?? '');
            $templateProcessor->setValue('nis', '-'); // NIS jika ada
            $templateProcessor->setValue('jenis_kelamin', $genderLabels[$student->gender->value] ?? '');
            $templateProcessor->setValue('tempat_lahir', $student->birth_place ?? '');
            $templateProcessor->setValue('tanggal_lahir', $birthDate);
            $templateProcessor->setValue('agama', $religionLabels[$student->religion->value] ?? '');
            $templateProcessor->setValue('alamat_siswa', $student->address ?? '');

            // 5. Isi placeholder data akademik
            $templateProcessor->setValue('nama_kelas', $classroom->name ?? '');
            $templateProcessor->setValue('tahun_ajaran', $schoolAcademicYear->year);

            // 6. Isi placeholder data pindah
            $templateProcessor->setValue('tanggal_pindah', $transferDate);
            $templateProcessor->setValue('alasan_pindah', $transferData['transfer_reason']);
            $templateProcessor->setValue('sekolah_tujuan', $transferData['destination_school'] ?? '-');
            $templateProcessor->setValue('kota_tujuan', $transferData['destination_city'] ?? '-');

            // 7. Isi placeholder data orang tua
            $templateProcessor->setValue('nama_ayah', $parent->father_name ?? '');
            $templateProcessor->setValue('pekerjaan_ayah', $parent->father_job ?? '');
            $templateProcessor->setValue('nama_ibu', $parent->mother_name ?? '');
            $templateProcessor->setValue('pekerjaan_ibu', $parent->mother_job ?? '');

            // 8. Isi placeholder data kepala sekolah
            $principal = $school->principal;
            if ($principal) {
                $templateProcessor->setValue('nama_kepala_sekolah', $principal->name ?? '');
                $templateProcessor->setValue('nip_kepala_sekolah', $principal->employee_id ?? $principal->nip ?? '');
            } else {
                $templateProcessor->setValue('nama_kepala_sekolah', '');
                $templateProcessor->setValue('nip_kepala_sekolah', '');
            }

            // 9. Format tanggal surat
            $currentDate = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');
            $templateProcessor->setValue('tanggal_surat', $currentDate);

            // 10. Generate nama file
            $studentName = str_replace([' ', '/', '\\'], '_', $student->name);
            $filename = 'Surat_Keterangan_Pindah_Sekolah_' . $studentName . '.docx';
            $tempPath = storage_path('app/temp');
            File::ensureDirectoryExists($tempPath);
            $tempFilePath = $tempPath . '/' . uniqid('transfer_certificate_', true) . '.docx';
            $templateProcessor->saveAs($tempFilePath);

            Log::info('Surat Keterangan Pindah Sekolah berhasil disimpan.', [
                'tempFilePath' => $tempFilePath,
                'filename' => $filename
            ]);

            return response()
                ->download($tempFilePath, $filename)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Gagal membuat Surat Keterangan Pindah Sekolah!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Export Final Report Card.
     */
    public function exportReportCard(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        Gate::authorize('exportDocuments', $classroomStudent);

        try {
            Log::info('Memulai ekspor Rapor Akhir.', [
                'classroomStudentId' => $classroomStudent->id,
                'studentName' => $classroomStudent->student->name,
            ]);

            $validated = $request->validate([
                'semester_id' => 'required|exists:academic_years,id',
            ]);

            // Load data yang dibutuhkan
            $schoolAcademicYear->load(['academicYear', 'school']);
            $classroomStudent->load(['student.parent', 'student.guardian']);
            $school = $schoolAcademicYear->school;
            $school->load('principal');

            $selectedSemester = \App\Models\AcademicYear::find($validated['semester_id']);

            return $this->generateReportCardDocument(
                $classroomStudent,
                $classroom,
                $schoolAcademicYear,
                $school,
                $selectedSemester
            );
        } catch (\Exception $e) {
            Log::error('Gagal membuat Rapor Akhir!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'classroomStudentId' => $classroomStudent->id,
            ]);

            report($e);
            return redirect()->back()->with('error', 'Gagal membuat Rapor Akhir: ' . $e->getMessage());
        }
    }

    /**
     * Generate Word document for Final Report Card.
     */
    private function generateReportCardDocument($classroomStudent, $classroom, $schoolAcademicYear, $school, $semester)
    {
        try {
            Log::info('Memulai pembuatan dokumen Rapor Akhir.');

            // 1. Muat template
            $templatePath = storage_path('app/templates/Template_Rapor.docx');

            if (!file_exists($templatePath)) {
                Log::error('Template Rapor Akhir tidak ditemukan!', ['path' => $templatePath]);
                throw new \Exception('File template Rapor Akhir tidak ditemukan di server.');
            }

            $templateProcessor = new TemplateProcessor($templatePath);

            // 2. Siapkan data siswa
            $student = $classroomStudent->student;
            $parent = $student->parent;

            // Gender labels
            $genderLabels = [
                \App\Enums\GenderEnum::MALE->value => 'Laki-laki',
                \App\Enums\GenderEnum::FEMALE->value => 'Perempuan',
            ];
            $religionLabels = [
                \App\Enums\ReligionEnum::MUSLIM->value => 'Islam',
                \App\Enums\ReligionEnum::CHRISTIAN->value => 'Kristen Protestan',
                \App\Enums\ReligionEnum::CATHOLIC->value => 'Kristen Katolik',
                \App\Enums\ReligionEnum::HINDU->value => 'Hindu',
                \App\Enums\ReligionEnum::BUDDHIST->value => 'Buddha',
                \App\Enums\ReligionEnum::OTHER->value => 'Lainnya',
            ];

            // Format tanggal
            $birthDate = \Carbon\Carbon::parse($student->birth_date)->locale('id')->translatedFormat('d F Y');

            // 3. Isi placeholder data sekolah
            $templateProcessor->setValue('nama_sekolah', $school->name ?? '');
            $templateProcessor->setValue('npsn', $school->npsn ?? '');
            $templateProcessor->setValue('alamat_sekolah', $school->address ?? '');

            // 4. Isi placeholder data siswa
            $templateProcessor->setValue('nama_siswa', $student->name ?? '');
            $templateProcessor->setValue('nisn', $student->nisn ?? '');
            $templateProcessor->setValue('jenis_kelamin', $genderLabels[$student->gender->value] ?? '');
            $templateProcessor->setValue('tempat_lahir', $student->birth_place ?? '');
            $templateProcessor->setValue('tanggal_lahir', $birthDate);
            $templateProcessor->setValue('agama', $religionLabels[$student->religion->value] ?? '');

            // 5. Isi placeholder data akademik
            $templateProcessor->setValue('nama_kelas', $classroom->name ?? '');
            $templateProcessor->setValue('semester', $semester->name ?? '');
            $templateProcessor->setValue('tahun_ajaran', $schoolAcademicYear->year);

            // 6. Process nilai-nilai untuk semester yang dipilih
            $this->processReportCardGrades($templateProcessor, $classroomStudent, $classroom, $semester);

            // 7. Isi placeholder data orang tua
            $templateProcessor->setValue('nama_ayah', $parent->father_name ?? '');
            $templateProcessor->setValue('pekerjaan_ayah', $parent->father_job ?? '');
            $templateProcessor->setValue('nama_ibu', $parent->mother_name ?? '');
            $templateProcessor->setValue('pekerjaan_ibu', $parent->mother_job ?? '');

            // 8. Isi placeholder data kepala sekolah
            $principal = $school->principal;
            if ($principal) {
                $templateProcessor->setValue('nama_kepala_sekolah', $principal->name ?? '');
                $templateProcessor->setValue('nip_kepala_sekolah', $principal->employee_id ?? $principal->nip ?? '');
            } else {
                $templateProcessor->setValue('nama_kepala_sekolah', '');
                $templateProcessor->setValue('nip_kepala_sekolah', '');
            }

            // 9. Generate nama file
            $studentName = str_replace([' ', '/', '\\'], '_', $student->name);
            $semesterName = str_replace([' ', '/', '\\'], '_', $semester->name);
            $filename = 'Rapor_Akhir_' . $studentName . '_' . $semesterName . '.docx';
            $tempPath = storage_path('app/temp');
            File::ensureDirectoryExists($tempPath);
            $tempFilePath = $tempPath . '/' . uniqid('report_card_', true) . '.docx';
            $templateProcessor->saveAs($tempFilePath);

            Log::info('Rapor Akhir berhasil disimpan.', [
                'tempFilePath' => $tempFilePath,
                'filename' => $filename
            ]);

            return response()
                ->download($tempFilePath, $filename)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Gagal membuat Rapor Akhir!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Process grades for report card.
     */
    private function processReportCardGrades($templateProcessor, $classroomStudent, $classroom, $semester)
    {
        // Placeholder untuk implementasi pengolahan nilai
        // Ini akan diisi sesuai dengan struktur database yang ada
        $templateProcessor->setValue('tabel_nilai', '[Tabel Nilai Akan Diisi Sini]');
        $templateProcessor->setValue('catatan_wali_kelas', '[Catatan Wali Kelas Akan Diisi Sini]');
        $templateProcessor->setValue('kehadiran_sakit', '0');
        $templateProcessor->setValue('kehadiran_izin', '0');
        $templateProcessor->setValue('kehadiran_tanpa_keterangan', '0');
        $templateProcessor->setValue('ekstrakurikuler', '[Data Ekstrakurikuler Akan Diisi Sini]');
    }

    /**
     * Export STS Assessment Data.
     */
    public function exportSts(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        Gate::authorize('exportDocuments', $classroomStudent);

        try {
            Log::info('Memulai ekspor Data STS.', [
                'classroomStudentId' => $classroomStudent->id,
                'studentName' => $classroomStudent->student->name,
            ]);

            // Load data yang dibutuhkan
            $schoolAcademicYear->load(['academicYear', 'school']);
            $classroomStudent->load(['student.parent', 'student.guardian']);
            $school = $schoolAcademicYear->school;
            $school->load('principal');

            return $this->generateStsDocument(
                $classroomStudent,
                $classroom,
                $schoolAcademicYear,
                $school
            );
        } catch (\Exception $e) {
            Log::error('Gagal membuat Data STS!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'classroomStudentId' => $classroomStudent->id,
            ]);

            report($e);
            return redirect()->back()->with('error', 'Gagal membuat Data STS: ' . $e->getMessage());
        }
    }

    /**
     * Generate Word document for STS.
     */
    private function generateStsDocument($classroomStudent, $classroom, $schoolAcademicYear, $school)
    {
        try {
            Log::info('Memulai pembuatan dokumen STS dengan injeksi XML (Struktur Dinamis).');

            // 1. Muat template
            $templatePath = storage_path('app/templates/Template_Rapor_STS.docx');
            if (!file_exists($templatePath)) {
                Log::error('Template STS tidak ditemukan!', ['path' => $templatePath]);
                throw new \Exception('File template STS tidak ditemukan di server.');
            }
            $templateProcessor = new TemplateProcessor($templatePath);

            // 2. Isi placeholder sederhana
            $student = $classroomStudent->student;
            $templateProcessor->setValue('nama_siswa', $student->name ?? '');
            $templateProcessor->setValue('nisn', $student->nisn ?? '');
            $templateProcessor->setValue('nis', '-');

            $semesterRoman = $schoolAcademicYear->academicYear->name === 'GANJIL' ? 'I' : 'II';
            $tahunAjaranShort = str_replace('/', ' / 202', $schoolAcademicYear->year);

            $templateProcessor->setValue('nama_kelas', $classroom->name ?? '');
            $templateProcessor->setValue('semester_roman', $semesterRoman);
            $templateProcessor->setValue('smt', $semesterRoman);
            $templateProcessor->setValue('tahun_ajaran_short', $tahunAjaranShort);
            $templateProcessor->setValue('fase', $classroom->fase ?? '-');

            // ... (Kode untuk mengisi data Kepala Sekolah & Guru tetap sama) ...
            $principal = $school->principal;
            if ($principal) {
                $templateProcessor->setValue('nama_kepala_sekolah', $principal->name ?? '');
                $templateProcessor->setValue('nip_kepala_sekolah', $principal->employee_id ?? $principal->nip ?? '');
            } else {
                $templateProcessor->setValue('nama_kepala_sekolah', 'Alfera Zelfiani, S.Pd.I');
                $templateProcessor->setValue('nip_kepala_sekolah', '10226020 5006 13 0032');
            }

            $teacher = $classroom->teacher;
            if ($teacher) {
                $templateProcessor->setValue('nama_guru_kelas', $teacher->name ?? '');
                $templateProcessor->setValue('nip_guru_kelas', $teacher->employee_id ?? $teacher->nip ?? '');
            } else {
                $templateProcessor->setValue('nama_guru_kelas', 'DWI RATNA SARI, S.Pd.I');
                $templateProcessor->setValue('nip_guru_kelas', '10226020 5006 13 0006');
            }

            $place = $school->place_date_sts ?? 'Rejang Lebong';
            $date = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');
            $templateProcessor->setValue('place_date_sts', $place . ', ' . $date);

            // 3. Persiapan Tabel PhpWord
            $tablePhpWord = new PhpWord();
            $section = $tablePhpWord->addSection();

            // Definisikan Gaya
            $headerStyle = ['bold' => true, 'bgColor' => 'F2F2F2', 'size' => 9];
            $bodyStyle = ['size' => 9];
            $bodyBoldStyle = ['size' => 9, 'bold' => true];
            $cellCentered = ['alignment' => 'center', 'valign' => 'center'];
            $cellLeft = ['alignment' => 'left', 'valign' => 'center'];
            $cellSpan = ['gridSpan' => 2, 'valign' => 'center']; // Untuk footer
            $tableStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80, 'alignment' => 'center'];

            // Definisikan lebar kolom
            $colWidths = [
                'no' => 600,
                'mapel' => 5000,
                'non_tes' => 1200,
                'tes' => 1200,
                'jumlah' => 1200,
                'na' => 1200,
            ];

            $wordTable = $section->addTable($tableStyle);

            // 4. Buat Baris Header Tabel
            $wordTable->addRow(400);
            $wordTable->addCell($colWidths['no'], $cellCentered)->addText('NO', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidths['mapel'], $cellCentered)->addText('MATA PELAJARAN', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidths['non_tes'], $cellCentered)->addText('NON TES', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidths['tes'], $cellCentered)->addText('TES', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidths['jumlah'], $cellCentered)->addText('JUMLAH', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidths['na'], $cellCentered)->addText('N.A.', $headerStyle, $cellCentered);

            // 5. Ambil SEMUA data nilai dinamis SATU KALI
            $student_id = $classroomStudent->student_id;
            // Muat mata pelajaran dari kelas 
            $classroomSubjects = $classroom->classroomSubjects()
                ->join('subjects', 'classroom_subjects.subject_id', '=', 'subjects.id')
                ->orderBy('subjects.name', 'asc') // Urutkan berdasarkan nama mapel
                ->with([
                    'subject',
                    'summatives.summativeType',
                    'summatives.studentSummatives' => function ($query) use ($student_id) {
                        $query->where('student_id', $student_id);
                    }
                ])
                ->select('classroom_subjects.*') // Hindari ambiguitas kolom
                ->get();

            $totalAllSubjectsNA = 0;
            $subjectCount = 0;
            $i = 1; // Indeks baris tabel, mulai dari 1

            // 6. Loop melalui daftar DINAMIS
            foreach ($classroomSubjects as $cs) {
                // Hentikan jika kita sudah mengisi 11 baris (sesuai template)
                if ($i > 11) break;

                $materiScores = [];
                $stsScores = [];

                foreach ($cs->summatives as $summative) {
                    $scoreModel = $summative->studentSummatives->first();
                    $score = $scoreModel ? (int)$scoreModel->value : null;

                    if ($score !== null) {
                        $typeName = $summative->summativeType->name;
                        if ($typeName === DefaultSummativeTypeEnum::MATERI->value) {
                            $materiScores[] = $score;
                        } elseif ($typeName === DefaultSummativeTypeEnum::TENGAH_SEMESTER->value) {
                            $stsScores[] = $score;
                        }
                    }
                }

                $materiMean = count($materiScores) > 0 ? array_sum($materiScores) / count($materiScores) : 0;
                $stsMean = count($stsScores) > 0 ? array_sum($stsScores) / count($stsScores) : 0;
                $jumlah = $materiMean + $stsMean;

                $naScores = [];
                if ($materiMean > 0) $naScores[] = $materiMean;
                if ($stsMean > 0) $naScores[] = $stsMean;
                $na = count($naScores) > 0 ? array_sum($naScores) / count($naScores) : 0;

                if ($na > 0) {
                    $totalAllSubjectsNA += $na;
                    $subjectCount++;
                }

                // Tambahkan baris ke tabel Word
                $wordTable->addRow();
                $wordTable->addCell($colWidths['no'], $cellCentered)->addText($i, $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidths['mapel'], $cellLeft)->addText($cs->subject->name, $bodyStyle, $cellLeft);
                $wordTable->addCell($colWidths['non_tes'], $cellCentered)->addText($materiMean > 0 ? round($materiMean, 1) : '-', $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidths['tes'], $cellCentered)->addText($stsMean > 0 ? round($stsMean, 1) : '-', $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidths['jumlah'], $cellCentered)->addText($jumlah > 0 ? round($jumlah, 1) : '-', $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidths['na'], $cellCentered)->addText($na > 0 ? round($na, 1) : '-', $bodyStyle, $cellCentered);

                $i++; // Pindah ke baris berikutnya
            }

            // 7. Isi baris kosong jika mapel < 11
            // $i sekarang adalah nomor baris BERIKUTNYA
            for ($j = $i; $j <= 11; $j++) {
                $wordTable->addRow();
                $wordTable->addCell($colWidths['no'], $cellCentered)->addText($j, $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidths['mapel'], $cellLeft)->addText('-', $bodyStyle, $cellLeft);
                $wordTable->addCell($colWidths['non_tes'], $cellCentered)->addText('-', $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidths['tes'], $cellCentered)->addText('-', $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidths['jumlah'], $cellCentered)->addText('-', $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidths['na'], $cellCentered)->addText('-', $bodyStyle, $cellCentered);
            }

            // 8. Buat Baris Footer Tabel (Rata-rata & Peringkat)
            $rataRata = $subjectCount > 0 ? $totalAllSubjectsNA / $subjectCount : 0;

            $wordTable->addRow();
            $wordTable->addCell($colWidths['no'] + $colWidths['mapel'], $cellSpan)->addText('Rata - Rata', $bodyBoldStyle, $cellLeft);
            $wordTable->addCell(array_sum(array_slice($colWidths, 2)), ['gridSpan' => 4, 'valign' => 'center'])->addText($rataRata > 0 ? round($rataRata, 2) : '-', $bodyBoldStyle, $cellCentered);

            $wordTable->addRow();
            $wordTable->addCell($colWidths['no'] + $colWidths['mapel'], $cellSpan)->addText('Peringkat Kelas', $bodyBoldStyle, $cellLeft);
            $wordTable->addCell(array_sum(array_slice($colWidths, 2)), ['gridSpan' => 4, 'valign' => 'center'])->addText('-', $bodyBoldStyle, $cellCentered); // Peringkat tidak dihitung


            // 9. Ekstraksi XML Tabel (Logika dari referensi)
            Log::info('Tabel STS (Struktur Dinamis) berhasil dibuat di memori. Memulai ekstraksi XML.');

            $tempTableFile = tempnam(sys_get_temp_dir(), 'phpword_table_sts');
            $xmlWriter = IOFactory::createWriter($tablePhpWord, 'Word2007');
            $xmlWriter->save($tempTableFile);

            $zip = new \ZipArchive();
            if ($zip->open($tempTableFile) === true) {
                $fullXml = $zip->getFromName('word/document.xml');
                $zip->close();
                unlink($tempTableFile);
            } else {
                throw new \Exception('Gagal memproses XML tabel STS (ZipArchive).');
            }

            if (!$fullXml) {
                throw new \Exception('Gagal memproses XML tabel STS (XML kosong).');
            }

            $tableXml = '';
            if (preg_match('/<w:body(.*?)>(.*)<\/w:body>/s', $fullXml, $matches)) {
                $bodyContent = $matches[2];
                $tableXml = preg_replace('/<w:sectPr\s*.*?\s*\/w:sectPr>/s', '', $bodyContent);
                $tableXml = preg_replace('/<w:lastRenderedPageBreak\s*\/>/', '', $tableXml);
                $tableXml = trim($tableXml);
            } else {
                throw new \Exception('Gagal memproses XML tabel STS (preg_match body).');
            }

            // 10. Injeksi XML Tabel ke Template Utama
            // Pastikan template-mu memiliki placeholder ${tabel_nilai_sts}
            $templateProcessor->setValue('tabel_nilai_sts', $tableXml);


            // 11. Generate nama file dan Simpan
            $studentName = str_replace([' ', '/', '\\'], '_', $student->name);
            $filename = 'STS_' . $studentName . '.docx';
            $tempPath = storage_path('app/temp');
            File::ensureDirectoryExists($tempPath);
            $tempFilePath = $tempPath . '/' . uniqid('sts_', true) . '.docx';
            $templateProcessor->saveAs($tempFilePath);

            Log::info('STS (XML Injected) berhasil disimpan.', [
                'tempFilePath' => $tempFilePath,
                'filename' => $filename
            ]);

            return response()
                ->download($tempFilePath, $filename)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Gagal membuat STS (XML Injected)!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }
}