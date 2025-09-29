import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { ClassroomSubject } from '@/types/models/classroom-subjects';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Subject } from '@/types/models/subjects';
import { SummativeType } from '@/types/models/summative-types';
import { Head } from '@inertiajs/react';
import SummativeForm from './_components/summatives-form';

interface CreateProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    subject: Subject;
    classroomSubject: ClassroomSubject;
    summativeTypes: SummativeType[];
}

export default function Create({ schoolAcademicYear, classroom, classroomSubject, summativeTypes }: CreateProps) {
    const subject = classroomSubject.subject!;

    const breadcrumbs: BreadcrumbItem[] = [
        // ... (buat breadcrumbs yang sesuai)
        {
            title: 'Sumatif',
            href: route('protected.school-academic-years.classrooms.subjects.summatives.index', { schoolAcademicYear, classroom, classroomSubject }),
        },
        { title: 'Tambah', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Tambah Sumatif untuk ${subject.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <SummativeForm
                        classroom={classroom}
                        schoolAcademicYear={schoolAcademicYear}
                        classroomSubject={classroomSubject}
                        subject={subject}
                        summativeTypes={summativeTypes}
                    />
                </div>
            </div>
        </AppLayout>
    );
}
