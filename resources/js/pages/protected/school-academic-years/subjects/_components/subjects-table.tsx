// resources/js/Pages/protected/school-academic-years/teachers/_components/teachers-table.tsx

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
import { Eye, Settings2, Trash2 } from 'lucide-react';
import { SubjectsTableFilters } from './subjects-table-filters';
import { Subject, SubjectsPaginated } from '@/types/models/subjects';

// Mendefinisikan kolom-kolom tabel
export const getColumns = (schoolAcademicYear: SchoolAcademicYear): ColumnDef<Subject>[] => [
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
            const { from } = (table.options.meta as TableMeta) || {};
            return from ? from + row.index : row.index + 1;
        },
        enableSorting: false,
    },
    {
        accessorKey: 'name',
        header: ({ column }) => <DataTableColumnHeader column={column} title="Nama" />,
    },
    {
        accessorKey: 'description',
        header: ({ column }) => <DataTableColumnHeader column={column} title="Deskripsi" />,
    },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => {
            const subject = row.original;
            return (
                <div className="flex gap-2">
                    <TableTooltipAction info="Lihat">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() =>
                                router.get(
                                    route('protected.school-academic-years.subjects.show', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        subject: subject.id,
                                    }),
                                )
                            }
                        >
                            <Eye className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction>
                    <TableTooltipAction info="Edit">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() =>
                                router.get(
                                    route('protected.school-academic-years.subjects.edit', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        subject: subject.id,
                                    }),
                                )
                            }
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
                                    Tindakan ini tidak dapat dibatalkan. Ini akan menghapus data guru secara permanen.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                <AlertDialogAction
                                    className="bg-destructive text-white hover:bg-destructive/80 hover:text-white"
                                    onClick={() =>
                                        router.delete(
                                            route('protected.school-academic-years.subjects.destroy', {
                                                schoolAcademicYear: schoolAcademicYear.id,
                                                subject: subject.id,
                                            }),
                                        )
                                    }
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

// Props untuk komponen TeachersTable
interface SubjectsTableProps {
    subjects: SubjectsPaginated;
    schoolAcademicYear: SchoolAcademicYear;
}

export function SubjectsTable({ subjects, schoolAcademicYear }: SubjectsTableProps) {
    // Fungsi untuk menangani hapus data massal
    const handleBulkDelete = (table: TanstackTable<Subject>) => {
        const selectedIds = Object.keys(table.getState().rowSelection);

        router.post(
            route('protected.school-academic-years.teachers.bulk-destroy', { schoolAcademicYear: schoolAcademicYear.id }),
            { ids: selectedIds },
            {
                onSuccess: () => table.resetRowSelection(),
                preserveScroll: true,
            },
        );
    };

    // Gunakan useMemo atau definisikan di dalam komponen untuk mengakses schoolAcademicYear
    const columns = getColumns(schoolAcademicYear);

    return (
        <div className="space-y-4">
            <DataTable columns={columns} data={subjects.data} meta={{ from: subjects.from }}>
                {(table) => {
                    // [UBAH] Hitung jumlah baris yang dipilih dari state global
                    const selectedRowCount = Object.keys(table.getState().rowSelection).length;

                    return (
                        <div className="flex w-full items-center gap-4">
                            <SubjectsTableFilters />
                            <BulkDeleteAlertDialog itemCount={selectedRowCount} itemName="data guru" onConfirm={() => handleBulkDelete(table)}>
                                <Button className="text-xs" variant="destructive" disabled={selectedRowCount === 0}>
                                    <Trash2 className="mr-1 h-2 w-2" />
                                    Hapus ({selectedRowCount})
                                </Button>
                            </BulkDeleteAlertDialog>
                        </div>
                    );
                }}
            </DataTable>
            <InertiaPagination paginateItems={subjects} />
        </div>
    );
}
