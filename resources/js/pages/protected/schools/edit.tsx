import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { School } from '@/types/models/schools';
import { Head } from '@inertiajs/react';
import SchoolsForm from './_components/schools-form';

interface EditProps {
    school: School;
}
export default function Edit({ school }: EditProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Schools',
            href: route('protected.schools.index'),
        },
        {
            title: `Edit ${school.name}`,
            href: route('protected.schools.edit', { school: school.id }),
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Schools" />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <SchoolsForm school={school} />
                </div>
            </div>
        </AppLayout>
    );
}
