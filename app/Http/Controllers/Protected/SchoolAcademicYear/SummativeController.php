<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Enums\DefaultSummativeTypeEnum;
use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\SchoolAcademicYear;
use App\Models\StudentSummative;
use App\Models\Summative;
use App\Models\SummativeType;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log; // <-- PASTIKAN INI ADA
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\LogBatch;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;

class SummativeController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name,identifier',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $classroomSubject->load('subject');

        $query = $classroomSubject->summatives()
            ->with('summativeType')
            ->orderBy('created_at', 'asc');

        $summatives = QueryBuilder::for($query)
            ->through([
                Filter::class,
                Sort::class,
            ])
            ->paginate();


        return Inertia::render('protected/school-academic-years/classrooms/subjects/summatives/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
            'summatives' => $summatives,
        ]);
    }

    public function create(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        $classroomSubject->load('subject');
        $summativeTypes = $schoolAcademicYear->summativeTypes()->orderBy('name')->get();

        return Inertia::render('protected/school-academic-years/classrooms/subjects/summatives/create', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
            'summativeTypes' => $summativeTypes,
        ]);
    }

    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        $validated = $this->validateSummative($request, $schoolAcademicYear);

        $classroomSubject->summatives()->create($validated);

        return redirect()->route('protected.school-academic-years.classrooms.subjects.summatives.index', [$schoolAcademicYear, $classroom, $classroomSubject])
            ->with('success', 'Sumatif berhasil dibuat.');
    }

    public function edit(SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject, Summative $summative)
    {
        if ($summative->classroom_subject_id !== $classroomSubject->id) {
            abort(403);
        }

        $classroomSubject->load('subject');
        $summativeTypes = $schoolAcademicYear->summativeTypes()->orderBy('name')->get();

        return Inertia::render('protected/school-academic-years/classrooms/subjects/summatives/edit', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
            'summativeTypes' => $summativeTypes,
            'summative' => $summative,
        ]);
    }

    public function update(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject, Summative $summative)
    {
        if ($summative->classroom_subject_id !== $classroomSubject->id) {
            abort(403);
        }

        $validated = $this->validateSummative($request, $schoolAcademicYear);

        $summative->update($validated);

        return redirect()->route('protected.school-academic-years.classrooms.subjects.summatives.index', [$schoolAcademicYear, $classroom, $classroomSubject])
            ->with('success', 'Sumatif berhasil diperbarui.');
    }

    public function values(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        $classroomSubject->load('subject');

        // 1. Ambil semua siswa di kelas ini
        $students = $classroom->classroomStudents()->with('student')->get()->pluck('student');

        // 2. Ambil semua sumatif untuk mata pelajaran ini, diurutkan
        $summatives = $classroomSubject->summatives()->with('summativeType')->orderBy('created_at', 'asc')->get();

        // 3. Ambil semua nilai siswa yang relevan dalam satu query
        $studentIds = $students->pluck('id');
        $summativeIds = $summatives->pluck('id');

        $scores = StudentSummative::whereIn('student_id', $studentIds)
            ->whereIn('summative_id', $summativeIds)
            ->get()
            ->keyBy(function ($item) {
                return $item->student_id . '-' . $item->summative_id;
            });

        // 4. Transformasi data menjadi struktur yang diinginkan frontend
        $studentData = $students->map(function ($student) use ($summatives, $scores) {
            $studentSummatives = [];
            $allScores = []; // Untuk menghitung Nilai Rapor (NR)

            // Kelompokkan sumatif berdasarkan jenisnya
            $summativesByType = $summatives->groupBy('summativeType.name');

            foreach ($summativesByType as $typeName => $typeSummatives) {
                $values = $typeSummatives->map(function ($summative) use ($student, $scores) {
                    $score = $scores->get($student->id . '-' . $summative->id);
                    return [
                        'id' => $summative->id,
                        'name' => $summative->name,
                        'identifier' => $summative->identifier,
                        'score' => $score ? (int) $score->value : null,
                    ];
                });

                $validScores = $values->pluck('score')->filter(fn($s) => !is_null($s));
                $mean = $validScores->avg() ?? 0;
                $allScores[] = $mean;

                $studentSummatives[$typeName] = [
                    'values' => $values,
                    'mean' => round($mean, 1),
                ];
            }

            // Menentukan deskripsi
            $materiScores = collect($studentSummatives[DefaultSummativeTypeEnum::MATERI->value]['values'] ?? []);
            $highestScore = $materiScores->whereNotNull('score')->sortByDesc('score')->first();
            $lowestScore = $materiScores->whereNotNull('score')->sortBy('score')->first();

            $highestSummative = $highestScore ? $summatives->find($highestScore['id']) : null;
            $lowestSummative = $lowestScore ? $summatives->find($lowestScore['id']) : null;

            return [
                'id' => $student->id,
                'nisn' => $student->nisn,
                'name' => $student->name,
                'nr' => round(collect($allScores)->avg() ?? 0),
                'summatives' => $studentSummatives,
                'description' => [
                    'Materi Unggul' => $highestSummative->name ?? null,
                    'Materi Kurang' => $lowestSummative->name ?? null,
                    'Materi Paling Menonjol' => $highestSummative->prominent ?? null,
                    'Materi Yang Perlu Ditingkatkan' => $lowestSummative->improvement ?? null,
                ],
            ];
        });

        return Inertia::render('protected/school-academic-years/classrooms/subjects/summatives/values', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'classroom' => $classroom,
            'classroomSubject' => $classroomSubject,
            'studentSummativeValues' => $studentData, // <-- Kirim data yang sudah di-transformasi
        ]);
    }

    private function getStudentSummativeData(Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        $students = $classroom->classroomStudents()->with('student')->get()->pluck('student');
        $summatives = $classroomSubject->summatives()->with('summativeType')->orderBy('created_at', 'asc')->get();
        $studentIds = $students->pluck('id');
        $summativeIds = $summatives->pluck('id');

        $scores = StudentSummative::whereIn('student_id', $studentIds)
            ->whereIn('summative_id', $summativeIds)
            ->get()
            ->keyBy(fn($item) => $item->student_id . '-' . $item->summative_id);

        return $students->map(function ($student) use ($summatives, $scores) {
            $studentSummatives = [];
            $allScores = [];
            $summativesByType = $summatives->groupBy('summativeType.name');

            foreach ($summativesByType as $typeName => $typeSummatives) {
                $values = $typeSummatives->map(function ($summative) use ($student, $scores) {
                    $score = $scores->get($student->id . '-' . $summative->id);
                    return [
                        'id' => $summative->id,
                        'name' => $summative->name,
                        'identifier' => $summative->identifier,
                        'score' => $score ? (int) $score->value : null,
                    ];
                });

                $validScores = $values->pluck('score')->filter(fn($s) => !is_null($s));
                $mean = $validScores->avg() ?? 0;
                $allScores[] = $mean;

                $studentSummatives[$typeName] = [
                    'values' => $values,
                    'mean' => round($mean, 1),
                ];
            }

            $materiScores = collect($studentSummatives[DefaultSummativeTypeEnum::MATERI->value]['values'] ?? []);
            $highestScore = $materiScores->whereNotNull('score')->sortByDesc('score')->first();
            $lowestScore = $materiScores->whereNotNull('score')->sortBy('score')->first();
            $highestSummative = $highestScore ? $summatives->find($highestScore['id']) : null;
            $lowestSummative = $lowestScore ? $summatives->find($lowestScore['id']) : null;

            return (object) [ // Menggunakan object agar lebih mudah diakses
                'id' => $student->id,
                'nisn' => $student->nisn,
                'name' => $student->name,
                'nr' => round(collect($allScores)->avg() ?? 0),
                'summatives' => (object) $studentSummatives,
                'description' => (object) [
                    'Materi Unggul' => $highestSummative->name ?? null,
                    'Materi Kurang' => $lowestSummative->name ?? null,
                    'Materi Paling Menonjol' => $highestSummative->prominent ?? null,
                    'Materi Yang Perlu Ditingkatkan' => $lowestSummative->improvement ?? null,
                ],
            ];
        });
    }


    /**
     * Mengambil data dan memanggil generator dokumen.
     */
    public function exportWord(SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        // LOG 1: Mencatat dimulainya proses ekspor
        Log::info('Memulai ekspor Word sumatif.', [
            'classroomSubjectId' => $classroomSubject->id,
            'className' => $classroom->name,
        ]);

        // 1. Ambil data
        $studentData = $this->getStudentSummativeData($classroom, $classroomSubject);

        // Cek jika data kosong, kembalikan dengan pesan error
        if ($studentData->isEmpty()) {
            Log::warning('Ekspor Word dibatalkan: Tidak ada data siswa yang ditemukan.', [
                'classroomSubjectId' => $classroomSubject->id,
            ]);
            return redirect()->route('protected.school-academic-years.classrooms.subjects.summatives.values', [$schoolAcademicYear, $classroom, $classroomSubject])
                ->with('error', 'Tidak ada data siswa untuk diekspor.');
        }

        // 2. Panggil fungsi private untuk membuat dan mengembalikan dokumen
        return $this->generateRekapNilaiDocument(
            $studentData,
            $classroomSubject,
            $classroom,
            $schoolAcademicYear
        );
    }

    /**
     * Fungsi private yang fokus hanya untuk membuat dokumen Word.
     */
    /**
     * Fungsi private yang fokus hanya untuk membuat dokumen Word.
     */
    private function generateRekapNilaiDocument($studentData, $classroomSubject, $classroom, $schoolAcademicYear)
    {
        try {
            // LOG 2: Mencatat dimulainya pembuatan dokumen
            Log::info('Memulai pembuatan dokumen Word dengan PHPWord.', [
                'totalStudents' => $studentData->count(),
            ]);

            // 1. Muat template
            $templatePath = storage_path('app/templates/template_rekap_nilai.docx');
            if (!file_exists($templatePath)) {
                // LOG 3: Mencatat kegagalan file template
                Log::error('Template Word tidak ditemukan!', ['path' => $templatePath]);
                throw new \Exception('File template Word tidak ditemukan di server.');
            }
            $templateProcessor = new TemplateProcessor($templatePath);

            // 2. Isi placeholder sederhana
            $classroomSubject->load('subject');
            $templateProcessor->setValue('nama_mapel', $classroomSubject->subject->name);
            $templateProcessor->setValue('nama_kelas', $classroom->name);
            $templateProcessor->setValue('tahun_ajaran', $schoolAcademicYear->year . ' Semester ' . $schoolAcademicYear->academicYear->name);

            // 3. Persiapan Tabel PhpWord
            $tablePhpWord = new PhpWord();
            $section = $tablePhpWord->addSection();

            // Gaya Font
            $headerStyle = ['bold' => true, 'bgColor' => 'F2F2F2', 'size' => 8]; // FONT HEADER (8)
            $bodyStyle = ['size' => 8]; // FONT BODY STANDAR (8)
            $descStyle = ['size' => 7]; // FONT DESKRIPSI LEBIH KECIL (7)
            $nrStyle = ['bold' => true, 'size' => 9]; // FONT NR (9)

            // Gaya Sel
            $cellVCentered = ['valign' => 'center'];
            $cellCentered = ['alignment' => 'center', 'valign' => 'center'];
            $cellMerge = ['vMerge' => 'restart', 'valign' => 'center'];
            $cellMergeContinue = ['vMerge' => 'continue'];

            $wordTable = $section->addTable([
                'borderSize'  => 6,
                'borderColor' => '000000',
                'cellMargin'  => 50, // Margin Sel Dikecilkan
                'alignment'   => 'center',
                'width'       => 10000,
                'unit'        => 'pct',
                'allowOverlap' => true,
                'cellSpacing' => 0,
            ]);

            // Lebar Kolom yang Dioptimalkan (dalam TWIP, 1000 TWIP = 1 inci)
            $colWidth_No = 500;
            $colWidth_NISN = 1000;
            $colWidth_Name = 2000;
            $colWidth_Score = 800; // Untuk Nilai Individual (S1, S2, dst.)
            $colWidth_Mean = 800;  // Untuk Rata-rata (NA, (S))
            $colWidth_NR = 800;
            $colWidth_Desc = 3500; // Untuk Deskripsi

            // Data untuk dinamisasi header
            $sampleStudent = $studentData->first();
            $summativeKeys = array_keys((array)$sampleStudent->summatives);
            $descriptionKeys = array_keys((array)$sampleStudent->description);

            // Ambil data detail sumatif (untuk Baris Header 2 & 3)
            $detailedSummatives = [];
            foreach ($summativeKeys as $key) {
                $summative = $sampleStudent->summatives->$key;
                $isMateri = str_contains($key, DefaultSummativeTypeEnum::MATERI->value);

                if ($isMateri) {
                    // Untuk MATERI, kelompokkan berdasarkan identifier
                    $materiGroups = collect($summative['values'])->groupBy('identifier');
                    foreach ($materiGroups as $label => $values) {
                        $detailedSummatives[$key]['groups'][] = (object)['label' => ucfirst($label), 'span' => $values->count()];
                        foreach ($values as $value) {
                            $detailedSummatives[$key]['cols'][] = $value['name']; // Nama sumatif individual
                        }
                    }
                } else {
                    // Untuk non-MATERI, setiap item adalah kolom
                    foreach ($summative['values'] as $value) {
                        $detailedSummatives[$key]['groups'][] = (object)['label' => strtoupper($value['name']), 'span' => 1];
                        $detailedSummatives[$key]['cols'][] = null; // Tidak ada sub-header di Baris 3
                    }
                }
                // Tambahkan kolom rata-rata/nilai akhir (NA/S)
                $detailedSummatives[$key]['groups'][] = (object)['label' => $isMateri ? '(S)' : 'NA', 'span' => 1];
                $detailedSummatives[$key]['cols'][] = null; // Tidak ada sub-header di Baris 3
            }


            // --- Baris Header 1: Jenis Sumatif & Deskripsi Umum ---
            $wordTable->addRow(400); // Tinggi baris dikurangi
            $wordTable->addCell($colWidth_No, $cellMerge)->addText('No', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidth_NISN, $cellMerge)->addText('Nomor Induk', $headerStyle, $cellCentered);
            $wordTable->addCell($colWidth_Name, $cellMerge)->addText('Nama Siswa', $headerStyle, $cellCentered);

            // Kolom Dinamis (Sumatif)
            foreach ($summativeKeys as $key) {
                $summative = $sampleStudent->summatives->$key;
                $colSpan = count($summative['values']) + 1;
                $wordTable->addCell(null, ['gridSpan' => $colSpan, 'valign' => 'center'])->addText(strtoupper($key), $headerStyle, $cellCentered);
            }

            $wordTable->addCell($colWidth_NR, $cellMerge)->addText('NR', $headerStyle, $cellCentered);
            $wordTable->addCell(null, ['gridSpan' => count($descriptionKeys), 'valign' => 'center'])->addText('Deskripsi', $headerStyle, $cellCentered);


            // --- Baris Header 2: Sub-Pengelompokan & Nama Deskripsi ---
            $wordTable->addRow(400); // Tinggi baris dikurangi
            $wordTable->addCell(null, $cellMergeContinue);
            $wordTable->addCell(null, $cellMergeContinue);
            $wordTable->addCell(null, $cellMergeContinue);

            foreach ($summativeKeys as $key) {
                foreach ($detailedSummatives[$key]['groups'] as $group) {
                    $isFinalCol = str_ends_with($group->label, ')');
                    // Hitung lebar berdasarkan jumlah kolom skor yang dicakup
                    $colWidth = $isFinalCol ? $colWidth_Mean : ($colWidth_Score * $group->span);
                    $cellOptions = $isFinalCol ? $cellMerge : ['gridSpan' => $group->span, 'valign' => 'center', 'width' => $colWidth];
                    $wordTable->addCell($colWidth, $cellOptions)->addText($group->label, $headerStyle, $cellCentered);
                }
            }

            $wordTable->addCell(null, $cellMergeContinue);
            foreach ($descriptionKeys as $key) {
                $wordTable->addCell($colWidth_Desc, $cellMerge)->addText($key, $headerStyle, $cellCentered);
            }


            // --- Baris Header 3: Nama Sumatif Individual (Hanya untuk Jenis MATERI) ---
            $wordTable->addRow(400); // Tinggi baris dikurangi
            $wordTable->addCell(null, $cellMergeContinue);
            $wordTable->addCell(null, $cellMergeContinue);
            $wordTable->addCell(null, $cellMergeContinue);

            foreach ($summativeKeys as $key) {
                $isMateri = str_contains($key, DefaultSummativeTypeEnum::MATERI->value);
                foreach ($detailedSummatives[$key]['cols'] as $colName) {
                    if ($isMateri) {
                        $wordTable->addCell($colWidth_Score)->addText($colName, $headerStyle, $cellCentered);
                    } else {
                        $wordTable->addCell(null, $cellMergeContinue);
                    }
                }
                $wordTable->addCell(null, $cellMergeContinue);
            }

            $wordTable->addCell(null, $cellMergeContinue);
            foreach ($descriptionKeys as $key) {
                $wordTable->addCell(null, $cellMergeContinue);
            }


            // --- Baris Data (Body Rows) ---
            foreach ($studentData as $index => $student) {
                $wordTable->addRow();
                $wordTable->addCell($colWidth_No)->addText($index + 1, $bodyStyle, $cellCentered);
                $wordTable->addCell($colWidth_NISN)->addText($student->nisn, $bodyStyle, $cellVCentered);
                $wordTable->addCell($colWidth_Name)->addText($student->name, $bodyStyle, $cellVCentered);

                foreach ($summativeKeys as $key) {
                    $summativeData = $student->summatives->$key;
                    // Nilai sumatif individu
                    foreach ($summativeData['values'] as $value) {
                        $wordTable->addCell($colWidth_Score)->addText($value['score'] ?? '-', $bodyStyle, $cellCentered);
                    }
                    // Nilai rata-rata per jenis
                    $wordTable->addCell($colWidth_Mean)->addText($summativeData['mean'], $nrStyle, $cellCentered);
                }

                // Nilai Rapor (NR)
                $wordTable->addCell($colWidth_NR)->addText($student->nr, $nrStyle, $cellCentered);

                // Deskripsi
                foreach ($descriptionKeys as $key) {
                    $deskripsiText = $student->description->$key ?? '-';
                    // Pastikan sel deskripsi menggunakan lebar khusus
                    $wordTable->addCell($colWidth_Desc)->addText($deskripsiText, $descStyle, $cellVCentered);
                }
            }


            // LOG 4: Mencatat akhir dari pembuatan tabel
            Log::info('Tabel nilai berhasil dibuat. Memproses ke XML dengan ekstraksi file zip.');

            // 4. Proses tabel ke XML dan set nilai placeholder
            // --- AWAL KODE EKSTRAKSI FILE ZIP (PALING ANDAL) ---

            $tempTableFile = tempnam(sys_get_temp_dir(), 'phpword_table');
            $xmlWriter = IOFactory::createWriter($tablePhpWord, 'Word2007');
            $xmlWriter->save($tempTableFile);

            $zip = new \ZipArchive();
            if ($zip->open($tempTableFile) === true) {
                $fullXml = $zip->getFromName('word/document.xml');
                $zip->close();
                unlink($tempTableFile);
            } else {
                Log::error('Gagal membuka file Word sementara sebagai zip.');
                throw new \Exception('Gagal memproses XML tabel untuk injeksi. File zip Word corrupt.');
            }

            if (!$fullXml) {
                Log::error('Gagal membaca word/document.xml dari file zip.');
                throw new \Exception('Gagal memproses XML tabel untuk injeksi. Konten XML kosong.');
            }

            // Ekstraksi konten antara <w:body> dan </w:body>
            $tableXml = '';
            if (preg_match('/<w:body(.*?)>(.*)<\/w:body>/s', $fullXml, $matches)) {
                $bodyContent = $matches[2];

                // 1. Hapus Section Properties yang pasti merusak template.
                $tableXml = preg_replace('/<w:sectPr\s*.*?\s*\/w:sectPr>/s', '', $bodyContent);

                // 2. Hapus tag <w:lastRenderedPageBreak/> yang tidak perlu
                $tableXml = preg_replace('/<w:lastRenderedPageBreak\s*\/>/', '', $tableXml);

                $tableXml = trim($tableXml);
            } else {
                Log::error('Gagal mengekstrak body XML dari document.xml menggunakan zip. Pola masih gagal.');
                throw new \Exception('Gagal memproses XML tabel untuk injeksi. Struktur body XML tidak terdeteksi.');
            }

            // Set nilai placeholder dengan XML tabel yang sudah diekstrak dan dibersihkan
            $templateProcessor->setValue('tabel_nilai', $tableXml);

            // --- AKHIR KODE EKSTRAKSI FILE ZIP ---

            // 5. Simpan ke file sementara
            $filename = 'rekap-nilai-' . str_replace(' ', '_', $classroomSubject->subject->name) . '-' . str_replace(' ', '_', $classroom->name) . '.docx';
            $tempPath = storage_path('app/temp');
            File::ensureDirectoryExists($tempPath);
            $tempFilePath = $tempPath . '/' . uniqid('rekap_nilai_', true) . '.docx';
            $templateProcessor->saveAs($tempFilePath);

            // LOG 5: Mencatat file sementara berhasil disimpan
            Log::info('Dokumen sementara berhasil disimpan. Mempersiapkan respons download.', [
                'tempFilePath' => $tempFilePath,
                'filename' => $filename
            ]);

            // 6. Kembalikan sebagai response download
            return response()
                ->download($tempFilePath, $filename)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            // LOG 6: Mencatat pengecualian
            Log::error('Gagal membuat dokumen Word!', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'classroomSubjectId' => $classroomSubject->id,
            ]);

            report($e);
            return redirect()->back()->with('error', 'Gagal membuat dokumen: ' . $e->getMessage());
        }
    }

    private function getTableXmlContent(PhpWord $phpWordInstance): string
    {
        // Gunakan TemplateProcessor untuk membaca output writer
        $tempFile = tempnam(sys_get_temp_dir(), 'phpword');
        $xmlWriter = IOFactory::createWriter($phpWordInstance, 'Word2007');
        $xmlWriter->save($tempFile);

        // Muat file yang dihasilkan ke dalam TemplateProcessor
        $templateProcessor = new TemplateProcessor($tempFile);

        // TemplateProcessor secara internal menyimpan XML konten body sebagai properti,
        // kita bisa mengaksesnya melalui metode yang sudah ada di library PhpWord, yaitu getXml().
        $fullXml = $templateProcessor->get = $templateProcessor->getXml();

        // Hapus file temporary
        unlink($tempFile);

        // Ekstraksi konten <w:body> (lebih andal dengan XML yang dimuat PhpWord)
        if (preg_match('/<w:body(.*?)>(.*)<\/w:body>/s', $fullXml, $matches)) {
            $bodyContent = $matches[2];

            // Membersihkan tag konflik (<w:sectPr> dan <w:lastRenderedPageBreak/>)
            $tableXml = preg_replace('/<w:sectPr\s*.*?\s*\/w:sectPr>/s', '', $bodyContent);
            $tableXml = preg_replace('/<w:lastRenderedPageBreak\s*\/>/', '', $tableXml);

            return trim($tableXml);
        }

        return '';
    }

    public function updateValue(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        // 1. Validasi data yang masuk
        $validated = $request->validate([
            'student_id' => ['required', 'ulid', Rule::exists('students', 'id')],
            'summative_id' => [
                'required',
                'ulid',
                // Pastikan summative_id yang dikirim benar-benar milik classroomSubject ini
                Rule::exists('summatives', 'id')->where('classroom_subject_id', $classroomSubject->id)
            ],
            'value' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        // 2. Gunakan updateOrCreate untuk efisiensi
        // Mencari berdasarkan student_id dan summative_id
        // Memperbarui atau membuat dengan 'value' yang baru
        $studentSummative = StudentSummative::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'summative_id' => $validated['summative_id'],
            ],
            [
                'value' => $validated['value'],
            ]
        );

        // 3. Kirim respons JSON yang berhasil
        return response()->json([
            'message' => 'Nilai berhasil disimpan.',
            'data' => $studentSummative,
        ]);
    }


    public function destroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject, Summative $summative)
    {
        if ($summative->classroom_subject_id !== $classroomSubject->id) {
            abort(403);
        }

        $summative->delete();

        return redirect()->route('protected.school-academic-years.classrooms.subjects.summatives.index', [$schoolAcademicYear, $classroom, $classroomSubject])
            ->with('success', 'Data sumatif berhasil dihapus.');
    }


    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear, Classroom $classroom, ClassroomSubject $classroomSubject)
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:summatives,id'],
        ]);

        LogBatch::startBatch();

        DB::transaction(function () use ($request, $classroomSubject) {
            Summative::where('classroom_subject_id', $classroomSubject->id)
                ->whereIn('id', $request->input('ids'))
                ->get()
                ->each->delete();
        });

        LogBatch::endBatch();

        return redirect()->route('protected.school-academic-years.classrooms.subjects.summatives.index', [$schoolAcademicYear, $classroom, $classroomSubject])
            ->with('success', 'Data sumatif yang dipilih berhasil dihapus.');
    }

    /**
     * Method privat untuk menampung aturan validasi sumatif.
     */
    private function validateSummative(Request $request, SchoolAcademicYear $schoolAcademicYear): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'summative_type_id' => [
                'required',
                'ulid',
                Rule::exists('summative_types', 'id')->where('school_academic_year_id', $schoolAcademicYear->id),
            ],
            'identifier' => [
                Rule::requiredIf(function () use ($request) {
                    $type = SummativeType::find($request->input('summative_type_id'));
                    return $type && $type->name === DefaultSummativeTypeEnum::MATERI->value;
                }),
                'nullable',
                'string',
                'max:255',
            ],
            'prominent' => [
                Rule::requiredIf(function () use ($request) {
                    $type = SummativeType::find($request->input('summative_type_id'));
                    return $type && $type->name === DefaultSummativeTypeEnum::MATERI->value;
                }),
                'nullable',
                'string',
            ],
            'improvement' => [
                Rule::requiredIf(function () use ($request) {
                    $type = SummativeType::find($request->input('summative_type_id'));
                    return $type && $type->name === DefaultSummativeTypeEnum::MATERI->value;
                }),
                'nullable',
                'string',
            ],
        ]);
    }
}