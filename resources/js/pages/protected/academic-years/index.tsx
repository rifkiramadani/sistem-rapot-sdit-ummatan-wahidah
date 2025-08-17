// Di file: app/protected/academicYear/index.tsx

import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { AcademicYearsPaginated } from '@/types/models/academic-years';
import { Head, Link } from '@inertiajs/react';
import { AcademicYearTable } from './_components/academic-years-table';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Academic Years',
        href: route('protected.academic-years.index'),
    },
];

interface IndexProps {
    academicYears: AcademicYearsPaginated;
}
export default function Index({ academicYears }: IndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Academic Years" />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <div className="flex-1 space-y-4 rounded-xl border border-sidebar-border/70 p-4 md:min-h-min dark:border-sidebar-border">
                    <Link href={route('protected.academic-years.create')}>
                        <Button>
                            <Plus />
                            Tambah
                        </Button>
                    </Link>
                    <AcademicYearTable academicYears={academicYears} />
                </div>
            </div>
        </AppLayout>
    );
}
