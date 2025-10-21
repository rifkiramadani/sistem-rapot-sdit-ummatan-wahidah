import { Head, Link, router } from '@inertiajs/react';
import { format, parse } from 'date-fns';
import { id as indonesiaLocale } from 'date-fns/locale';
import { useState } from 'react';
import { CalendarIcon } from 'lucide-react';
import { cn } from '@/lib/utils';

import DetailItem from '@/components/detail-item';
import SectionTitle from '@/components/section-title';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type ClassroomStudent } from '@/types/models/classroom-students';
import { type Classroom } from '@/types/models/classrooms';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Student } from '@/types/models/students';
import { BookCopy, Pencil, FileText, Download, FileCheck, FileSignature } from 'lucide-react';

const genderLabels = { male: 'Laki-laki', female: 'Perempuan' };
const religionLabels = {
    muslim: 'Islam',
    christian: 'Kristen Protestan',
    catholic: 'Kristen Katolik',
    hindu: 'Hindu',
    buddhist: 'Buddha',
    other: 'Lainnya',
};

interface ShowProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    classroomStudent: ClassroomStudent & {
        student: Student;
    };
}

export default function Show({ schoolAcademicYear, classroom, classroomStudent }: ShowProps) {
    // Ekstrak data siswa agar lebih mudah diakses
    const student = classroomStudent.student;

    // State untuk modals
    const [transferDialogOpen, setTransferDialogOpen] = useState(false);
    const [reportCardDialogOpen, setReportCardDialogOpen] = useState(false);

    // State transferForm sekarang mencakup transfer_date
    const [transferForm, setTransferForm] = useState({
        transfer_date: undefined as Date | undefined, // Menampung objek Date
        transfer_reason: '',
        destination_school: '',
        destination_city: '',
    });
    const [selectedSemester, setSelectedSemester] = useState('');

    // Handler untuk transfer certificate (sudah diperbarui)
    const handleTransferCertificate = () => {
        // Baca tanggal dari transferForm
        const formattedDate = transferForm.transfer_date
            ? format(transferForm.transfer_date, 'yyyy-MM-dd')
            : '';

        const params = new URLSearchParams({
            transfer_date: formattedDate,
            transfer_reason: transferForm.transfer_reason,
            destination_school: transferForm.destination_school,
            destination_city: transferForm.destination_city,
        });

        const url = route('protected.school-academic-years.classrooms.students.export-transfer-certificate', {
            schoolAcademicYear: schoolAcademicYear.id,
            classroom: classroom.id,
            classroomStudent: classroomStudent.id,
        }) + '?' + params.toString();

        // Open in new window for download
        window.open(url, '_blank');

        // Close dialog and reset form
        setTransferDialogOpen(false);
        // Reset semua field di transferForm, termasuk transfer_date
        setTransferForm({
            transfer_date: undefined,
            transfer_reason: '',
            destination_school: '',
            destination_city: '',
        });
    };

    // Handler untuk report card
    const handleReportCard = () => {
        if (!selectedSemester) {
            return;
        }

        const url = route('protected.school-academic-years.classrooms.students.export-report-card', {
            schoolAcademicYear: schoolAcademicYear.id,
            classroom: classroom.id,
            classroomStudent: classroomStudent.id,
        }) + '?semester_id=' + selectedSemester;

        // Open in new window for download
        window.open(url, '_blank');

        // Close dialog and reset form
        setReportCardDialogOpen(false);
        setSelectedSemester('');
    };

    // Helper function for STS export
    const handleStsExport = () => {
        const url = route('protected.school-academic-years.classrooms.students.export-sts', {
            schoolAcademicYear: schoolAcademicYear.id,
            classroom: classroom.id,
            classroomStudent: classroomStudent.id,
        });

        // Open in new window for download
        window.open(url, '_blank');
    };

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        { title: 'Kelas', href: route('protected.school-academic-years.classrooms.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        {
            title: classroom.name,
            href: route('protected.school-academic-years.classrooms.show', { schoolAcademicYear: schoolAcademicYear.id, classroom: classroom.id }),
        },
        {
            title: 'Daftar Siswa',
            href: route('protected.school-academic-years.classrooms.students.index', {
                schoolAcademicYear: schoolAcademicYear.id,
                classroom: classroom.id,
            }),
        },
        { title: student.name, href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Siswa: ${student.name}`} />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            {/* Tombol edit tetap mengarah ke halaman edit siswa utama */}
                            <div className="flex flex-wrap items-center gap-2">
                                <Link
                                    href={route('protected.school-academic-years.students.edit', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        student: student.id,
                                    })}
                                >
                                    <Button variant="outline" size="sm">
                                        <Pencil className="mr-2 h-4 w-4" />
                                        Edit Profil Siswa
                                    </Button>
                                </Link>
                                <Link
                                    href={route('protected.school-academic-years.classrooms.students.summatives', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        classroom: classroom.id,
                                        classroomStudent: classroomStudent.id,
                                    })}
                                >
                                    <Button variant="outline" size="sm">
                                        <BookCopy className="mr-2 h-4 w-4" />
                                        Lihat Sumatif
                                    </Button>
                                </Link>
                                <Link
                                    href={route('protected.school-academic-years.classrooms.students.export-cover', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        classroom: classroom.id,
                                        classroomStudent: classroomStudent.id,
                                    })}
                                >
                                    <Button variant="outline" size="sm">
                                        <FileText className="mr-2 h-4 w-4" />
                                        Export Sampul Rapor
                                    </Button>
                                </Link>

                                {/* Transfer Certificate Dialog (Sudah diperbaiki) */}
                                {/* Transfer Certificate Dialog (SUDAH DIPERBAIKI) */}
                                <Dialog open={transferDialogOpen} onOpenChange={setTransferDialogOpen}>
                                    <DialogTrigger asChild>
                                        <Button variant="outline" size="sm">
                                            <FileSignature className="mr-2 h-4 w-4" />
                                            Export Surat Pindah
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent className="sm:max-w-[500px]">
                                        <DialogHeader>
                                            <DialogTitle>Export Surat Keterangan Pindah Sekolah</DialogTitle>
                                            <DialogDescription>
                                                Isi data yang diperlukan untuk membuat Surat Keterangan Pindah Sekolah.
                                            </DialogDescription>
                                        </DialogHeader>
                                        <div className="grid gap-4 py-4">
                                            <div className="grid gap-2">
                                                <Label htmlFor="transfer_date">
                                                    Tanggal Pindah <span className="text-red-500">*</span>
                                                </Label>

                                                {/* MODIFIED: Tambahkan modal={true} di sini */}
                                                <Popover modal={true}>
                                                    <PopoverTrigger asChild>
                                                        <Button
                                                            variant="outline"
                                                            className={cn(
                                                                "w-full justify-start text-left font-normal",
                                                                !transferForm.transfer_date && "text-muted-foreground"
                                                            )}
                                                        >
                                                            <CalendarIcon className="mr-2 h-4 w-4" />
                                                            {transferForm.transfer_date
                                                                ? format(transferForm.transfer_date, "PPP", { locale: indonesiaLocale })
                                                                : "Pilih tanggal"}
                                                        </Button>
                                                    </PopoverTrigger>
                                                    <PopoverContent className="w-auto p-0" align="start">
                                                        <Calendar
                                                            mode="single"
                                                            selected={transferForm.transfer_date}
                                                            onSelect={(date) =>
                                                                setTransferForm({ ...transferForm, transfer_date: date })
                                                            }
                                                            initialFocus
                                                            disabled={(date) => date > new Date()}
                                                            locale={indonesiaLocale}
                                                        />
                                                    </PopoverContent>
                                                </Popover>
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="transfer_reason">
                                                    Alasan Pindah <span className="text-red-500">*</span>
                                                </Label>
                                                <Textarea
                                                    id="transfer_reason"
                                                    placeholder="Masukkan alasan pindah sekolah"
                                                    value={transferForm.transfer_reason}
                                                    onChange={(e) => setTransferForm({ ...transferForm, transfer_reason: e.target.value })}
                                                    required
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="destination_school">Sekolah Tujuan</Label>
                                                <Input
                                                    id="destination_school"
                                                    placeholder="Nama sekolah tujuan (opsional)"
                                                    value={transferForm.destination_school}
                                                    onChange={(e) => setTransferForm({ ...transferForm, destination_school: e.target.value })}
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="destination_city">Kota Tujuan</Label>
                                                <Input
                                                    id="destination_city"
                                                    placeholder="Kota tujuan (opsional)"
                                                    value={transferForm.destination_city}
                                                    onChange={(e) => setTransferForm({ ...transferForm, destination_city: e.target.value })}
                                                />
                                            </div>
                                        </div>
                                        <DialogFooter>
                                            <Button variant="outline" onClick={() => setTransferDialogOpen(false)}>
                                                Batal
                                            </Button>
                                            <Button
                                                onClick={handleTransferCertificate}
                                                disabled={!transferForm.transfer_date || !transferForm.transfer_reason}
                                                type="button"
                                            >
                                                <Download className="mr-2 h-4 w-4" />
                                                Export Surat Pindah
                                            </Button>
                                        </DialogFooter>
                                    </DialogContent>
                                </Dialog>

                                {/* Report Card Dialog */}
                                <Dialog open={reportCardDialogOpen} onOpenChange={setReportCardDialogOpen}>
                                    <DialogTrigger asChild>
                                        <Button variant="outline" size="sm">
                                            <FileCheck className="mr-2 h-4 w-4" />
                                            Export Rapor Akhir
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent className="sm:max-w-[400px]">
                                        <DialogHeader>
                                            <DialogTitle>Export Rapor Akhir</DialogTitle>
                                            <DialogDescription>
                                                Pilih semester untuk rapor yang akan diekspor.
                                            </DialogDescription>
                                        </DialogHeader>
                                        <div className="grid gap-4 py-4">
                                            <div className="grid gap-2">
                                                <Label htmlFor="semester">
                                                    Pilih Semester <span className="text-red-500">*</span>
                                                </Label>
                                                <Select value={selectedSemester} onValueChange={setSelectedSemester}>
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Pilih semester" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="1">Semester 1</SelectItem>
                                                        <SelectItem value="2">Semester 2</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>
                                        <DialogFooter>
                                            <Button variant="outline" onClick={() => setReportCardDialogOpen(false)}>
                                                Batal
                                            </Button>
                                            <Button
                                                onClick={handleReportCard}
                                                disabled={!selectedSemester}
                                                type="button"
                                            >
                                                <Download className="mr-2 h-4 w-4" />
                                                Export Rapor
                                            </Button>
                                        </DialogFooter>
                                    </DialogContent>
                                </Dialog>

                                {/* STS Export Button */}
                                <Button
                                    variant="outline"
                                    size="sm"
                                    type="button"
                                    onClick={handleStsExport}
                                >
                                    <FileText className="mr-2 h-4 w-4" />
                                    Export Data STS
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-x-6 gap-y-8 md:grid-cols-2">
                            <SectionTitle title="Data Diri Siswa" />
                            <DetailItem label="NISN" value={student.nisn} />
                            <DetailItem label="Nama Lengkap" value={student.name} />
                            <DetailItem label="Jenis Kelamin" value={genderLabels[student.gender]} />
                            <DetailItem label="Agama" value={religionLabels[student.religion]} />
                            <DetailItem
                                label="Tempat, Tanggal Lahir"
                                value={`${student.birth_place}, ${format(new Date(student.birth_date), 'dd MMMM yyyy', { locale: indonesiaLocale })}`}
                            />
                            <DetailItem label="Alamat Siswa" value={student.address} className="md:col-span-2" />

                            <SectionTitle title="Data Orang Tua" />
                            <DetailItem label="Nama Ayah" value={student.parent?.father_name} />
                            <DetailItem label="Pekerjaan Ayah" value={student.parent?.father_job} />
                            <DetailItem label="Nama Ibu" value={student.parent?.mother_name} />
                            <DetailItem label="Pekerjaan Ibu" value={student.parent?.mother_job} />
                            <DetailItem label="Alamat Orang Tua" value={student.parent?.address} className="md:col-span-2" />

                            <SectionTitle title="Data Wali" />
                            <DetailItem label="Nama Wali" value={student.guardian?.name} />
                            <DetailItem label="Pekerjaan Wali" value={student.guardian?.job} />
                            <DetailItem label="No. Telp Wali" value={student.guardian?.phone_number} />
                            <DetailItem label="Alamat Wali" value={student.guardian?.address} className="md:col-span-2" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
