// resources/js/Pages/protected/school-academic-years/teachers/index.tsx

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { TeachersPaginated } from '@/types/models/teachers';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { TeachersTable } from './_components/teachers-table';

// Ganti interface props untuk menerima data paginasi
interface IndexProps {
    teachers: TeachersPaginated;
    schoolAcademicYear: SchoolAcademicYear;
}

export default function Index({ teachers, schoolAcademicYear }: IndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dasbor',
            href: route('protected.school-academic-years.dashboard.index', {
                schoolAcademicYear: schoolAcademicYear.id,
            }),
        },
        {
            title: 'Guru',
            href: route('protected.school-academic-years.teachers.index', {
                schoolAcademicYear: schoolAcademicYear.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Guru" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <Link
                        href={route('protected.school-academic-years.teachers.create', {
                            schoolAcademicYear: schoolAcademicYear.id,
                        })}
                    >
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Tambah Guru
                        </Button>
                    </Link>

                    <TeachersTable teachers={teachers} schoolAcademicYear={schoolAcademicYear} />
                </div>
            </div>
        </AppLayout>
    );
}
