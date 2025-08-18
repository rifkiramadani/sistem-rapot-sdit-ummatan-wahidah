// resources/js/Pages/protected/school-academic-years/teachers/create.tsx

import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Head } from '@inertiajs/react';
import TeachersForm from './_components/teachers-form';

// Definisikan props untuk halaman Create
interface CreateProps {
    schoolAcademicYear: SchoolAcademicYear;
}

export default function Create({ schoolAcademicYear }: CreateProps) {
    // Definisikan breadcrumbs untuk navigasi
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: route('protected.school-academic-years.dashboard.index', {
                schoolAcademicYear: schoolAcademicYear.id,
            }),
        },
        {
            title: 'Guru',
            href: route('protected.school-academic-years.teachers.index', {
                schoolAcademicYear: schoolAcademicYear.id,
            }),
        },
        {
            title: 'Tambah',
            href: route('protected.school-academic-years.teachers.create', {
                schoolAcademicYear: schoolAcademicYear.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tambah Guru" />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    {/* Render komponen form dengan props yang diperlukan */}
                    <TeachersForm schoolAcademicYear={schoolAcademicYear} />
                </div>
            </div>
        </AppLayout>
    );
}
