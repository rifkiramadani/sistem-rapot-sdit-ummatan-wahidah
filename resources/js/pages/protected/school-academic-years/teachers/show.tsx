// resources/js/Pages/protected/school-academic-years/teachers/show.tsx

import { Head, Link } from '@inertiajs/react';

import DetailItem from '@/components/detail-item';
import SectionTitle from '@/components/section-title';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years';
import { type Teacher } from '@/types/models/teachers';
import { BookMarked, Pencil } from 'lucide-react';

// Definisikan props untuk halaman Show
interface ShowProps {
    schoolAcademicYear: SchoolAcademicYear;
    teacher: Teacher;
}

export default function Show({ schoolAcademicYear, teacher }: ShowProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }),
        },
        {
            title: 'Guru',
            href: route('protected.school-academic-years.teachers.index', { schoolAcademicYear: schoolAcademicYear.id }),
        },
        {
            title: teacher.name, // Judul adalah nama guru
            href: route('protected.school-academic-years.teachers.show', { schoolAcademicYear: schoolAcademicYear.id, teacher: teacher.id }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Guru: ${teacher.name}`} />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            {/* Tombol Aksi di Kiri */}
                            <div className="flex gap-2">
                                <Button variant="outline" size="sm">
                                    <BookMarked className="mr-2 h-4 w-4" />
                                    Lihat Kelas (Segera)
                                </Button>
                            </div>
                            {/* Tombol Aksi di Kanan */}
                            <div className="flex gap-2">
                                <Link
                                    href={route('protected.school-academic-years.teachers.edit', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        teacher: teacher.id,
                                    })}
                                >
                                    <Button variant="outline" size="sm">
                                        <Pencil className="mr-2 h-4 w-4" />
                                        Edit
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <SectionTitle title="Data Diri Guru" />
                            <DetailItem label="Nama" value={teacher.name} />
                            <DetailItem label="Nomor Induk Yayasan (NIY)" value={teacher.niy} />
                            <DetailItem label="Email Akun" value={teacher.user?.email} />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
