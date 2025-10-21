// resources/js/Pages/.../classrooms/subjects/_components/classroom-subjects-table.tsx

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
import { ClassroomSubject, ClassroomSubjectsPaginated } from '@/types/models/classroom-subjects';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { router } from '@inertiajs/react';
import { ColumnDef, Table as TanstackTable } from '@tanstack/react-table';
import { Eye, Settings2, Trash2 } from 'lucide-react';
import { ClassroomSubjectsTableFilters } from './classroom-subjects-table-filters';

export const getColumns = (schoolAcademicYear: SchoolAcademicYear, classroom: Classroom): ColumnDef<ClassroomSubject>[] => [
    {
        id: 'select',
        header: ({ table }) => (
            <Checkbox
                checked={table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate')}
                onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
            />
        ),
        cell: ({ row }) => <Checkbox checked={row.getIsSelected()} onCheckedChange={(value) => row.toggleSelected(!!value)} />,
    },
    // { id: 'no', header: 'No.', cell: ({ row, table }) => ((table.options.meta as TableMeta)?.from ?? 0) + row.index },
    {
        accessorFn: (row) => row.subject?.name ?? 'N/A',
        id: 'name',
        header: ({ column }) => <DataTableColumnHeader column={column} title="Nama Mata Pelajaran" />,
    },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => {
            const classroomSubject = row.original;
            return (
                <div className="flex gap-2">
                    <TableTooltipAction info="Lihat">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() =>
                                router.get(
                                    route('protected.school-academic-years.classrooms.subjects.show', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        classroom: classroom.id,
                                        classroomSubject: classroomSubject.id,
                                    }),
                                )
                            }
                        >
                            <Eye className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction>
                    {/* <TableTooltipAction info="Edit">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() =>
                                router.get(
                                    route('protected.school-academic-years.classrooms.subjects.edit', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        classroom: classroom.id,
                                        classroomSubject: classroomSubject.id,
                                    }),
                                )
                            }
                        >
                            <Settings2 className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction> */}
                    <AlertDialog>
                        <TableTooltipAction info="Hapus dari Kelas">
                            <AlertDialogTrigger asChild>
                                <Button variant="outline" size="icon">
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </AlertDialogTrigger>
                        </TableTooltipAction>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Hapus {classroomSubject.subject?.name}?</AlertDialogTitle>
                                <AlertDialogDescription>Tindakan ini akan menghapus tautan mata pelajaran dari kelas ini.</AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                <AlertDialogAction
                                    className="bg-destructive text-white hover:bg-destructive/80 hover:text-white"
                                    onClick={() =>
                                        router.delete(
                                            route('protected.school-academic-years.classrooms.subjects.destroy', {
                                                schoolAcademicYear,
                                                classroom,
                                                classroomSubject: classroomSubject.id,
                                            }),
                                        )
                                    }
                                >
                                    Ya, Hapus
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            );
        },
    },
];

interface ClassroomSubjectsTableProps {
    classroomSubjects: ClassroomSubjectsPaginated;
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
}

export function ClassroomSubjectsTable({ classroomSubjects, schoolAcademicYear, classroom }: ClassroomSubjectsTableProps) {
    const handleBulkDelete = (table: TanstackTable<ClassroomSubject>) => {
        const selectedIds = Object.keys(table.getState().rowSelection);
        router.post(
            route('protected.school-academic-years.classrooms.subjects.bulk-destroy', { schoolAcademicYear, classroom }),
            { ids: selectedIds },
            { onSuccess: () => table.resetRowSelection(), preserveScroll: true },
        );
    };

    const columns = getColumns(schoolAcademicYear, classroom);

    return (
        <div className="space-y-4">
            <DataTable columns={columns} data={classroomSubjects.data} meta={{ from: classroomSubjects.from }}>
                {(table) => (
                    <div className="flex w-full items-center gap-4">
                        <ClassroomSubjectsTableFilters />
                        <BulkDeleteAlertDialog
                            itemCount={Object.keys(table.getState().rowSelection).length}
                            itemName="mata pelajaran"
                            onConfirm={() => handleBulkDelete(table)}
                        >
                            <Button className="text-xs" variant="destructive" disabled={Object.keys(table.getState().rowSelection).length === 0}>
                                <Trash2 className="mr-1 h-2 w-2" /> Hapus ({Object.keys(table.getState().rowSelection).length})
                            </Button>
                        </BulkDeleteAlertDialog>
                    </div>
                )}
            </DataTable>
            <InertiaPagination paginateItems={classroomSubjects} />
        </div>
    );
}
