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
import { Student, StudentsPaginated } from '@/types/models/students';
import { router } from '@inertiajs/react';
import { ColumnDef, Table as TanstackTable } from '@tanstack/react-table';
import { Eye, Settings2, Trash2 } from 'lucide-react';
import { StudentsTableFilters } from './students-table-filters';

export const getColumns = (schoolAcademicYear: SchoolAcademicYear): ColumnDef<Student>[] => [
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
    // { id: 'no', header: 'No.', cell: ({ row, table }) => ((table.options.meta as TableMeta)?.from ?? 0) + row.index, enableSorting: false },
    { accessorKey: 'nisn', header: ({ column }) => <DataTableColumnHeader column={column} title="NISN" /> },
    { accessorKey: 'name', header: ({ column }) => <DataTableColumnHeader column={column} title="Nama Siswa" /> },
    { accessorKey: 'gender', header: 'L/P', cell: ({ row }) => (row.original.gender === 'male' ? 'Laki-laki' : 'Perempuan') },
    { accessorFn: (row) => row.guardian?.name ?? 'N/A', id: 'guardian_name', header: 'Nama Wali' },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => {
            const student = row.original;
            return (
                <div className="flex gap-2">
                    <TableTooltipAction info="Lihat">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() =>
                                router.get(
                                    route('protected.school-academic-years.students.show', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        student: student.id,
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
                                    route('protected.school-academic-years.students.edit', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        student: student.id,
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
                                    Tindakan ini tidak dapat dibatalkan. Ini akan menghapus data siswa secara permanen.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                <AlertDialogAction
                                    className="bg-destructive text-white hover:bg-destructive/80 hover:text-white"
                                    onClick={() =>
                                        router.delete(
                                            route('protected.school-academic-years.students.destroy', {
                                                schoolAcademicYear: schoolAcademicYear.id,
                                                student: student.id,
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

interface StudentsTableProps {
    students: StudentsPaginated;
    schoolAcademicYear: SchoolAcademicYear;
}

export function StudentsTable({ students, schoolAcademicYear }: StudentsTableProps) {
    const handleBulkDelete = (table: TanstackTable<Student>) => {
        const selectedIds = Object.keys(table.getState().rowSelection);
        router.post(
            route('protected.school-academic-years.students.bulk-destroy', { schoolAcademicYear: schoolAcademicYear.id }),
            { ids: selectedIds },
            { onSuccess: () => table.resetRowSelection(), preserveScroll: true },
        );
    };

    const columns = getColumns(schoolAcademicYear);

    return (
        <div className="space-y-4">
            <DataTable columns={columns} data={students.data} meta={{ from: students.from }}>
                {(table) => (
                    <div className="flex w-full items-center gap-4">
                        <StudentsTableFilters />
                        <BulkDeleteAlertDialog
                            itemCount={Object.keys(table.getState().rowSelection).length}
                            itemName="data siswa"
                            onConfirm={() => handleBulkDelete(table)}
                        >
                            <Button className="text-xs" variant="destructive" disabled={Object.keys(table.getState().rowSelection).length === 0}>
                                <Trash2 className="mr-1 h-2 w-2" />
                                Hapus ({Object.keys(table.getState().rowSelection).length})
                            </Button>
                        </BulkDeleteAlertDialog>
                    </div>
                )}
            </DataTable>
            <InertiaPagination paginateItems={students} />
        </div>
    );
}
