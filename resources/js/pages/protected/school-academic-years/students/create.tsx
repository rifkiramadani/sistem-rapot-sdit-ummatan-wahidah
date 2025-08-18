import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Head } from '@inertiajs/react';
import StudentsForm from './_components/students-form';

interface CreateProps {
    schoolAcademicYear: SchoolAcademicYear;
}

export default function Create({ schoolAcademicYear }: CreateProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        { title: 'Siswa', href: route('protected.school-academic-years.students.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        { title: 'Tambah', href: route('protected.school-academic-years.students.create', { schoolAcademicYear: schoolAcademicYear.id }) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tambah Siswa" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <StudentsForm schoolAcademicYear={schoolAcademicYear} />
                </div>
            </div>
        </AppLayout>
    );
}
