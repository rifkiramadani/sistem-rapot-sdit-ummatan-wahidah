// resources/js/Pages/protected/schools/show.tsx

import { Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type AcademicYear } from '@/types/models/academic-years';
import { Pencil } from 'lucide-react';
import { format } from 'date-fns'

// Komponen kecil untuk menampilkan baris data
function DetailItem({ label, value, className }: { label: string; value: string | number | null | undefined; className?: string }) {
    return (
        <div className={className}>
            <p className="text-sm font-medium text-muted-foreground">{label}</p>
            <p className="text-base font-semibold">{value || '-'}</p>
        </div>
    );
}

export default function Show({ academicYear }: { academicYear: AcademicYear }) {
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
                            {/* <div className="flex gap-2">
                                <Link href={route('protected.schools.academic-years.index', school.id)}>
                                    <Button variant="outline" size="sm">
                                        <CalendarClock className="mr-2 h-4 w-4" />
                                        Tahun Ajaran
                                    </Button>
                                </Link>
                            </div> */}
                            <div className="flex gap-2">
                                {/* <Link href={route('protected.academic-years.edit', academicYear.id)}>
                                </Link> */}
                                <Button variant="outline" size="sm">
                                    <Pencil className="mr-2 h-4 w-4" />
                                    Edit
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <DetailItem label="Nama" value={academicYear.name} className="md:col-span-2" />
                            <DetailItem label="Mulai" value={format(academicYear.start, 'yyyy')} className="md:col-span-2" />
                            <DetailItem label="Selesai" value={format(academicYear.end, 'yyyy')} />
                            {/* <DetailItem label="Kode Pos" value={school.postal_code} />
                            <DetailItem label="Website" value={school.website} />
                            <DetailItem label="Email" value={school.email} />
                            <DetailItem label="Tempat & Tanggal Rapor" value={school.place_date_raport} />
                            <DetailItem label="Tempat & Tanggal STS" value={school.place_date_sts} /> */}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
