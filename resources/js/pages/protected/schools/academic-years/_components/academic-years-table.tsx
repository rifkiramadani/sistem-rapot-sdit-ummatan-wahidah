// Di file: resources/js/pages/protected/schools/academic-years/_components/academic-years-table.tsx

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
import { ColumnDef } from '@tanstack/react-table';
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
                    <TableTooltipAction info="Hapus">
                        <AlertDialog>
                            <AlertDialogTrigger asChild>
                                <Button variant="outline" size="icon">
                                    <Trash2 className="h-4 w-4" />
                                </Button>
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
                    </TableTooltipAction>
                </div>
            );
        },
    },
];

interface AcademicYearsTableProps {
    schoolAcademicYears: SchoolAcademicYearsPaginated;
}
export function AcademicYearsTable({ schoolAcademicYears }: AcademicYearsTableProps) {
    return (
        <>
            <DataTable columns={columns} data={schoolAcademicYears.data} meta={{ from: schoolAcademicYears.from }}>
                <AcademicYearsTableFilters />
            </DataTable>
            <InertiaPagination paginateItems={schoolAcademicYears} />
        </>
    );
}
