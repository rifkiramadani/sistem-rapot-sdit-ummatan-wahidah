<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\TemplateProcessor;

class ModifyTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'template:modify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Modify the Word template to work with generateRekapNilaiDocument';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $templatePath = storage_path('app/templates/template_rekap_nilai.docx');

        try {
            // Check if template exists
            if (!file_exists($templatePath)) {
                $this->error("Template file not found at: {$templatePath}");
                $this->info("Please ensure the template file exists before running this command.");
                return 1;
            }

            // Load the template
            $templateProcessor = new TemplateProcessor($templatePath);

            // Set the placeholders that the controller expects
            $templateProcessor->setValue('nama_mapel', '${nama_mapel}');
            $templateProcessor->setValue('nama_kelas', '${nama_kelas}');
            $templateProcessor->setValue('tahun_ajaran', '${tahun_ajaran}');
            $templateProcessor->setValue('tabel_nilai', '${tabel_nilai}');

            // Save the modified template
            $templateProcessor->saveAs($templatePath);

            $this->info("Template modified successfully!");
            $this->info("Placeholders added:");
            $this->info("- nama_mapel: Subject name");
            $this->info("- nama_kelas: Classroom name");
            $this->info("- tahun_ajaran: Academic year");
            $this->info("- tabel_nilai: Scores table (will be replaced with XML)");

            return 0;

        } catch (\Exception $e) {
            $this->error("Error modifying template: " . $e->getMessage());
            $this->error("Make sure the template file exists and is readable.");
            return 1;
        }
    }
}