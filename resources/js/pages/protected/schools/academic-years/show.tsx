// Di file: resources/js/pages/protected/schools/academic-years/show.tsx

import { Head, Link } from '@inertiajs/react';
import { Pencil } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type AcademicYear } from '@/types/models/academic-years';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years';
import { type School } from '@/types/models/schools';
import { format } from 'date-fns';

// Komponen helper untuk menampilkan baris data, dimodifikasi agar bisa menerima children
function DetailItem({
    label,
    value,
    className,
    children,
}: {
    label: string;
    value?: string | number | null;
    className?: string;
    children?: React.ReactNode;
}) {
    return (
        <div className={className}>
            <p className="text-sm font-medium text-muted-foreground">{label}</p>
            {children ? <div className="text-base font-semibold">{children}</div> : <p className="text-base font-semibold">{value || '-'}</p>}
        </div>
    );
}

// Props untuk halaman Show, dengan asumsi data academic_year disertakan
interface ShowProps {
    school: School;
    schoolAcademicYear: SchoolAcademicYear & {
        academic_year: AcademicYear;
    };
}

export default function Show({ school, schoolAcademicYear }: ShowProps) {
    // Ekstrak data tahun ajaran untuk kemudahan akses
    const { academic_year: academicYear } = schoolAcademicYear;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Schools',
            href: route('protected.schools.index'),
        },
        {
            title: school.name,
            href: route('protected.schools.show', { school: school.id }),
        },
        {
            title: 'Academic Years',
            href: route('protected.schools.academic-years.index', { school: school.id }),
        },
        {
            title: academicYear.name,
            href: route('protected.schools.academic-years.show', {
                school: school.id,
                schoolAcademicYear: schoolAcademicYear.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Tahun Ajaran: ${academicYear.name}`} />

            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-end">
                            <Link
                                href={route('protected.schools.academic-years.edit', {
                                    school: school.id,
                                    schoolAcademicYear: schoolAcademicYear.id,
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
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <DetailItem label="Nama Tahun Ajaran" value={academicYear.name} className="md:col-span-2" />

                            <DetailItem label="Tanggal Mulai" value={format(new Date(academicYear.start), 'dd MMMM yyyy')} />
                            <DetailItem label="Tanggal Selesai" value={format(new Date(academicYear.end), 'dd MMMM yyyy')} />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
