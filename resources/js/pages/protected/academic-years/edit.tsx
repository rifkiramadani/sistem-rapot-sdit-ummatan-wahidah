import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import AcademicYearsForm from './_components/academic-years-form';
import { AcademicYear } from '@/types/models/academic-years';

interface EditProps {
    academicYear: AcademicYear;
}


export default function Edit({ academicYear }: EditProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Academic Years',
            href: route('protected.academic-years.index'),
        },
        {
            title: `Edit ${academicYear.name}`,
            href: route('protected.academic-years.edit', { academicYear: academicYear.id }),
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Academic Years" />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <AcademicYearsForm academicYear={academicYear} />
                </div>
            </div>
        </AppLayout>
    );
}
