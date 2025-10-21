import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { ClassroomsPaginated } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { ClassroomsTable } from './_components/classrooms-table';

interface IndexProps {
    classrooms: ClassroomsPaginated;
    schoolAcademicYear: SchoolAcademicYear;
    isTeacher?: boolean;
}

export default function Index({ classrooms, schoolAcademicYear, isTeacher = false }: IndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        { title: 'Kelas', href: route('protected.school-academic-years.classrooms.index', { schoolAcademicYear: schoolAcademicYear.id }) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kelas" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    {!isTeacher && (
                        <Link href={route('protected.school-academic-years.classrooms.create', { schoolAcademicYear: schoolAcademicYear.id })}>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Tambah Kelas
                            </Button>
                        </Link>
                    )}

                    <ClassroomsTable classrooms={classrooms} schoolAcademicYear={schoolAcademicYear} isTeacher={isTeacher} />
                </div>
            </div>
        </AppLayout>
    );
}
