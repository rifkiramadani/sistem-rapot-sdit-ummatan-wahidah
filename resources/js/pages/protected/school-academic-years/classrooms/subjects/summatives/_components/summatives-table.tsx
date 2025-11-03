// resources/js/Pages/protected/school-academic-years/classrooms/subjects/summatives/_components/summatives-table.tsx

import { DataTableColumnHeader } from '@/components/data-table-column-header';
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
import { ClassroomSubject } from '@/types/models/classroom-subjects';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Summative, SummativesPaginated } from '@/types/models/summatives';
import { ColumnDef, Table as TanstackTable } from '@tanstack/react-table';
import { Pencil, Trash2 } from 'lucide-react';
import { SummativesTableFilters } from './summatives-table-filters';
import { BulkDeleteAlertDialog } from '@/components/bulk-delete-alert-dialog';
import InertiaPagination from '@/components/inertia-pagination';
import { DataTable } from '@/components/data-table';
import { router } from '@inertiajs/react';


export const getColumns = (
    schoolAcademicYear: SchoolAcademicYear,
    classroom: Classroom,
    classroomSubject: ClassroomSubject,
    isTeacher: boolean = false,
): ColumnDef<Summative>[] => {
    const baseColumns: ColumnDef<Summative>[] = [
        { accessorKey: 'name', header: ({ column }) => <DataTableColumnHeader column={column} title="Nama Sumatif" /> },
        { accessorKey: 'identifier', header: ({ column }) => <DataTableColumnHeader column={column} title="Identifier" /> },
        { accessorFn: (row) => row.summative_type?.name ?? 'N/A', id: 'summative_type', header: 'Jenis' },
        { accessorKey: 'prominent', header: ({ column }) => <DataTableColumnHeader column={column} title="Menonjol" />, cell: ({ row }) => <div className="w-[200px] truncate">{row.original.prominent}</div> },
        { accessorKey: 'improvement', header: ({ column }) => <DataTableColumnHeader column={column} title="Peningkatan" />, cell: ({ row }) => <div className="w-[200px] truncate">{row.original.improvement}</div> },
        {
            id: 'actions',
            header: 'Aksi',
            cell: ({ row }) => {
                const summative = row.original;
                return (
                    <div className="flex gap-2">
                        {/* Tombol Edit */}
                        <TableTooltipAction info="Edit">
                            <Button variant="outline" size="icon" onClick={() =>
                                router.get(
                                    route('protected.school-academic-years.classrooms.subjects.summatives.edit', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        classroom: classroom.id,
                                        classroomSubject: classroomSubject.id,
                                        summative: summative.id
                                    }),
                                )
                            }>
                                <Pencil className="h-4 w-4" />
                            </Button>
                        </TableTooltipAction>

                        {/* TOMBOL HAPUS TUNGGAL */}
                        <AlertDialog>
                            <TableTooltipAction info="Hapus Sumatif">
                                <AlertDialogTrigger asChild>
                                    <Button variant="outline" size="icon">
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </AlertDialogTrigger>
                            </TableTooltipAction>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle>Hapus Sumatif: {summative.name}?</AlertDialogTitle>
                                    <AlertDialogDescription>
                                        Tindakan ini akan menghapus data sumatif ini dan <strong>semua nilai siswa yang terkait</strong>.
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel>Batal</AlertDialogCancel>
                                    <AlertDialogAction
                                        className="bg-destructive text-white hover:bg-destructive/80 hover:text-white"
                                        onClick={() => {
                                            // Build the URL first (debug-friendly)
                                            const url = route(
                                                'protected.school-academic-years.classrooms.subjects.summatives.destroy',
                                                {
                                                    schoolAcademicYear: schoolAcademicYear.id,
                                                    classroom: classroom.id,
                                                    classroomSubject: classroomSubject.id,
                                                    summative: summative.id,
                                                }
                                            );

                                            // Optional: console.log supaya bisa cek di DevTools apakah URL sudah benar
                                            // (hapus atau comment out setelah berhasil)
                                            // eslint-disable-next-line no-console
                                            console.log('DELETE URL:', url);

                                            // Gunakan POST dengan _method = DELETE (method spoofing)
                                            router.post(
                                                url,
                                                { _method: 'DELETE' },
                                                {
                                                    preserveScroll: true,
                                                    onBefore: () => {
                                                        // Optional: disable UI atau set loading state jika perlu
                                                    },
                                                    onSuccess: () => {
                                                        // Reload atau gunakan Inertia.visit untuk partial reload
                                                        window.location.reload();
                                                    },
                                                    onError: (errors) => {
                                                        // Optional: tampilkan error di console atau toast
                                                        // eslint-disable-next-line no-console
                                                        console.error('Gagal menghapus summative:', errors);
                                                    },
                                                }
                                            );
                                        }}
                                    >
                                        Ya, Hapus
                                    </AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                        {/* AKHIR TOMBOL HAPUS TUNGGAL */}
                    </div>
                );
            },
        },
    ];

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
        },
        ...baseColumns,
    ];
};

interface SummativesTableProps {
    summatives: SummativesPaginated;
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    classroomSubject: ClassroomSubject;
    isTeacher?: boolean;
}

export function SummativesTable({ summatives, schoolAcademicYear, classroom, classroomSubject, isTeacher = false }: SummativesTableProps) {
    const handleBulkDelete = (table: TanstackTable<Summative>) => {
        const selectedIds = Object.keys(table.getState().rowSelection);
        router.post(
            route('protected.school-academic-years.classrooms.subjects.summatives.bulk-destroy', {
                schoolAcademicYear: schoolAcademicYear.id,
                classroom: classroom.id,
                classroomSubject: classroomSubject.id
            }),
            { ids: selectedIds },
            { onSuccess: () => table.resetRowSelection(), preserveScroll: true },
        );
    };

    const columns = getColumns(schoolAcademicYear, classroom, classroomSubject, isTeacher);

    return (
        <div className="space-y-4">
            <DataTable columns={columns} data={summatives.data} meta={{ from: summatives.from }}>
                {(table) => (
                    <div className="flex w-full items-center gap-4">
                        <SummativesTableFilters />
                        <BulkDeleteAlertDialog
                            itemCount={Object.keys(table.getState().rowSelection).length}
                            itemName="data sumatif"
                            onConfirm={() => handleBulkDelete(table)}
                        >
                            <Button className="text-xs" variant="destructive" disabled={Object.keys(table.getState().rowSelection).length === 0}>
                                <Trash2 className="mr-1 h-2 w-2" /> Hapus ({Object.keys(table.getState().rowSelection).length})
                            </Button>
                        </BulkDeleteAlertDialog>
                    </div>
                )}
            </DataTable>
            <InertiaPagination paginateItems={summatives} />
        </div>
    );
}
