import { Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';
import { id as indonesiaLocale } from 'date-fns/locale';

import DetailItem from '@/components/detail-item';
import SectionTitle from '@/components/section-title';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years';
import { type Student } from '@/types/models/students';
import { BookMarked, Pencil } from 'lucide-react';

const genderLabels = { male: 'Laki-laki', female: 'Perempuan' };
const religionLabels = {
    muslim: 'Islam',
    christian: 'Kristen Protestan',
    catholic: 'Kristen Katolik',
    hindu: 'Hindu',
    buddhist: 'Buddha',
    other: 'Lainnya',
};

// --- Props Halaman ---
interface ShowProps {
    schoolAcademicYear: SchoolAcademicYear;
    student: Student;
}

export default function Show({ schoolAcademicYear, student }: ShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        { title: 'Siswa', href: route('protected.school-academic-years.students.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        {
            title: student.name,
            href: route('protected.school-academic-years.students.show', { schoolAcademicYear: schoolAcademicYear.id, student: student.id }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Siswa: ${student.name}`} />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <Link
                                href={route('protected.school-academic-years.students.subjects', {
                                    schoolAcademicYear: schoolAcademicYear.id,
                                    student: student.id,
                                })}
                            >
                                <Button variant="outline" size="sm">
                                    <BookMarked className="mr-2 h-4 w-4" />
                                    Lihat Mata Pelajaran
                                </Button>
                            </Link>
                            <Link
                                href={route('protected.school-academic-years.students.edit', {
                                    schoolAcademicYear: schoolAcademicYear.id,
                                    student: student.id,
                                })}
                            >
                                <Button variant="outline" size="sm">
                                    <Pencil className="mr-2 h-4 w-4" />
                                    Edit
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-x-6 gap-y-8 md:grid-cols-2">
                            {/* Data Diri Siswa */}
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

                            {/* Data Orang Tua */}
                            <SectionTitle title="Data Orang Tua" />
                            <DetailItem label="Nama Ayah" value={student.parent?.father_name} />
                            <DetailItem label="Pekerjaan Ayah" value={student.parent?.father_job} />
                            <DetailItem label="Nama Ibu" value={student.parent?.mother_name} />
                            <DetailItem label="Pekerjaan Ibu" value={student.parent?.mother_job} />
                            <DetailItem label="Alamat Orang Tua" value={student.parent?.address} className="md:col-span-2" />

                            {/* Data Wali */}
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
