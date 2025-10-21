// resources/js/Pages/protected/school-academic-years/teachers/index.tsx

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { SubjectsTable } from './_components/subjects-table';
import { SubjectsPaginated } from '@/types/models/subjects';

// Ganti interface props untuk menerima data paginasi
interface IndexProps {
    subjects: SubjectsPaginated;
    schoolAcademicYear: SchoolAcademicYear;
}

export default function Index({ subjects, schoolAcademicYear }: IndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dasbor',
            href: route('protected.school-academic-years.dashboard.index', {
                schoolAcademicYear: schoolAcademicYear.id,
            }),
        },
        {
            title: 'Subject',
            href: route('protected.school-academic-years.subjects.index', {
                schoolAcademicYear: schoolAcademicYear.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Subjects" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <Link
                        href={route('protected.school-academic-years.subjects.create', {
                            schoolAcademicYear: schoolAcademicYear.id,
                        })}
                    >
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Tambah Subject
                        </Button>
                    </Link>

                    <SubjectsTable subjects={subjects} schoolAcademicYear={schoolAcademicYear} />
                </div>
            </div>
        </AppLayout>
    );
}
