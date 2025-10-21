<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
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
        // Gate::authorize('view', $classroom);

        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name,nisn',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

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
        ]);
    }

    public function show(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        // Gate::authorize('view', $classroomStudent);

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
        // Gate::authorize('update', $classroom);

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
        // Gate::authorize('update', $classroom);

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
        // Gate::authorize('delete', $classroomStudent);

        $classroomStudent->delete();

        return redirect()->route('protected.school-academic-years.classrooms.students.index', [$schoolAcademicYear, $classroom])
            ->with('success', 'Siswa berhasil dikeluarkan dari kelas.');
    }

    /**
     * Menampilkan daftar nilai sumatif siswa untuk semua mata pelajaran di kelasnya.
     */
    public function summatives(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomStudent $classroomStudent)
    {
        // Gate::authorize('view', $classroomStudent);
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
        // Gate::authorize('bulkDelete', ClassroomStudent::class);

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
            $templatePath = storage_path('app/templates/template_rekap_nilai_siswa.docx');

            // Jika template tidak ada, buat template sederhana
            if (!file_exists($templatePath)) {
                Log::info('Template tidak ditemukan, membuat template sederhana.');
                $this->createSimpleStudentTemplate($templatePath);
            }

            $templateProcessor = new TemplateProcessor($templatePath);

            // 2. Isi placeholder
            $templateProcessor->setValue('nama_siswa', $classroomStudent->student->name);
            $templateProcessor->setValue('nisn', $classroomStudent->student->nisn);
            $templateProcessor->setValue('nama_kelas', $classroom->name);
            $templateProcessor->setValue('tahun_ajaran', $schoolAcademicYear->year . ' Semester ' . $schoolAcademicYear->academicYear->name);

            // 3. Buat tabel untuk nilai
            $tablePhpWord = new PhpWord();
            $section = $tablePhpWord->addSection();

            // Gaya
            $headerStyle = ['bold' => true, 'bgColor' => 'F2F2F2', 'size' => 10];
            $bodyStyle = ['size' => 10];

            $wordTable = $section->addTable([
                'borderSize' => 6,
                'borderColor' => '000000',
                'cellMargin' => 50,
                'alignment' => 'center',
                'width' => 10000,
                'unit' => 'pct',
            ]);

            // Header tabel
            $wordTable->addRow();
            $wordTable->addCell(2000)->addText('Mata Pelajaran', $headerStyle, ['alignment' => 'center']);
            $wordTable->addCell(3000)->addText('Nama Penilaian', $headerStyle, ['alignment' => 'center']);
            $wordTable->addCell(1500)->addText('Identitas', $headerStyle, ['alignment' => 'center']);
            $wordTable->addCell(1500)->addText('Tipe', $headerStyle, ['alignment' => 'center']);
            $wordTable->addCell(1000)->addText('Nilai', $headerStyle, ['alignment' => 'center']);
            $wordTable->addCell(2000)->addText('Keterangan', $headerStyle, ['alignment' => 'center']);

            // Data tabel
            foreach ($summatives as $summative) {
                $wordTable->addRow();
                $wordTable->addCell(2000)->addText($summative['subject'], $bodyStyle);
                $wordTable->addCell(3000)->addText($summative['name'], $bodyStyle);
                $wordTable->addCell(1500)->addText($summative['identifier'] ?: '-', $bodyStyle, ['alignment' => 'center']);
                $wordTable->addCell(1500)->addText($summative['type'], $bodyStyle, ['alignment' => 'center']);
                $wordTable->addCell(1000)->addText($summative['student_value'] !== null ? (string) $summative['student_value'] : '-', $bodyStyle, ['alignment' => 'center']);
                $wordTable->addCell(2000)->addText($summative['description'] ?: '-', $bodyStyle);
            }

            // 4. Proses tabel ke XML
            $tempTableFile = tempnam(sys_get_temp_dir(), 'phpword_student_table');
            $xmlWriter = IOFactory::createWriter($tablePhpWord, 'Word2007');
            $xmlWriter->save($tempTableFile);

            $zip = new \ZipArchive();
            if ($zip->open($tempTableFile) === true) {
                $fullXml = $zip->getFromName('word/document.xml');
                $zip->close();
                unlink($tempTableFile);
            } else {
                Log::error('Gagal membuka file Word sementara sebagai zip.');
                throw new \Exception('Gagal memproses XML tabel untuk injeksi.');
            }

            if (!$fullXml) {
                Log::error('Gagal membaca word/document.xml dari file zip.');
                throw new \Exception('Gagal memproses XML tabel untuk injeksi.');
            }

            // Ekstraksi konten body
            $tableXml = '';
            if (preg_match('/<w:body(.*?)>(.*)<\/w:body>/s', $fullXml, $matches)) {
                $bodyContent = $matches[2];
                $tableXml = preg_replace('/<w:sectPr\s*.*?\s*\/w:sectPr>/s', '', $bodyContent);
                $tableXml = preg_replace('/<w:lastRenderedPageBreak\s*\/>/', '', $tableXml);
                $tableXml = trim($tableXml);
            } else {
                Log::error('Gagal mengekstrak body XML dari document.xml.');
                throw new \Exception('Gagal memproses XML tabel untuk injeksi.');
            }

            // Set placeholder dengan XML tabel
            $templateProcessor->setValue('tabel_nilai', $tableXml);

            // 5. Simpan dan download
            $filename = 'nilai-sumatif-' . str_replace(' ', '_', $classroomStudent->student->name) . '-' . str_replace(' ', '_', $classroom->name) . '.docx';
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
}
