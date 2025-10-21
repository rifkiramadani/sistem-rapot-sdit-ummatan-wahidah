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
import { SchoolAcademicYear, SchoolAcademicYearsPaginated } from '@/types/models/school-academic-years';
import { router, Link } from '@inertiajs/react';
import { ColumnDef, Table as TanstackTable } from '@tanstack/react-table';
import { format } from 'date-fns';
import { Eye, Settings2, Trash2, LayoutDashboard } from 'lucide-react';
import { AcademicYearsTableFilters } from '../../schools/academic-years/_components/academic-years-table-filters';

export const columns: ColumnDef<SchoolAcademicYear>[] = [
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
        accessorKey: 'academic_year.name',
        header: ({ column }) => <DataTableColumnHeader column={column} title="Name" />,
        cell: ({ row }) => row.original.academic_year.name,
    },
    {
        accessorKey: 'academic_year.start',
        header: ({ column }) => <DataTableColumnHeader column={column} title="Start Year" />,
        // Custom cell to format the date
        cell: ({ row }) => {
            const date = new Date(row.original.academic_year.start);
            return format(date, 'yyyy'); // Format to display only the year
        },
    },
    {
        accessorKey: 'academic_year.end',
        header: ({ column }) => <DataTableColumnHeader column={column} title="End Year" />,
        // Custom cell to format the date
        cell: ({ row }) => {
            const date = new Date(row.original.academic_year.end);
            return format(date, 'yyyy'); // Format to display only the year
        },
    },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => {
            const schoolAcademicYear = row.original;
            const academicYear = schoolAcademicYear.academic_year;

            return (
                <div className="flex gap-2">
                    <Link
                        href={route('protected.school-academic-years.dashboard.index', {
                            schoolAcademicYear: schoolAcademicYear.id,
                        })}
                    >
                        <Button variant="outline" size="sm">
                            <LayoutDashboard className="mr-2 h-4 w-4" />
                            Dasbor
                        </Button>
                    </Link>
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
    schoolAcademicYears: SchoolAcademicYearsPaginated;
}
export function AcademicYearsTable({ schoolAcademicYears }: AcademicYearsTableProps) {
    const handleBulkDelete = (table: TanstackTable<SchoolAcademicYear>) => {
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
            <DataTable columns={columns} data={schoolAcademicYears.data} meta={{ from: schoolAcademicYears.from }}>
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
            <InertiaPagination paginateItems={schoolAcademicYears} />
        </>
    );
}
