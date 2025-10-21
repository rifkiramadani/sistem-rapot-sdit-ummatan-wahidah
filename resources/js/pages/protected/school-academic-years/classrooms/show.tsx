import { Head, Link } from '@inertiajs/react';

import DetailItem from '@/components/detail-item';
import SectionTitle from '@/components/section-title';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type Classroom } from '@/types/models/classrooms';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years';
import { BookCopy, Pencil, Users, Download } from 'lucide-react';

interface ShowProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom & {
        classroom_students: { student: { id: string; name: string } }[];
    };
}

export default function Show({ schoolAcademicYear, classroom }: ShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dasbor', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        { title: 'Kelas', href: route('protected.school-academic-years.classrooms.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        {
            title: classroom.name,
            href: route('protected.school-academic-years.classrooms.show', { schoolAcademicYear: schoolAcademicYear.id, classroom: classroom.id }),
        },
    ];


    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Kelas: ${classroom.name}`} />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex gap-2">
                                <Link
                                    href={route('protected.school-academic-years.classrooms.students.index', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        classroom: classroom.id,
                                    })}
                                >
                                    <Button variant="outline" size="sm">
                                        <Users className="mr-2 h-4 w-4" />
                                        Daftar Siswa
                                    </Button>
                                </Link>

                                <Link
                                    href={route('protected.school-academic-years.classrooms.subjects.index', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        classroom: classroom.id,
                                    })}
                                >
                                    <Button variant="outline" size="sm">
                                        <BookCopy className="mr-2 h-4 w-4" />
                                        Mata Pelajaran
                                    </Button>
                                </Link>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        const url = route('protected.school-academic-years.classrooms.export-final-grades', {
                                            schoolAcademicYear: schoolAcademicYear.id,
                                            classroom: classroom.id,
                                        });
                                        window.open(url, '_blank');
                                    }}
                                >
                                    <Download className="mr-2 h-4 w-4" />
                                    Export Nilai Akhir Kelas
                                </Button>
                            </div>
                            <Link
                                href={route('protected.school-academic-years.classrooms.edit', {
                                    schoolAcademicYear: schoolAcademicYear.id,
                                    classroom: classroom.id,
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
                            {/* Detail Kelas */}
                            <SectionTitle title="Detail Kelas" />
                            <DetailItem label="Nama Kelas" value={classroom.name} className="md:col-span-2" />
                            <DetailItem label="Wali Kelas" value={classroom.teacher?.name} />
                            <DetailItem label="Jumlah Siswa" value={`${classroom.classroom_students.length} siswa`} />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
