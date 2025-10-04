import { BulkDeleteAlertDialog } from '@/components/bulk-delete-alert-dialog';
import { DataTable } from '@/components/data-table';
import { DataTableColumnHeader } from '@/components/data-table-column-header';
import InertiaPagination from '@/components/inertia-pagination';
import TableTooltipAction from '@/components/table-tooltip-action';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { TableMeta } from '@/types';
import { ClassroomSubject } from '@/types/models/classroom-subjects';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Summative, SummativesPaginated } from '@/types/models/summatives';
import { ColumnDef, Table as TanstackTable } from '@tanstack/react-table';
import { Pencil, Trash2, Trophy } from 'lucide-react';
import { SummativesTableFilters } from './summatives-table-filters';

export const getColumns = (
    schoolAcademicYear: SchoolAcademicYear,
    classroom: Classroom,
    classroomSubject: ClassroomSubject,
): ColumnDef<Summative>[] => [
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
    { id: 'no', header: 'No.', cell: ({ row, table }) => ((table.options.meta as TableMeta)?.from ?? 0) + row.index },
    { accessorKey: 'name', header: ({ column }) => <DataTableColumnHeader column={column} title="Nama Sumatif" /> },
    { accessorKey: 'identifier', header: ({ column }) => <DataTableColumnHeader column={column} title="Identifier" /> },
    { accessorFn: (row) => row.summative_type?.name ?? 'N/A', id: 'summative_type', header: 'Jenis' },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => {
            const summative = row.original;
            return (
                <div className="flex gap-2">
                    <TableTooltipAction info="Lihat Nilai">
                        <Button variant="outline" size="icon" onClick={() => alert('Fitur Lihat Nilai akan segera hadir!')}>
                            <Trophy className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction>
                    <TableTooltipAction info="Edit">
                        <Button variant="outline" size="icon" onClick={() => alert('Fitur Edit Sumatif akan segera hadir!')}>
                            <Pencil className="h-4 w-4" />
                        </Button>
                    </TableTooltipAction>
                </div>
            );
        },
    },
];

interface SummativesTableProps {
    summatives: SummativesPaginated;
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    classroomSubject: ClassroomSubject;
}

export function SummativesTable({ summatives, schoolAcademicYear, classroom, classroomSubject }: SummativesTableProps) {
    const handleBulkDelete = (table: TanstackTable<Summative>) => {
        const selectedIds = Object.keys(table.getState().rowSelection);
        // router.post(route('...'), { ids: selectedIds }, { ... });
    };

    const columns = getColumns(schoolAcademicYear, classroom, classroomSubject);

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
