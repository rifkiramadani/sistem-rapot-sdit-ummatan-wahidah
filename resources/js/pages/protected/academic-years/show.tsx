// resources/js/Pages/protected/schools/show.tsx

import { Head, Link, router } from '@inertiajs/react';

import DetailItem from '@/components/detail-item';
import SectionTitle from '@/components/section-title';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type AcademicYear } from '@/types/models/academic-years';
import { format } from 'date-fns';
import { Eye, Pencil } from 'lucide-react';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';

export default function Show({ academicYear, schoolAcademicYear }: { academicYear: AcademicYear, schoolAcademicYear: SchoolAcademicYear }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Academic Years',
            href: route('protected.academic-years.index'),
        },
        {
            title: academicYear.name, // Judul adalah nama sekolah
            href: route('protected.academic-years.show', academicYear.id),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Tahun Ajaran: ${academicYear.name}`} />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex gap-2">
                                <Button
                                    variant="outline"
                                    size="icon"
                                    onClick={() =>
                                        router.get(
                                            route('protected.schools.academic-years.show', {
                                                school: schoolAcademicYear.school_id,
                                                schoolAcademicYear: schoolAcademicYear.id,
                                            }),
                                        )
                                    }
                                >
                                    <Eye className="h-4 w-4" />
                                </Button>
                            </div>
                            <div className="flex gap-2">
                                <Link href={route('protected.academic-years.edit', academicYear.id)}>
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
                            <SectionTitle title="Data Tahun Ajaran" />
                            <DetailItem label="Nama" value={academicYear.name} className="md:col-span-2" />
                            <DetailItem label="Mulai" value={format(academicYear.start, 'dd MMMM yyyy')} />
                            <DetailItem label="Selesai" value={format(academicYear.end, 'dd MMMM yyyy')} />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
