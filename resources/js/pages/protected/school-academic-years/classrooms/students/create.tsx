import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Student } from '@/types/models/students';
import { Head } from '@inertiajs/react';
import ClassroomStudentForm from './_components/classroom-students-form';

interface CreateProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    availableStudents: Student[];
}

export default function Create({ schoolAcademicYear, classroom, availableStudents }: CreateProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dasbor', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        { title: 'Kelas', href: route('protected.school-academic-years.classrooms.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        {
            title: classroom.name,
            href: route('protected.school-academic-years.classrooms.show', { schoolAcademicYear: schoolAcademicYear.id, classroom: classroom.id }),
        },
        {
            title: 'Daftar Siswa',
            href: route('protected.school-academic-years.classrooms.students.index', {
                schoolAcademicYear: schoolAcademicYear.id,
                classroom: classroom.id,
            }),
        },
        {
            title: 'Tambah',
            href: route('protected.school-academic-years.classrooms.students.create', {
                schoolAcademicYear: schoolAcademicYear.id,
                classroom: classroom.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Tambah Siswa ke Kelas ${classroom.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <ClassroomStudentForm schoolAcademicYear={schoolAcademicYear} classroom={classroom} availableStudents={availableStudents} />
                </div>
            </div>
        </AppLayout>
    );
}
