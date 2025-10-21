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
import { Classroom, ClassroomsPaginated } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { router } from '@inertiajs/react';
import { ColumnDef, Table as TanstackTable } from '@tanstack/react-table';
import { Eye, Settings2, Trash2 } from 'lucide-react';
import { ClassroomsTableFilters } from './classrooms-table-filters';

export const getColumns = (schoolAcademicYear: SchoolAcademicYear, isTeacher: boolean = false): ColumnDef<Classroom>[] => {
    const baseColumns: ColumnDef<Classroom>[] = [
        // { id: 'no', header: 'No.', cell: ({ row, table }) => ((table.options.meta as TableMeta)?.from ?? 0) + row.index, enableSorting: false },
        { accessorKey: 'name', header: ({ column }) => <DataTableColumnHeader column={column} title="Nama Kelas" /> },
        { accessorFn: (row) => row.teacher?.name ?? 'N/A', id: 'teacher_name', header: 'Wali Kelas' },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => {
            const classroom = row.original;
            return (
                <div className="flex gap-2">
                    <TableTooltipAction info="Lihat">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() =>
                                router.get(
                                    route('protected.school-academic-years.classrooms.show', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        classroom: classroom.id,
                                    }),
                                )
                            }
                        >
                            <Eye className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction>
                    {!isTeacher && (
                        <>
                            <TableTooltipAction info="Edit">
                                <Button
                                    variant="outline"
                                    size="icon"
                                    onClick={() =>
                                        router.get(
                                            route('protected.school-academic-years.classrooms.edit', {
                                                schoolAcademicYear: schoolAcademicYear.id,
                                                classroom: classroom.id,
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
                                            Tindakan ini tidak dapat dibatalkan. Ini akan menghapus data kelas secara permanen.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Batal</AlertDialogCancel>
                                        <AlertDialogAction
                                            className="bg-destructive text-white hover:bg-destructive/80 hover:text-white"
                                            onClick={() =>
                                                router.delete(
                                                    route('protected.school-academic-years.classrooms.destroy', {
                                                        schoolAcademicYear: schoolAcademicYear.id,
                                                        classroom: classroom.id,
                                                    }),
                                                )
                                            }
                                        >
                                            Lanjutkan
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </>
                    )}
                </div>
            );
        },
    },
    ];

    // Add select column only for non-teachers
    if (!isTeacher) {
        return [
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
            ...baseColumns,
        ];
    }

    return baseColumns;
};

interface ClassroomsTableProps {
    classrooms: ClassroomsPaginated;
    schoolAcademicYear: SchoolAcademicYear;
    isTeacher?: boolean;
}

export function ClassroomsTable({ classrooms, schoolAcademicYear, isTeacher = false }: ClassroomsTableProps) {
    const handleBulkDelete = (table: TanstackTable<Classroom>) => {
        const selectedIds = Object.keys(table.getState().rowSelection);
        router.post(
            route('protected.school-academic-years.classrooms.bulk-destroy', { schoolAcademicYear: schoolAcademicYear.id }),
            { ids: selectedIds },
            { onSuccess: () => table.resetRowSelection(), preserveScroll: true },
        );
    };

    const columns = getColumns(schoolAcademicYear, isTeacher);

    return (
        <div className="space-y-4">
            <DataTable columns={columns} data={classrooms.data} meta={{ from: classrooms.from }}>
                {(table) => (
                    <div className="flex w-full items-center gap-4">
                        <ClassroomsTableFilters />
                        {!isTeacher && (
                            <BulkDeleteAlertDialog
                                itemCount={Object.keys(table.getState().rowSelection).length}
                                itemName="data kelas"
                                onConfirm={() => handleBulkDelete(table)}
                            >
                                <Button className="text-xs" variant="destructive" disabled={Object.keys(table.getState().rowSelection).length === 0}>
                                    <Trash2 className="mr-1 h-2 w-2" />
                                    Hapus ({Object.keys(table.getState().rowSelection).length})
                                </Button>
                            </BulkDeleteAlertDialog>
                        )}
                    </div>
                )}
            </DataTable>
            <InertiaPagination paginateItems={classrooms} />
        </div>
    );
}
