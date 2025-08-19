import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { ClassroomSubjectsPaginated } from '@/types/models/classroom-subjects';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { ClassroomSubjectsTable } from './_components/classroom-subjects-table';

interface IndexProps {
    classroomSubjects: ClassroomSubjectsPaginated;
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
}

export default function Index({ classroomSubjects, schoolAcademicYear, classroom }: IndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        { title: 'Kelas', href: route('protected.school-academic-years.classrooms.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        {
            title: classroom.name,
            href: route('protected.school-academic-years.classrooms.show', { schoolAcademicYear: schoolAcademicYear.id, classroom: classroom.id }),
        },
        {
            title: 'Mata Pelajaran',
            href: route('protected.school-academic-years.classrooms.subjects.index', {
                schoolAcademicYear: schoolAcademicYear.id,
                classroom: classroom.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Mapel di Kelas ${classroom.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <Link
                        href={route('protected.school-academic-years.classrooms.subjects.create', {
                            schoolAcademicYear: schoolAcademicYear.id,
                            classroom: classroom.id,
                        })}
                    >
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Tambah Mata Pelajaran
                        </Button>
                    </Link>

                    <ClassroomSubjectsTable classroomSubjects={classroomSubjects} schoolAcademicYear={schoolAcademicYear} classroom={classroom} />
                </div>
            </div>
        </AppLayout>
    );
}
