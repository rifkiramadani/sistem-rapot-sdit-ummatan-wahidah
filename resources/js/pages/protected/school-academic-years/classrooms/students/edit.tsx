import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { ClassroomStudent } from '@/types/models/classroom-students';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Student } from '@/types/models/students';
import { Head } from '@inertiajs/react';
import ClassroomStudentForm from './_components/classroom-students-form';

interface EditProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    classroomStudent: ClassroomStudent;
    availableStudents: Student[];
}

export default function Edit({ schoolAcademicYear, classroom, classroomStudent, availableStudents }: EditProps) {
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
        { title: 'Edit', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Siswa di Kelas ${classroom.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <ClassroomStudentForm
                        schoolAcademicYear={schoolAcademicYear}
                        classroom={classroom}
                        availableStudents={availableStudents}
                        classroomStudent={classroomStudent}
                    />
                </div>
            </div>
        </AppLayout>
    );
}
