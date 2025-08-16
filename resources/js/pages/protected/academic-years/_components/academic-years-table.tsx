// Di file: app/protected/academicYear/_components/academic-year-table.tsx

import { DataTable } from '@/components/data-table';
import { DataTableColumnHeader } from '@/components/data-table-column-header';
import InertiaPagination from '@/components/inertia-pagination';
import { Checkbox } from '@/components/ui/checkbox';
import { TableMeta } from '@/types';
import { AcademicYear, AcademicYearsPaginated } from '@/types/models/academic-years.d';
import { ColumnDef } from '@tanstack/react-table';
import { AcademicYearTableFilters } from '../_components/academic-years-table-filters';

export const columns: ColumnDef<AcademicYear>[] = [
    {
        id: 'select',
        header: ({ table }) => (
            <Checkbox
                checked={table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate')}
                onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
                aria-label="Select all"
            />
        ),
        cell: ({ row }) => (
            <Checkbox checked={row.getIsSelected()} onCheckedChange={(value) => row.toggleSelected(!!value)} aria-label="Select row" />
        ),
        enableSorting: false,
        enableHiding: false,
    },
    {
        id: 'no',
        header: 'No.',
        cell: ({ row, table }) => {
            const { from } = table.options.meta as TableMeta;
            return from + row.index;
        },
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'name',
        header: ({ column }) => <DataTableColumnHeader column={column} title="Name" />,
    },
    {
        accessorKey: 'start',
        header: ({ column }) => <DataTableColumnHeader column={column} title="Start Year" />,
    },
    {
        accessorKey: 'end',
        header: ({ column }) => <DataTableColumnHeader column={column} title="End Year" />,
    },
];

interface AcademicYearTableProps {
    academicYears: AcademicYearsPaginated;
}
export function AcademicYearTable({ academicYears }: AcademicYearTableProps) {
    return (
        <>
            <DataTable columns={columns} data={academicYears.data} meta={{ from: academicYears.from }}>
                <AcademicYearTableFilters />
            </DataTable>
            <InertiaPagination paginateItems={academicYears} />
        </>
    );
}
