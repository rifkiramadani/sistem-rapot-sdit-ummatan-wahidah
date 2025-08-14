import { DataTable } from '@/components/data-table';
import { DataTableColumnHeader } from '@/components/data-table-column-header';
import InertiaPagination from '@/components/inertia-pagination';
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { TableMeta } from '@/types';
import { School, SchoolsPaginated } from '@/types/models/schools';
import { router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { MoreHorizontal, Pen, Trash } from 'lucide-react';
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
        enableHiding: false,
        // ... (di dalam file columns.tsx Anda)

        cell: ({ row }) => {
            const school = row.original;

            return (
                <>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="h-8 w-8 p-0">
                                <span className="sr-only">Buka menu</span>
                                <MoreHorizontal />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Aksi</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem onSelect={() => router.get(route('protected.schools.edit', school.id))} className="cursor-pointer">
                                <span className="flex items-center gap-2">
                                    <Pen className="h-4 w-4" />
                                    Edit
                                </span>
                            </DropdownMenuItem>
                            <DropdownMenuItem className="cursor-pointer text-red-600 focus:text-red-600">
                                <AlertDialog>
                                    <AlertDialogContent>
                                        <AlertDialogTrigger asChild>
                                            <span className="flex items-center gap-2">
                                                <Trash className="h-4 w-4" />
                                                Hapus
                                            </span>
                                        </AlertDialogTrigger>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Apakah Anda Yakin?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Tindakan ini tidak dapat diurungkan. Ini akan menghapus data sekolah secara permanen dari server.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Batal</AlertDialogCancel>
                                            <AlertDialogAction></AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    {/* Dialog Konfirmasi Hapus */}
                </>
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
