import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { StudentsPaginated } from '@/types/models/students';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { ClassroomStudentsTable } from './_components/classroom-students-table';

interface IndexProps {
    students: StudentsPaginated;
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
}

export default function Index({ students, schoolAcademicYear, classroom }: IndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }) },
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
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Siswa di Kelas ${classroom.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <Link
                        href={route('protected.school-academic-years.classrooms.students.create', {
                            schoolAcademicYear: schoolAcademicYear.id,
                            classroom: classroom.id,
                        })}
                    >
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Tambah Siswa ke Kelas {classroom.name}
                        </Button>
                    </Link>

                    <ClassroomStudentsTable students={students} schoolAcademicYear={schoolAcademicYear} classroom={classroom} />
                </div>
            </div>
        </AppLayout>
    );
}
