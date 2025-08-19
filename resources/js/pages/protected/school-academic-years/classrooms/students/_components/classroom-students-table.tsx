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
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Student, StudentsPaginated } from '@/types/models/students';
import { router } from '@inertiajs/react';
import { ColumnDef, Table as TanstackTable } from '@tanstack/react-table';
import { Eye, Trash2 } from 'lucide-react';
import { ClassroomStudentsTableFilters } from './classroom-students-table-filters';

export const getColumns = (schoolAcademicYear: SchoolAcademicYear, classroom: Classroom): ColumnDef<Student>[] => [
    {
        id: 'select',
        header: ({ table }) => (
            <Checkbox
                checked={table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate')}
                onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
            />
        ),
        cell: ({ row }) => <Checkbox checked={row.getIsSelected()} onCheckedChange={(value) => row.toggleSelected(!!value)} />,
        enableSorting: false,
        enableHiding: false,
    },
    { id: 'no', header: 'No.', cell: ({ row, table }) => ((table.options.meta as TableMeta)?.from ?? 0) + row.index, enableSorting: false },
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
                    <TableTooltipAction info="Lihat Profil Siswa">
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
                    <AlertDialog>
                        <TableTooltipAction info="Keluarkan dari Kelas">
                            <AlertDialogTrigger asChild>
                                <Button variant="outline" size="icon">
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            </AlertDialogTrigger>
                        </TableTooltipAction>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Apakah Anda Yakin?</AlertDialogTitle>
                                <AlertDialogDescription>
                                    Tindakan ini akan mengeluarkan siswa dari kelas ini, tetapi tidak akan menghapus data siswa secara permanen.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                <AlertDialogAction
                                    className="bg-destructive text-white hover:bg-destructive/80 hover:text-white"
                                    onClick={() =>
                                        router.delete(
                                            route('protected.school-academic-years.classrooms.students.destroy', {
                                                schoolAcademicYear: schoolAcademicYear.id,
                                                classroom: classroom.id,
                                                classroomStudent: student.id /* Ganti dengan ID ClassroomStudent jika ada */,
                                            }),
                                        )
                                    }
                                >
                                    Ya, Keluarkan
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            );
        },
    },
];

interface ClassroomStudentsTableProps {
    students: StudentsPaginated;
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
}

export function ClassroomStudentsTable({ students, schoolAcademicYear, classroom }: ClassroomStudentsTableProps) {
    const handleBulkDelete = (table: TanstackTable<Student>) => {
        const selectedIds = Object.keys(table.getState().rowSelection);
        router.post(
            route('protected.school-academic-years.classrooms.students.bulk-destroy', {
                schoolAcademicYear: schoolAcademicYear.id,
                classroom: classroom.id,
            }),
            { student_ids: selectedIds },
            { onSuccess: () => table.resetRowSelection(), preserveScroll: true },
        );
    };

    const columns = getColumns(schoolAcademicYear, classroom);

    return (
        <div className="space-y-4">
            <DataTable columns={columns} data={students.data} meta={{ from: students.from }}>
                {(table) => (
                    <div className="flex w-full items-center gap-4">
                        <ClassroomStudentsTableFilters />
                        <BulkDeleteAlertDialog
                            itemCount={Object.keys(table.getState().rowSelection).length}
                            itemName="siswa dari kelas ini"
                            onConfirm={() => handleBulkDelete(table)}
                        >
                            <Button className="text-xs" variant="destructive" disabled={Object.keys(table.getState().rowSelection).length === 0}>
                                <Trash2 className="mr-1 h-2 w-2" /> Keluarkan ({Object.keys(table.getState().rowSelection).length})
                            </Button>
                        </BulkDeleteAlertDialog>
                    </div>
                )}
            </DataTable>
            <InertiaPagination paginateItems={students} />
        </div>
    );
}
