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
import { ClassroomStudent, ClassroomStudentsPaginated } from '@/types/models/classroom-students';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { router } from '@inertiajs/react';
import { ColumnDef, Table as TanstackTable } from '@tanstack/react-table';
import { Eye, Trash2 } from 'lucide-react';
import { ClassroomStudentsTableFilters } from './classroom-students-table-filters';

export const getColumns = (schoolAcademicYear: SchoolAcademicYear, classroom: Classroom, isTeacher: boolean = false): ColumnDef<ClassroomStudent>[] => {
    const baseColumns: ColumnDef<ClassroomStudent>[] = [
        // { id: 'no', header: 'No.', cell: ({ row, table }) => ((table.options.meta as TableMeta)?.from ?? 0) + row.index, enableSorting: false },
        {
            accessorFn: (row) => row.student?.nisn ?? 'N/A',
            id: 'nisn',
            header: ({ column }) => <DataTableColumnHeader column={column} title="NISN" />,
        },
        {
            accessorFn: (row) => row.student?.name ?? 'N/A',
            id: 'name',
            header: ({ column }) => <DataTableColumnHeader column={column} title="Nama Siswa" />,
        },
        {
            accessorFn: (row) => row.student?.gender,
            id: 'gender',
            header: 'L/P',
            cell: ({ row }) => {
                const gender = row.original.student?.gender;
                if (!gender) return 'N/A';
                return gender === 'male' ? 'Laki-laki' : 'Perempuan';
            },
        },
        {
            // Rantai '?.' bisa digunakan berkali-kali untuk akses yang lebih dalam
            accessorFn: (row) => row.student?.guardian?.name ?? 'N/A',
            id: 'guardian_name',
            header: 'Nama Wali',
        },
        {
            id: 'actions',
            header: 'Aksi',
            cell: ({ row }) => {
                const classroomStudent = row.original;
                return (
                    <div className="flex gap-2">
                        <TableTooltipAction info="Lihat">
                            <Button
                                variant="outline"
                                size="icon"
                                onClick={() =>
                                    router.get(
                                        route('protected.school-academic-years.classrooms.students.show', {
                                            schoolAcademicYear: schoolAcademicYear.id,
                                            classroom: classroom.id,
                                            classroomStudent: classroomStudent.id,
                                        }),
                                    )
                                }
                            >
                                <Eye className="h-4 w-4" />
                            </Button>
                        </TableTooltipAction>
                        {!isTeacher && (
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
                                                        classroomStudent: classroomStudent.id,
                                                    }),
                                                )
                                            }
                                        >
                                            Ya, Keluarkan
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
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
                    />
                ),
                cell: ({ row }) => <Checkbox checked={row.getIsSelected()} onCheckedChange={(value) => row.toggleSelected(!!value)} />,
                enableSorting: false,
                enableHiding: false,
            },
            ...baseColumns,
        ];
    }

    return baseColumns;
};

interface ClassroomStudentsTableProps {
    // [UBAH] Gunakan tipe data paginasi yang baru
    classroomStudents: ClassroomStudentsPaginated;
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    isTeacher?: boolean;
}

export function ClassroomStudentsTable({ classroomStudents, schoolAcademicYear, classroom, isTeacher = false }: ClassroomStudentsTableProps) {
    const handleBulkDelete = (table: TanstackTable<ClassroomStudent>) => {
        // ID yang diambil adalah ID dari record pivot
        const selectedIds = Object.keys(table.getState().rowSelection);
        router.post(
            route('protected.school-academic-years.classrooms.students.bulk-destroy', { schoolAcademicYear, classroom }),
            { ids: selectedIds },
            { onSuccess: () => table.resetRowSelection(), preserveScroll: true },
        );
    };

    const columns = getColumns(schoolAcademicYear, classroom, isTeacher);

    return (
        <div className="space-y-4">
            <DataTable columns={columns} data={classroomStudents.data} meta={{ from: classroomStudents.from }}>
                {(table) => (
                    <div className="flex w-full items-center gap-4">
                        <ClassroomStudentsTableFilters />
                        {!isTeacher && (
                            <BulkDeleteAlertDialog
                                itemCount={Object.keys(table.getState().rowSelection).length}
                                itemName="siswa dari kelas ini"
                                onConfirm={() => handleBulkDelete(table)}
                            >
                                <Button className="text-xs" variant="destructive" disabled={Object.keys(table.getState().rowSelection).length === 0}>
                                    <Trash2 className="mr-1 h-2 w-2" /> Keluarkan ({Object.keys(table.getState().rowSelection).length})
                                </Button>
                            </BulkDeleteAlertDialog>
                        )}
                    </div>
                )}
            </DataTable>
            <InertiaPagination paginateItems={classroomStudents} />
        </div>
    );
}
