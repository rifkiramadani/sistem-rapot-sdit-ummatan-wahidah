import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Teacher } from '@/types/models/teachers';
import { Head } from '@inertiajs/react';

interface IndexProps {
    teachers: Teacher[];
    schoolAcademicYear: SchoolAcademicYear;
}
export default function Index({ teachers, schoolAcademicYear }: IndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: route('protected.school-academic-years.dashboard.index', {
                schoolAcademicYear: schoolAcademicYear.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <pre>{JSON.stringify(schoolAcademicYear, null, 2)}</pre>
                </div>
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <pre>{JSON.stringify(teachers, null, 2)}</pre>
                </div>
            </div>
        </AppLayout>
    );
}
