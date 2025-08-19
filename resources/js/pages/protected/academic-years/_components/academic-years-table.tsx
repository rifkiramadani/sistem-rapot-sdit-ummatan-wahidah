// Di file: app/protected/academicYear/_components/academic-year-table.tsx

import { BulkDeleteAlertDialog } from '@/components/bulk-delete-alert-dialog';
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
import { AcademicYear, AcademicYearsPaginated } from '@/types/models/academic-years.d';
import { router } from '@inertiajs/react';
import { ColumnDef, Table as TanstackTable } from '@tanstack/react-table';
import { format } from 'date-fns';
import { Eye, Settings2, Trash2 } from 'lucide-react';
import { AcademicYearsTableFilters } from '../../schools/academic-years/_components/academic-years-table-filters';

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
        // Custom cell to format the date
        cell: ({ row }) => {
            const date = new Date(row.original.start);
            return format(date, 'yyyy'); // Format to display only the year
        },
    },
    {
        accessorKey: 'end',
        header: ({ column }) => <DataTableColumnHeader column={column} title="End Year" />,
        // Custom cell to format the date
        cell: ({ row }) => {
            const date = new Date(row.original.end);
            return format(date, 'yyyy'); // Format to display only the year
        },
    },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => {
            const academicYear = row.original;

            return (
                <div className="flex gap-2">
                    <TableTooltipAction info="Lihat">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() => router.get(route('protected.academic-years.show', { academicYear: academicYear.id }))}
                        >
                            <Eye className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction>
                    <TableTooltipAction info="Edit">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() => router.get(route('protected.academic-years.edit', { academicYear: academicYear.id }))}
                        >
                            <Settings2 className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction>

                    <AlertDialog>
                        <TableTooltipAction info="Hapus">
                            <AlertDialogTrigger asChild>
                                <Button variant="outline" size="icon">
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </AlertDialogTrigger>
                        </TableTooltipAction>
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
                                        router.delete(route('protected.academic-years.destroy', { academicYear: academicYear.id }));
                                    }}
                                >
                                    Lanjutkan
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            );
        },
    },
];

interface AcademicYearsTableProps {
    academicYears: AcademicYearsPaginated;
}
export function AcademicYearsTable({ academicYears }: AcademicYearsTableProps) {
    const handleBulkDelete = (table: TanstackTable<AcademicYear>) => {
        const selectedIds = Object.keys(table.getState().rowSelection);

        // Kirim ID ke backend
        router.post(
            route('protected.academic-years.bulk-destroy'),
            {
                ids: selectedIds,
            },
            {
                onSuccess: () => {
                    table.resetRowSelection();
                },
                preserveScroll: true,
            },
        );
    };
    return (
        <>
            <DataTable columns={columns} data={academicYears.data} meta={{ from: academicYears.from }}>
                {(table) => {
                    const selectedRowCount = Object.keys(table.getState().rowSelection).length;
                    return (
                        <div className="flex w-full items-center gap-4">
                            <AcademicYearsTableFilters />
                            <BulkDeleteAlertDialog
                                itemCount={selectedRowCount}
                                itemName="data tahun ajaran"
                                onConfirm={() => handleBulkDelete(table)}
                            >
                                <Button className="text-xs" variant="destructive" disabled={selectedRowCount === 0}>
                                    <Trash2 className="mr-1 h-2 w-2" />({selectedRowCount})
                                </Button>
                            </BulkDeleteAlertDialog>
                        </div>
                    );
                }}
            </DataTable>
            <InertiaPagination paginateItems={academicYears} />
        </>
    );
}
