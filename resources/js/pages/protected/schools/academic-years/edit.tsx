// Di file: resources/js/pages/protected/schools/academic-years/edit.tsx

import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type AcademicYear } from '@/types/models/academic-years';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years';
import { type School } from '@/types/models/schools';
import { Head } from '@inertiajs/react';
import AcademicYearForm from './_components/academic-year-form';

// 1. Definisikan props untuk halaman Edit
interface EditProps {
    school: School;
    academicYears: AcademicYear[];
    schoolAcademicYear: SchoolAcademicYear; // Prop ini berisi data yang akan diedit
}

export default function Edit({ school, academicYears, schoolAcademicYear }: EditProps) {
    // 2. Perbarui breadcrumbs untuk halaman Edit
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
            title: 'Edit',
            href: route('protected.schools.academic-years.edit', {
                school: school.id,
                schoolAcademicYear: schoolAcademicYear.id,
            }),
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            {/* 3. Perbarui judul halaman */}
            <Head title={`Edit Tahun Ajaran - ${school.name}`} />

            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    {/* 4. Teruskan prop schoolAcademicYear ke form */}
                    <AcademicYearForm school={school} academicYears={academicYears} schoolAcademicYear={schoolAcademicYear} />
                </div>
            </div>
        </AppLayout>
    );
}
