// resources/js/Pages/protected/school-academic-years/teachers/edit.tsx

import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years';
import { type Teacher } from '@/types/models/teachers';
import { Head } from '@inertiajs/react';
import TeachersForm from './_components/teachers-form';

// Definisikan props untuk halaman Edit
interface EditProps {
    schoolAcademicYear: SchoolAcademicYear;
    teacher: Teacher; // Prop 'teacher' sekarang wajib ada
}

export default function Edit({ schoolAcademicYear, teacher }: EditProps) {
    // Definisikan breadcrumbs untuk navigasi halaman edit
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dasbor',
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
            title: 'Edit',
            href: route('protected.school-academic-years.teachers.edit', {
                schoolAcademicYear: schoolAcademicYear.id,
                teacher: teacher.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Guru" />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    {/* Render komponen form, kali ini dengan prop 'teacher' */}
                    <TeachersForm schoolAcademicYear={schoolAcademicYear} teacher={teacher} />
                </div>
            </div>
        </AppLayout>
    );
}
