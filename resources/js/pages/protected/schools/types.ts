import { ColumnDef } from '@tanstack/react-table';

type Principal = {
    id: string;
    name: string;
};

type AcademicYear = {
    id: string;
    name: string;
};

// The main type for your School data
export type School = {
    id: string;
    name: string;
    npsn: string;
    principal: Principal | null;
    current_academic_year: AcademicYear | null;
};

// The shape of the Laravel Paginator object
export interface Paginator<T> {
    data: T[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
    per_page: number;
}

export const columns: ColumnDef<School>[] = [
    {
        accessorKey: 'name',
        header: 'Name',
    },
    {
        accessorKey: 'npsn',
        header: 'NPSN',
    },
    {
        // Use an accessorFn to safely access nested properties
        accessorFn: (row) => row.principal?.name ?? 'N/A',
        id: 'principalName', // A unique ID is required for accessorFn
        header: 'Principal',
    },
    {
        accessorFn: (row) => row.current_academic_year?.name ?? 'N/A',
        id: 'academicYear',
        header: 'Current Academic Year',
    },
];
