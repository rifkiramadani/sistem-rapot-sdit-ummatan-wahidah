import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { SchoolsPaginated } from '@/types/models/schools';
import { Head } from '@inertiajs/react';
import { SchoolsTable } from './_components/schools-table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schools',
        href: route('protected.schools.index'),
    },
];

interface IndexProps {
    schools: SchoolsPaginated;
}
export default function Index({ schools }: IndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Schools" />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <SchoolsTable schools={schools} />
                </div>
            </div>
        </AppLayout>
    );
}
