// Di file: resources/js/pages/protected/schools/academic-years/_components/academic-years-table.tsx

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
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { router } from '@inertiajs/react';
import { ColumnDef, Table as TanstackTable } from '@tanstack/react-table';
import { format } from 'date-fns';
import { Eye, Trash2 } from 'lucide-react';
import { type SchoolAcademicYearsPaginated } from '../index';
import { AcademicYearsTableFilters } from './academic-years-table-filters';

// Definisi Kolom Tabel
export const columns: ColumnDef<SchoolAcademicYear>[] = [
    {
        id: 'select',
        header: ({ table }) => (
            <Checkbox
                checked={table.getIsAllPageRowsSelected()}
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
    },
    {
        // Akses aman di accessorFn (sudah benar)
        accessorFn: (row) => row.academic_year?.name ?? 'N/A',
        id: 'name',
        header: ({ column }) => <DataTableColumnHeader column={column} title="Tahun Ajaran" />,
    },
    {
        accessorFn: (row) => row.academic_year?.start,
        id: 'start_date',
        header: 'Tanggal Mulai',
        cell: ({ row }) => {
            // 1. Ambil data tanggal dari relasi
            const startDate = row.original.academic_year?.start;

            console.log(startDate);

            // 2. Lakukan pengecekan sebelum memformat
            if (!startDate) {
                return 'N/A'; // Tampilkan fallback jika tanggal tidak ada
            }

            // 3. Format hanya jika tanggalnya valid
            return format(new Date(startDate), 'dd MMMM yyyy');
        },
        enableSorting: false,
    },
    {
        accessorFn: (row) => row.academic_year?.end,
        id: 'end_date',
        header: 'Tanggal Selesai',
        cell: ({ row }) => {
            // Lakukan pengecekan yang sama untuk tanggal selesai
            const endDate = row.original.academic_year?.end;

            if (!endDate) {
                return 'N/A';
            }

            return format(new Date(endDate), 'dd MMMM yyyy');
        },
        enableSorting: false,
    },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => {
            const schoolAcademicYear = row.original;

            return (
                <div className="flex gap-2">
                    <TableTooltipAction info="Lihat">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() =>
                                router.get(
                                    route('protected.schools.academic-years.show', {
                                        school: schoolAcademicYear.school_id,
                                        schoolAcademicYear: schoolAcademicYear.id,
                                    }),
                                )
                            }
                        >
                            <Eye className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction>
                    {/* <TableTooltipAction info="Pengaturan">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() =>
                                router.get(
                                    route('protected.schools.academic-years.edit', {
                                        school: schoolAcademicYear.school_id,
                                        schoolAcademicYear: schoolAcademicYear.id,
                                    }),
                                )
                            }
                        >
                            <Settings2 className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction> */}

                    <AlertDialog>
                        <AlertDialogTrigger asChild>
                            <TableTooltipAction info="Hapus">
                                <Button variant="outline" size="icon">
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </TableTooltipAction>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Apakah Anda benar-benar yakin?</AlertDialogTitle>
                                <AlertDialogDescription>Tindakan ini akan menghapus tautan tahun ajaran dari sekolah ini.</AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                <AlertDialogAction
                                    className="bg-destructive text-white hover:bg-destructive/80 hover:text-white"
                                    onClick={() => {
                                        router.delete(
                                            route('protected.schools.academic-years.destroy', {
                                                school: schoolAcademicYear.school_id,
                                                schoolAcademicYear: schoolAcademicYear.id,
                                            }),
                                        );
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
        const selectedRows = table.getFilteredSelectedRowModel().rows;

        // Pastikan ada baris yang dipilih untuk mendapatkan school_id
        if (selectedRows.length === 0) return;

        const schoolId = selectedRows[0].original.school_id;

        const selectedIds = selectedRows.map((row) => row.original.id);

        router.post(
            route('protected.schools.academic-years.bulk-destroy', { school: schoolId }),
            {
                ids: selectedIds, // Kirim array ID di dalam body
            },
            {
                onSuccess: () => table.resetRowSelection(),
                preserveScroll: true,
            },
        );
    };
    return (
        <>
            <DataTable columns={columns} data={schoolAcademicYears.data} meta={{ from: schoolAcademicYears.from }}>
                {(table) => {
                    const selectedRowCount = table.getFilteredSelectedRowModel().rows.length;
                    return (
                        <div className="flex w-full items-center gap-4">
                            {/* Filter tetap di posisi atas */}
                            <AcademicYearsTableFilters />

                            {/* Gunakan komponen BulkDeleteDialog */}
                            <BulkDeleteAlertDialog itemCount={selectedRowCount} itemName="tahun ajaran" onConfirm={() => handleBulkDelete(table)}>
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
