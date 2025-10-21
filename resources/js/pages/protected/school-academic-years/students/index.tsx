import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { StudentsPaginated } from '@/types/models/students';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { StudentsTable } from './_components/students-table';

interface IndexProps {
    students: StudentsPaginated;
    schoolAcademicYear: SchoolAcademicYear;
}

export default function Index({ students, schoolAcademicYear }: IndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dasbor', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }) },
        { title: 'Siswa', href: route('protected.school-academic-years.students.index', { schoolAcademicYear: schoolAcademicYear.id }) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Siswa" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <Link href={route('protected.school-academic-years.students.create', { schoolAcademicYear: schoolAcademicYear.id })}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Tambah Siswa
                        </Button>
                    </Link>

                    <StudentsTable students={students} schoolAcademicYear={schoolAcademicYear} />
                </div>
            </div>
        </AppLayout>
    );
}
