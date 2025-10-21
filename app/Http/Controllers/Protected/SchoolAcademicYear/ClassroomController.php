<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\SchoolAcademicYear;
use App\Models\StudentSummative;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Spatie\Activitylog\Facades\LogBatch;
use Illuminate\Support\Collection;

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

        // Check if current user is a teacher and filter their classrooms
        $user = $request->user();
        $isTeacher = $user && $user->role && $user->role->name === \App\Enums\RoleEnum::TEACHER->value;

        $classroomsQuery = $schoolAcademicYear->classrooms()->with('teacher');

        if ($isTeacher) {
            // Filter to show only classrooms where this user is the teacher
            $teacherRecord = $user->teacher()
                ->where('school_academic_year_id', $schoolAcademicYear->id)
                ->first();

            if ($teacherRecord) {
                $classroomsQuery->where('teacher_id', $teacherRecord->id);
            } else {
                // If teacher is not registered in this academic year, return empty result
                $classroomsQuery->whereRaw('1 = 0');
            }
        }

        $classrooms = QueryBuilder::for($classroomsQuery)
            ->through([
                Filter::class, // Akan otomatis memanggil scopeQ()
                Sort::class,
            ])
            ->paginate();

        return Inertia::render('protected/school-academic-years/classrooms/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classrooms' => $classrooms,
            'isTeacher' => $isTeacher,
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

    /**
     * Export Final Grades for the entire class.
     */
    public function exportFinalGrades(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom)
    {
        Gate::authorize('view', $classroom);

        try {
            Log::info('Memulai ekspor Nilai Akhir Kelas.', [
                'classroomId' => $classroom->id,
                'className' => $classroom->name,
            ]);

            // Load data yang dibutuhkan
            $schoolAcademicYear->load(['academicYear', 'school']);
            $classroom->load(['teacher', 'classroomStudents.student']);

            return $this->generateFinalGradesDocument(
                $classroom,
                $schoolAcademicYear,
                $schoolAcademicYear->school
            );
        } catch (\Exception $e) {
            Log::error('Gagal membuat Nilai Akhir Kelas!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'classroomId' => $classroom->id,
            ]);

            report($e);
            return redirect()->back()->with('error', 'Gagal membuat Nilai Akhir Kelas: ' . $e->getMessage());
        }
    }

    /**
     * Generate Word document for Final Grades.
     */
    private function generateFinalGradesDocument($classroom, $schoolAcademicYear, $school)
    {
        try {
            Log::info('Memulai pembuatan dokumen Nilai Akhir Kelas (XML Injection - Dynamic Subjects).');

            // 1. Muat template
            $templatePath = storage_path('app/templates/Template_Rapor_NA.docx');
            if (!file_exists($templatePath)) {
                Log::error('Template Nilai Akhir tidak ditemukan!', ['path' => $templatePath]);
                throw new \Exception('File template Template_Rapor_NA.docx tidak ditemukan di server.');
            }
            $templateProcessor = new TemplateProcessor($templatePath);

            // 2. Isi placeholder sederhana
            $templateProcessor->setValue('kelas', $classroom->name ?? '');
            $templateProcessor->setValue('semester', $schoolAcademicYear->academicYear->name ?? '');

            // Format tahun ajaran
            $years = explode('/', $schoolAcademicYear->year ?? '20XX/20XX');
            $startYear = $years[0] ? substr($years[0], -2) : 'XX'; // Ambil 2 digit terakhir
            $endYear = count($years) > 1 && $years[1] ? substr($years[1], -2) : 'XX'; // Ambil 2 digit terakhir
            $templateProcessor->setValue('tahun_mulai', $startYear);
            $templateProcessor->setValue('tahun_selesai', $endYear);

            // Isi data Kepala Sekolah
            $principal = $school->principal; // Asumsi relasi 'principal' sudah di-load
            if ($principal) {
                $templateProcessor->setValue('nama_kepala_sekolah', $principal->name ?? 'Alfera Zelfiani, S.Pd.I');
                $nipKepsek = $principal->employee_id ?? $principal->nip ?? '10226020 5006 13 0032';
                $templateProcessor->setValue('nip_kepala_sekolah', $nipKepsek);
            } else {
                $templateProcessor->setValue('nama_kepala_sekolah', 'Alfera Zelfiani, S.Pd.I');
                $templateProcessor->setValue('nip_kepala_sekolah', '10226020 5006 13 0032');
            }


            // 3. Ambil Data Inti (Siswa dan Mapel)
            $classroomStudents = $classroom->classroomStudents()
                ->with(['student'])
                ->select('classroom_students.*')
                ->join('students', 'classroom_students.student_id', '=', 'students.id')
                ->orderBy('students.name', 'asc')
                ->get();

            $classroomSubjects = $classroom->classroomSubjects()
                ->join('subjects', 'classroom_subjects.subject_id', '=', 'subjects.id')
                ->orderBy('subjects.name', 'asc') // Urutkan mapel
                ->with('subject')
                ->select('classroom_subjects.*')
                ->get();

            $studentIds = $classroomStudents->pluck('student_id');
            $classroomSubjectIds = $classroomSubjects->pluck('id');

            // 4. Ambil SEMUA Nilai Sumatif yang Relevan dalam SATU Kueri
            $allStudentScores = StudentSummative::whereIn('student_id', $studentIds)
                ->whereHas('summative', function ($query) use ($classroomSubjectIds) {
                    $query->whereIn('classroom_subject_id', $classroomSubjectIds);
                })
                ->with(['summative.summativeType', 'summative.classroomSubject'])
                ->get()
                ->groupBy('student_id')
                ->map(function ($scoresByStudent) {
                    return $scoresByStudent->groupBy('summative.classroom_subject_id');
                });


            // 5. Persiapan Tabel PhpWord
            $tablePhpWord = new PhpWord();
            $section = $tablePhpWord->addSection(['marginLeft' => 0, 'marginRight' => 0, 'marginTop' => 0, 'marginBottom' => 0]);

            // Definisikan Gaya
            $headerStyle = ['bold' => true, 'size' => 8];
            $bodyStyle = ['size' => 8];
            $cellCentered = ['alignment' => 'center', 'valign' => 'center'];
            $cellLeft = ['alignment' => 'left', 'valign' => 'center'];
            $tableStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 50, 'alignment' => 'center'];

            // Definisikan lebar kolom
            $colWidths = [
                'no' => 400,
                'nomor_induk' => 1000,
                'nisn' => 1000,
                'nama' => 2600,
                'mapel' => 500, // Lebar untuk setiap kolom mapel
                'na' => 600,
                'peringkat' => 700,
            ];

            $wordTable = $section->addTable($tableStyle);

            // 6. Buat Baris Header Tabel (Dua baris)
            // -- Baris Header 1 --
            $wordTable->addRow(400);
            $wordTable->addCell($colWidths['no'], ['vMerge' => 'restart', 'valign' => 'center'])->addText('No', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidths['nomor_induk'], ['vMerge' => 'restart', 'valign' => 'center'])->addText('Nomor Induk', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidths['nisn'], ['vMerge' => 'restart', 'valign' => 'center'])->addText('NISN', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidths['nama'], ['vMerge' => 'restart', 'valign' => 'center'])->addText('Nama Siswa', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidths['mapel'] * $classroomSubjects->count(), ['gridSpan' => $classroomSubjects->count(), 'valign' => 'center'])->addText('Nilai Akhir', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidths['na'], ['vMerge' => 'restart', 'valign' => 'center'])->addText('NA', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidths['peringkat'], ['vMerge' => 'restart', 'valign' => 'center'])->addText('Peringkat', $headerStyle, $cellCentered);

            // -- Baris Header 2 --
            $wordTable->addRow(400);
            $wordTable->addCell(null, ['vMerge' => 'continue']);
            $wordTable->addCell(null, ['vMerge' => 'continue']);
            $wordTable->addCell(null, ['vMerge' => 'continue']);
            $wordTable->addCell(null, ['vMerge' => 'continue']);
            // Loop DINAMIS melalui $classroomSubjects untuk nama mapel
            foreach ($classroomSubjects as $cs) {
                $subjectName = $cs->subject->name;
                $abbr = match ($subjectName) {
                    'Pendidikan Agama Islam' => 'PAI',
                    'Pendidikan Pancasila' => 'Pancasila',
                    'Bahasa Indonesia' => 'BI',
                    'Matematika' => 'MM',
                    'Ilmu Pengetahuan Alam dan Sosial' => 'IPAS',
                    'Bahasa Arab' => 'Arab',
                    "Hibdzil Do'a" => "Do'a",
                    'Hadist' => 'Hadist',
                    "Tahsin Qur'an (Wafa)" => 'Tahsin',
                    "Tahfidz Qur'an" => 'Tahfidz',
                    'Seni Musik' => 'Musik',
                    'Pendidikan Jasmani, Olahraga dan Kesehatan' => 'PJOK',
                    'Bahasa Inggris' => 'Inggris',
                    'Bahasa Rejang' => 'Rejang',
                    default => substr($subjectName, 0, 5) // Fallback
                };
                $wordTable->addCell($colWidths['mapel'], $cellCentered)->addText($abbr, $headerStyle, $cellCentered);
            }
            $wordTable->addCell(null, ['vMerge' => 'continue']);
            $wordTable->addCell(null, ['vMerge' => 'continue']);


            // 7. Hitung Nilai dan Peringkat untuk SEMUA Siswa
            $studentFinalData = [];
            foreach ($classroomStudents as $cs) {
                $student_id = $cs->student_id;
                $studentScores = [];
                $validSubjectScoresForNA = [];

                $scoresForThisStudent = $allStudentScores->get($student_id, new Collection());

                foreach ($classroomSubjects as $subject) {
                    $classroom_subject_id = $subject->id;
                    $scoresForThisSubject = $scoresForThisStudent->get($classroom_subject_id, new Collection());

                    $allTypeMeans = [];
                    $scoresByType = $scoresForThisSubject->groupBy('summative.summativeType.name');

                    foreach ($scoresByType as $typeName => $scoresInType) {
                        $validScoresInType = $scoresInType->pluck('value')->filter(fn($s) => !is_null($s) && $s >= 0)->map(fn($s) => (int)$s);
                        $mean = $validScoresInType->avg() ?? 0;
                        if ($mean > 0) {
                            $allTypeMeans[] = $mean;
                        }
                    }

                    $subjectFinalScore = count($allTypeMeans) > 0 ? round(array_sum($allTypeMeans) / count($allTypeMeans)) : 0;
                    $studentScores[$classroom_subject_id] = $subjectFinalScore;

                    if ($subjectFinalScore > 0) {
                        $validSubjectScoresForNA[] = $subjectFinalScore;
                    }
                }

                $na = count($validSubjectScoresForNA) > 0 ? round(array_sum($validSubjectScoresForNA) / count($validSubjectScoresForNA)) : 0;

                $studentFinalData[$student_id] = [
                    'student' => $cs->student,
                    'scores' => $studentScores,
                    'na' => $na,
                    'rank' => 0
                ];
            }

            // Hitung Peringkat
            $nasCollection = collect($studentFinalData)->map(function ($data) {
                return $data['na'];
            });
            $nas = $nasCollection->sortDesc()->all(); // Sort DESC

            $prevNa = -1;
            $studentsAtRank = 0;
            $currentRank = 0;

            foreach ($nas as $student_id => $na) {
                if ($na != $prevNa) {
                    $currentRank += $studentsAtRank;
                    $currentRank++;
                    $studentsAtRank = 1;
                } else {
                    $studentsAtRank++;
                }
                // Pastikan student_id ada di $studentFinalData sebelum menetapkan rank
                if (isset($studentFinalData[$student_id])) {
                    $studentFinalData[$student_id]['rank'] = $currentRank;
                }
                $prevNa = $na;
            }


            // 8. Loop data siswa dan buat Baris Data Tabel
            foreach ($studentFinalData as $student_id => $finalData) {
                $student = $finalData['student'];

                $wordTable->addRow();
                $index = $classroomStudents->search(function ($item) use ($student_id) {
                    return $item->student_id === $student_id;
                });

                $wordTable->addCell($colWidths['no'], $cellCentered)->addText($index + 1, $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidths['nomor_induk'], $cellCentered)->addText('-', $bodyStyle, $cellCentered); // Ganti jika ada nomor induk
                $wordTable->addCell($colWidths['nisn'], $cellCentered)->addText($student->nisn ?? '-', $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidths['nama'], $cellLeft)->addText($student->name ?? '', $bodyStyle, $cellLeft);

                // Loop kolom mapel (harus urut)
                foreach ($classroomSubjects as $subject) {
                    $classroom_subject_id = $subject->id;
                    $score = $finalData['scores'][$classroom_subject_id] ?? 0;
                    $wordTable->addCell($colWidths['mapel'], $cellCentered)->addText($score > 0 ? $score : '-', $bodyStyle, $cellCentered);
                }

                $wordTable->addCell($colWidths['na'], $cellCentered)->addText($finalData['na'] > 0 ? $finalData['na'] : '-', $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidths['peringkat'], $cellCentered)->addText($finalData['rank'] > 0 ? $finalData['rank'] : '-', $bodyStyle, $cellCentered);
            }

            // 9. Ekstraksi XML Tabel
            Log::info('Tabel Nilai Akhir Kelas berhasil dibuat. Memulai ekstraksi XML.');

            $tempTableFile = tempnam(sys_get_temp_dir(), 'phpword_table_na');
            $xmlWriter = IOFactory::createWriter($tablePhpWord, 'Word2007');
            $xmlWriter->save($tempTableFile);

            $zip = new \ZipArchive();
            if ($zip->open($tempTableFile) === true) {
                $fullXml = $zip->getFromName('word/document.xml');
                $zip->close();
                unlink($tempTableFile);
            } else {
                throw new \Exception('Gagal memproses XML tabel NA (ZipArchive).');
            }

            if (!$fullXml) {
                throw new \Exception('Gagal memproses XML tabel NA (XML kosong).');
            }

            $tableXml = '';
            // Ekstrak HANYA konten tabel
            if (preg_match('/<w:tbl>(.*?)<\/w:tbl>/s', $fullXml, $matches)) {
                $tableXml = '<w:tbl>' . $matches[1] . '</w:tbl>';
                $tableXml = preg_replace('/<w:sectPr\b[^>]*>(.*?)<\/w:sectPr>/s', '', $tableXml);
            } else {
                // Fallback: Coba ekstrak body jika tabel tidak terdeteksi (meski seharusnya ada)
                Log::warning('Tag <w:tbl> tidak ditemukan, mencoba ekstrak <w:body> sebagai fallback.');
                if (preg_match('/<w:body(.*?)>(.*)<\/w:body>/s', $fullXml, $matchesBody)) {
                    $bodyContent = $matchesBody[2];
                    $tableXml = preg_replace('/<w:sectPr\s*.*?\s*\/w:sectPr>/s', '', $bodyContent);
                    $tableXml = preg_replace('/<w:lastRenderedPageBreak\s*\/>/', '', $tableXml);
                    $tableXml = trim($tableXml);
                } else {
                    throw new \Exception('Gagal memproses XML tabel NA (preg_match tbl dan body gagal).');
                }
            }

            // 10. Injeksi XML Tabel ke Template Utama
            $templateProcessor->setValue('tabel_nilai_akhir', $tableXml); // Sesuaikan nama placeholder

            // 11. Generate nama file dan Simpan
            $className = str_replace([' ', '/', '\\'], '_', $classroom->name);
            $filename = 'Nilai_Akhir_Kelas_' . $className . '.docx';
            $tempPath = storage_path('app/temp');
            File::ensureDirectoryExists($tempPath);
            $tempFilePath = $tempPath . '/' . uniqid('nilai_akhir_kelas_', true) . '.docx';
            $templateProcessor->saveAs($tempFilePath);

            Log::info('Nilai Akhir Kelas (XML Injected) berhasil disimpan.', [
                'tempFilePath' => $tempFilePath,
                'filename' => $filename
            ]);

            return response()
                ->download($tempFilePath, $filename)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Gagal membuat Nilai Akhir Kelas (XML Injected)!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            // Jangan throw $e jika ingin menampilkan pesan error di redirect
            return redirect()->back()->with('error', 'Gagal membuat dokumen Nilai Akhir Kelas: ' . $e->getMessage());
            // throw $e; // Gunakan ini jika ingin error ditampilkan di halaman error Laravel
        }
    }
}