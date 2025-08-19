import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Teacher } from '@/types/models/teachers';
import { Head } from '@inertiajs/react';
import ClassroomsForm from './_components/classrooms-form';

interface EditProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    teachers: Teacher[];
}

export default function Edit({ schoolAcademicYear, classroom, teachers }: EditProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        { title: 'Kelas', href: route('protected.school-academic-years.classrooms.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        {
            title: 'Edit',
            href: route('protected.school-academic-years.classrooms.edit', { schoolAcademicYear: schoolAcademicYear.id, classroom: classroom.id }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Kelas: ${classroom.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <ClassroomsForm schoolAcademicYear={schoolAcademicYear} classroom={classroom} teachers={teachers} />
                </div>
            </div>
        </AppLayout>
    );
}
