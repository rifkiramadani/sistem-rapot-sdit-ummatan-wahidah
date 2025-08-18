// Di file: app/protected/academicYear/_components/academic-year-table.tsx

import { DataTable } from '@/components/data-table';
import { DataTableColumnHeader } from '@/components/data-table-column-header';
import InertiaPagination from '@/components/inertia-pagination';
import { Checkbox } from '@/components/ui/checkbox';
import { TableMeta } from '@/types';
import { AcademicYear, AcademicYearsPaginated } from '@/types/models/academic-years.d';
import { ColumnDef } from '@tanstack/react-table';
import { AcademicYearTableFilters } from '../_components/academic-years-table-filters';
import { format } from "date-fns"
import TableTooltipAction from '@/components/table-tooltip-action';
import { Button } from '@/components/ui/button';
import { Eye, Settings2, Trash2 } from 'lucide-react';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { router } from '@inertiajs/react';

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
                        <Button variant="outline" size="icon" onClick={() => router.get(route('protected.academic-years.show', { academicYear: academicYear.id }))}>
                            <Eye className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction>
                    <TableTooltipAction info="Edit">
                        <Button variant="outline" size="icon" onClick={() => router.get(route('protected.academic-years.edit', { academicYear: academicYear.id }))}>
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
                                            // router.delete(route('protected.academic-years.destroy', { academicYear: academicYear.id }));
                                        }}
                                    >
                                        Lanjutkan
                                    </AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                    </TableTooltipAction>
                </div >
            );
        },
    }
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
