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
        return $this->modifyReportCoverTemplate();
    }

    private function modifyReportCoverTemplate()
    {
        $templatePath = storage_path('app/templates/Sampul_Rapor.docx');

        try {
            // Check if template exists
            if (!file_exists($templatePath)) {
                $this->error("Template file not found at: {$templatePath}");
                $this->info("Please ensure the Sampul_Rapor.docx template file exists before running this command.");
                return 1;
            }

            // Load the template
            $templateProcessor = new TemplateProcessor($templatePath);

            // Set the placeholders that the controller expects for Report Cover
            // School Information
            $templateProcessor->setValue('nama_sekolah', '${nama_sekolah}');
            $templateProcessor->setValue('npsn', '${npsn}');
            $templateProcessor->setValue('alamat_sekolah', '${alamat_sekolah}');
            $templateProcessor->setValue('kode_pos', '${kode_pos}');
            $templateProcessor->setValue('website', '${website}');
            $templateProcessor->setValue('email', '${email}');

            // Student Information
            $templateProcessor->setValue('nama_siswa', '${nama_siswa}');
            $templateProcessor->setValue('nisn', '${nisn}');
            $templateProcessor->setValue('nis', '${nis}');
            $templateProcessor->setValue('jenis_kelamin', '${jenis_kelamin}');
            $templateProcessor->setValue('tempat_lahir', '${tempat_lahir}');
            $templateProcessor->setValue('tanggal_lahir', '${tanggal_lahir}');
            $templateProcessor->setValue('agama', '${agama}');
            $templateProcessor->setValue('alamat_siswa', '${alamat_siswa}');
            $templateProcessor->setValue('foto_siswa', '${foto_siswa}');

            // Parent Information
            $templateProcessor->setValue('nama_ayah', '${nama_ayah}');
            $templateProcessor->setValue('pekerjaan_ayah', '${pekerjaan_ayah}');
            $templateProcessor->setValue('nama_ibu', '${nama_ibu}');
            $templateProcessor->setValue('pekerjaan_ibu', '${pekerjaan_ibu}');
            $templateProcessor->setValue('alamat_orang_tua', '${alamat_orang_tua}');

            // Guardian Information
            $templateProcessor->setValue('nama_wali', '${nama_wali}');
            $templateProcessor->setValue('pekerjaan_wali', '${pekerjaan_wali}');
            $templateProcessor->setValue('no_telp_wali', '${no_telp_wali}');
            $templateProcessor->setValue('alamat_wali', '${alamat_wali}');

            // Academic Information
            $templateProcessor->setValue('nama_kelas', '${nama_kelas}');
            $templateProcessor->setValue('tahun_ajaran', '${tahun_ajaran}');

            // Principal Information
            $templateProcessor->setValue('nama_kepala_sekolah', '${nama_kepala_sekolah}');
            $templateProcessor->setValue('nip_kepala_sekolah', '${nip_kepala_sekolah}');
            $templateProcessor->setValue('tanda_tangan_kepala_sekolah', '${tanda_tangan_kepala_sekolah}');

            // Save the modified template
            $templateProcessor->saveAs($templatePath);

            $this->info("Sampul Rapor template modified successfully!");
            $this->info("Placeholders added for:");
            $this->info("- School Information (nama_sekolah, npsn, alamat_sekolah, etc.)");
            $this->info("- Student Information (nama_siswa, nisn, foto_siswa, etc.)");
            $this->info("- Parent Information (nama_ayah, pekerjaan_ayah, etc.)");
            $this->info("- Guardian Information (nama_wali, pekerjaan_wali, etc.)");
            $this->info("- Academic Information (nama_kelas, tahun_ajaran)");
            $this->info("- Principal Information (nama_kepala_sekolah, nip_kepala_sekolah, tanda_tangan_kepala_sekolah)");

            return 0;

        } catch (\Exception $e) {
            $this->error("Error modifying Sampul Rapor template: " . $e->getMessage());
            $this->error("Make sure the template file exists and is readable.");
            return 1;
        }
    }
}