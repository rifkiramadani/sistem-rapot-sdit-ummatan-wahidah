import { DataTable } from '@/components/data-table';
import { DataTableColumnHeader } from '@/components/data-table-column-header';
import InertiaPagination from '@/components/inertia-pagination';
import { Checkbox } from '@/components/ui/checkbox';
import { TableMeta } from '@/types';
import { School, SchoolsPaginated } from '@/types/models/schools';
import { ColumnDef } from '@tanstack/react-table';
import { SchoolsTableActions } from './schools-table-actions';
import { SchoolsTableFilters } from './schools-table-filters';

export const columns: ColumnDef<School>[] = [
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
        // Ganti header menjadi komponen Button
        header: ({ column }) => <DataTableColumnHeader column={column} title="Name" />,
    },
    {
        accessorKey: 'npsn',
        // Lakukan hal yang sama untuk kolom lain yang bisa di-sort
        header: ({ column }) => <DataTableColumnHeader column={column} title="NPSN" />,
    },
    {
        accessorFn: (row) => row.principal?.name ?? 'N/A',
        id: 'principalName',
        header: 'Principal',
        // Untuk saat ini kita nonaktifkan, karena sorting relasi memerlukan penanganan khusus di backend
        enableSorting: false,
    },
    {
        accessorFn: (row) => row.current_academic_year?.name ?? 'N/A',
        id: 'academicYear',
        header: 'Current Academic Year',
        enableSorting: false,
    },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => <SchoolsTableActions school={row.original} />,
    },
];

interface SchoolsTableProps {
    schools: SchoolsPaginated;
}
export function SchoolsTable({ schools }: SchoolsTableProps) {
    return (
        <>
            <DataTable columns={columns} data={schools.data} meta={{ from: schools.from }}>
                <SchoolsTableFilters />
            </DataTable>
            <InertiaPagination paginateItems={schools} />
        </>
    );
}
