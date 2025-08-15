// Di file: resources/js/pages/protected/schools/academic-years/index.tsx

import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Paginator } from '@/types';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { School } from '@/types/models/schools';
import { Head } from '@inertiajs/react';
import { AcademicYearsTable } from './_components/academic-years-table';

// Tipe untuk data yang dipaginasi
export type SchoolAcademicYearsPaginated = Paginator<SchoolAcademicYear>;

interface IndexProps {
    school: School;
    schoolAcademicYears: SchoolAcademicYearsPaginated;
}

export default function Index({ school, schoolAcademicYears }: IndexProps) {
    console.log(schoolAcademicYears);
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
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Tahun Ajaran ${school.name}`} />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    {/* <Link

                    // href={route('protected.schools.academic-years.create', { school: school.id })}
                    >
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Tambah Tahun Ajaran
                        </Button>
                    </Link> */}
                    <AcademicYearsTable schoolAcademicYears={schoolAcademicYears} />
                </div>
            </div>
        </AppLayout>
    );
}
