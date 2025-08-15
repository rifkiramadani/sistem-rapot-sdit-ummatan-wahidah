import { DataTable } from '@/components/data-table';
import { DataTableColumnHeader } from '@/components/data-table-column-header';
import InertiaPagination from '@/components/inertia-pagination';
import TableTooltipAction from '@/components/table-tooltip-action';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { TableMeta } from '@/types';
import { School, SchoolsPaginated } from '@/types/models/schools';
import { router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { Eye, Settings2, Trash2 } from 'lucide-react';
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
        cell: ({ row }) => {
            const school = row.original;

            return (
                <div className="flex gap-2">
                    <TableTooltipAction info="Lihat">
                        <Button variant="outline" size="icon" onClick={() => router.get(route('protected.schools.show', { school: school.id }))}>
                            <Eye className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction>
                    <TableTooltipAction info="Edit">
                        <Button variant="outline" size="icon" onClick={() => router.get(route('protected.schools.edit', { school: school.id }))}>
                            <Settings2 className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction>
                    <TableTooltipAction info="Lihat">
                        <AlertDialog>
                            <AlertDialogTrigger asChild>
                                <Button variant="outline" size="icon">
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle>Apakah Anda benar-benar yakin?</AlertDialogTitle>
                                    <AlertDialogDescription>
                                        Tindakan ini tidak dapat dibatalkan. Ini akan menghapus data secara permanen.
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel>Batal</AlertDialogCancel>
                                    <AlertDialogAction
                                        className="bg-destructive text-white hover:bg-destructive/80 hover:text-white"
                                        onClick={() => {
                                            router.delete(route('protected.schools.destroy', { school: school.id }));
                                        }}
                                    >
                                        Lanjutkan
                                    </AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                    </TableTooltipAction>
                </div>
            );
        },
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
