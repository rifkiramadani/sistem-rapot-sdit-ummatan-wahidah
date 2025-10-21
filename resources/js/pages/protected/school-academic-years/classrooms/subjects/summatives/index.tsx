import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { ClassroomSubject } from '@/types/models/classroom-subjects';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { SummativesPaginated } from '@/types/models/summatives';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { SummativesTable } from './_components/summatives-table';

interface IndexProps {
    summatives: SummativesPaginated;
    classroomSubject: ClassroomSubject;
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    isTeacher?: boolean;
}

export default function Index({ summatives, classroomSubject, schoolAcademicYear, classroom, isTeacher = false }: IndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }),
        },
        {
            title: 'Kelas',
            href: route('protected.school-academic-years.classrooms.index', { schoolAcademicYear: schoolAcademicYear.id }),
        },
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
        {
            // Breadcrumb ini mengarah ke halaman detail mata pelajaran
            title: classroomSubject.subject!.name,
            href: route('protected.school-academic-years.classrooms.subjects.show', {
                schoolAcademicYear: schoolAcademicYear.id,
                classroom: classroom.id,
                classroomSubject: classroomSubject.id,
            }),
        },
        {
            // Breadcrumb ini adalah halaman saat ini
            title: 'Sumatif',
            href: route('protected.school-academic-years.classrooms.subjects.summatives.index', {
                schoolAcademicYear: schoolAcademicYear.id,
                classroom: classroom.id,
                classroomSubject: classroomSubject.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Mapel di Kelas ${classroom.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                        <Link
                            href={route('protected.school-academic-years.classrooms.subjects.summatives.create', {
                                schoolAcademicYear: schoolAcademicYear.id,
                                classroom: classroom.id,
                                classroomSubject: classroomSubject.id,
                            })}
                        >
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Tambah Sumatif
                            </Button>
                        </Link>

                        <SummativesTable
                            summatives={summatives}
                            schoolAcademicYear={schoolAcademicYear}
                            classroom={classroom}
                            classroomSubject={classroomSubject}
                            isTeacher={isTeacher}
                        />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
