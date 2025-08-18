import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Student } from '@/types/models/students';
import { Head } from '@inertiajs/react';
import StudentsForm from './_components/students-form';

interface EditProps {
    schoolAcademicYear: SchoolAcademicYear;
    student: Student;
}

export default function Edit({ schoolAcademicYear, student }: EditProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        { title: 'Siswa', href: route('protected.school-academic-years.students.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        {
            title: 'Edit',
            href: route('protected.school-academic-years.students.edit', { schoolAcademicYear: schoolAcademicYear.id, student: student.id }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Siswa: ${student.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <StudentsForm schoolAcademicYear={schoolAcademicYear} student={student} />
                </div>
            </div>
        </AppLayout>
    );
}
