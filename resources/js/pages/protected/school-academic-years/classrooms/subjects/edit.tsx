import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { ClassroomSubject } from '@/types/models/classroom-subjects';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Subject } from '@/types/models/subjects';
import { Head } from '@inertiajs/react';
import ClassroomSubjectForm from './_components/classroom-subjects-form';

interface EditProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    classroomSubject: ClassroomSubject;
    availableSubjects: Subject[];
}

export default function Edit({ schoolAcademicYear, classroom, classroomSubject, availableSubjects }: EditProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear }) },
        { title: 'Kelas', href: route('protected.school-academic-years.classrooms.index', { schoolAcademicYear }) },
        { title: classroom.name, href: route('protected.school-academic-years.classrooms.show', { schoolAcademicYear, classroom }) },
        { title: 'Mata Pelajaran', href: route('protected.school-academic-years.classrooms.subjects.index', { schoolAcademicYear, classroom }) },
        { title: 'Edit', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Mapel di Kelas ${classroom.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <ClassroomSubjectForm
                        schoolAcademicYear={schoolAcademicYear}
                        classroom={classroom}
                        availableSubjects={availableSubjects}
                        classroomSubject={classroomSubject}
                    />
                </div>
            </div>
        </AppLayout>
    );
}
