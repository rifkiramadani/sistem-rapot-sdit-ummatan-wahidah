'use client';

import {
    ColumnDef,
    flexRender,
    getCoreRowModel,
    OnChangeFn,
    RowSelectionState,
    SortingState,
    Table as TanstackTable,
    useReactTable,
    VisibilityState,
} from '@tanstack/react-table';

import { DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { ReactNode } from 'react';
import { Button } from './ui/button';

interface DataTableProps<TData, TValue> {
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    meta?: any;
    // children?: ReactNode;
    children?: ReactNode | ((table: TanstackTable<TData>) => ReactNode);
}

export function DataTable<TData, TValue>({ columns, data, meta, children }: DataTableProps<TData, TValue>) {
    // Gunakan PageProps kustom Anda untuk inferensi tipe yang lebih baik
    const { props } = usePage<SharedData>();
    const { queryParams } = props;

    // 1. HAPUS useState dan useEffect untuk sorting.

    // 2. State sorting sekarang diturunkan LANGSUNG dari queryParams di setiap render.
    //    Ini adalah "single source of truth" kita.
    const sorting: SortingState = [
        {
            id: queryParams?.sort_by || 'name', // Gunakan optional chaining untuk keamanan
            desc: queryParams?.sort_direction === 'desc',
        },
    ];

    const hiddenColumnsStr = queryParams?.hidden_columns || '';
    const columnVisibility: VisibilityState = hiddenColumnsStr
        .split(',')
        .filter(Boolean)
        .reduce((acc: VisibilityState, columnId: string) => {
            acc[columnId] = false;
            return acc;
        }, {} as VisibilityState);

    // 3. Buat handler untuk mengubah URL saat visibilitas diubah.
    const handleColumnVisibilityChange: OnChangeFn<VisibilityState> = (updater) => {
        const newVisibility = updater instanceof Function ? updater(columnVisibility) : updater;

        // Cari semua kolom yang disembunyikan (yang nilainya 'false')
        const hiddenColumns = Object.entries(newVisibility)
            .filter(([, isVisible]) => !isVisible)
            .map(([columnId]) => columnId);

        // Gabungkan kembali menjadi string "npsn,academicYear"
        const newHiddenColumnsStr = hiddenColumns.join(',');

        router.get(
            window.location.pathname,
            {
                ...queryParams,
                // Gunakan 'undefined' untuk menghapus parameter dari URL jika tidak ada kolom yang disembunyikan
                hidden_columns: newHiddenColumnsStr || undefined,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            },
        );
    };

    // 3. Buat handler untuk menangani perubahan sorting secara langsung
    const handleSortingChange: OnChangeFn<SortingState> = (updater) => {
        // Dapatkan state sorting yang baru
        const newSorting = updater instanceof Function ? updater(sorting) : updater;
        const sortBy = newSorting[0]?.id;
        const sortDirection = newSorting[0]?.desc ? 'desc' : 'asc';

        // Jika sorting dihapus, jangan kirim parameternya
        if (!sortBy) {
            return;
        }

        // Panggil router.get secara langsung
        router.get(
            window.location.pathname, // Gunakan pathname agar lebih bersih
            {
                ...queryParams, // Pertahankan parameter lain seperti 'per_page'
                sort_by: sortBy,
                sort_direction: sortDirection,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true, // Jaga posisi scroll
            },
        );
    };

    const selectedRowsStr = queryParams?.selected_rows || '';
    const rowSelection: RowSelectionState = selectedRowsStr
        .split(',')
        .filter(Boolean)
        .reduce((acc: RowSelectionState, rowIndex: string) => {
            acc[rowIndex] = true; // Set baris dengan indeks ini sebagai terpilih
            return acc;
        }, {} as RowSelectionState);
    const handleRowSelectionChange: OnChangeFn<RowSelectionState> = (updater) => {
        const newRowSelection = updater instanceof Function ? updater(rowSelection) : updater;

        // Ambil semua kunci (indeks baris) dari objek selection
        const selectedRowIndexes = Object.keys(newRowSelection);

        // Gabungkan menjadi string "0,2,5"
        const newSelectedRowsStr = selectedRowIndexes.join(',');

        router.get(
            window.location.pathname,
            { ...queryParams, selected_rows: newSelectedRowsStr || undefined },
            { preserveState: true, replace: true, preserveScroll: true },
        );
    };

    const table = useReactTable({
        data,
        columns,
        meta,
        manualSorting: true,
        // 4. Gunakan handler dan state yang baru
        onSortingChange: handleSortingChange,
        onColumnVisibilityChange: handleColumnVisibilityChange,
        onRowSelectionChange: handleRowSelectionChange,
        state: {
            sorting, // State sorting yang diturunkan dari props
            columnVisibility,
            rowSelection,
        },
        getCoreRowModel: getCoreRowModel(),
    });

    return (
        <div>
            <div className="flex items-center py-4">
                <div className="flex flex-1 items-center space-x-2">
                    {/* Di sini perubahannya: */}
                    {/* Jika `children` adalah fungsi, panggil dengan `table`. Jika tidak, render seperti biasa. */}
                    {typeof children === 'function' ? children(table) : children}
                </div>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline" className="ml-auto">
                            Columns
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        {table
                            .getAllColumns()
                            .filter((column) => column.getCanHide())
                            .map((column) => {
                                return (
                                    <DropdownMenuCheckboxItem
                                        key={column.id}
                                        className="capitalize"
                                        checked={column.getIsVisible()}
                                        onCheckedChange={(value) => column.toggleVisibility(!!value)}
                                    >
                                        {column.id}
                                    </DropdownMenuCheckboxItem>
                                );
                            })}
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
            <div className="overflow-hidden rounded-md border">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    return (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                                        </TableHead>
                                    );
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow key={row.id} data-state={row.getIsSelected() && 'selected'}>
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="h-24 text-center">
                                    No results.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>
            <div className="flex items-center justify-between px-2">
                <div className="flex-1 text-sm text-muted-foreground">
                    {table.getFilteredSelectedRowModel().rows.length} of {table.getFilteredRowModel().rows.length} row(s) selected.
                </div>
            </div>
        </div>
    );
}
