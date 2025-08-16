// Di file: resources/js/pages/protected/schools/academic-years/create.tsx

import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type AcademicYear } from '@/types/models/academic-years';
import { type School } from '@/types/models/schools';
import { Head } from '@inertiajs/react';
import AcademicYearForm from './_components/academic-year-form';

interface CreateProps {
    school: School;
    academicYears: AcademicYear[];
}

export default function Create({ school, academicYears }: CreateProps) {
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
            title: 'Create',
            href: route('protected.schools.academic-years.create', { school: school.id }),
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Tambah Tahun Ajaran - ${school.name}`} />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <AcademicYearForm school={school} academicYears={academicYears} />
                </div>
            </div>
        </AppLayout>
    );
}
