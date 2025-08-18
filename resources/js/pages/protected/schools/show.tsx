// resources/js/Pages/protected/schools/show.tsx

import { Head, Link } from '@inertiajs/react';

import DetailItem from '@/components/detail-item';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type School } from '@/types/models/schools';
import { CalendarClock, Pencil } from 'lucide-react';

export default function Show({ school }: { school: School }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Schools',
            href: route('protected.schools.index'),
        },
        {
            title: school.name, // Judul adalah nama sekolah
            href: route('protected.schools.show', school.id),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Sekolah: ${school.name}`} />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex gap-2">
                                <Link href={route('protected.schools.academic-years.index', school.id)}>
                                    <Button variant="outline" size="sm">
                                        <CalendarClock className="mr-2 h-4 w-4" />
                                        Tahun Ajaran
                                    </Button>
                                </Link>
                            </div>
                            <div className="flex gap-2">
                                <Link href={route('protected.schools.edit', school.id)}>
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
                            <DetailItem label="Nama" value={school.name} className="md:col-span-2" />
                            <DetailItem label="Alamat" value={school.address} className="md:col-span-2" />
                            <DetailItem label="NPSN" value={school.npsn} />
                            <DetailItem label="Kode Pos" value={school.postal_code} />
                            <DetailItem label="Website" value={school.website} />
                            <DetailItem label="Email" value={school.email} />
                            <DetailItem label="Tempat & Tanggal Rapor" value={school.place_date_raport} />
                            <DetailItem label="Tempat & Tanggal STS" value={school.place_date_sts} />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
