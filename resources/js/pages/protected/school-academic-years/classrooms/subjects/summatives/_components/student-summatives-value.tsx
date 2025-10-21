import React, { FC, useState, useMemo, useRef } from 'react';
import * as XLSX from 'xlsx';
import { saveAs } from 'file-saver';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { StudentData } from '../values';
import { cn } from '@/lib/utils';
import { useMutation } from '@tanstack/react-query';
import axios from 'axios';
import { toast } from 'sonner';
import { router } from '@inertiajs/react';
import { DownloadCloud } from 'lucide-react';

// Tipe untuk parameter route
type RouteParams = {
    schoolAcademicYear: number | string;
    classroom: number | string;
    classroomSubject: number | string;
}

type HeaderCell = {
    id: string;
    label: string;
    rowSpan?: number;
    colSpan?: number;
    isSticky?: boolean;
    position?: string;
    width?: string;
    minWidth?: string;
    tooltip?: string;
    className?: string;
};

type DataColumn = {
    id: string;
    dataIndex: number;
    renderCell: (student: StudentData, index: number) => React.ReactNode;
};

// --- Komponen Sel Tambahan ---
const EditableCell: FC<{
    student: StudentData;
    summativeKey: string;
    valueIndex: number;
    // Tambahkan props untuk parameter route
    routeParams: RouteParams;
}> = ({ student, summativeKey, valueIndex, routeParams }) => {

    const summativeValue = student.summatives[summativeKey].values[valueIndex];
    const initialValue = summativeValue.score;
    const [open, setOpen] = useState(false);
    const [currentValue, setCurrentValue] = useState(initialValue?.toString() ?? "");

    // Gunakan parameter dari props untuk membuat URL
    const routeUrl = route(
        'protected.school-academic-years.classrooms.subjects.summatives.update-value',
        routeParams
    );

    const mutation = useMutation({
        mutationFn: (newValue: string) => {
            return axios.post(routeUrl, {
                student_id: student.id,
                summative_id: summativeValue.id,
                value: newValue,
            });
        },
        onSuccess: () => {
            setOpen(false);
            toast.success('Nilai berhasil diperbarui!');
            router.reload(); // Gunakan preserveState agar tidak kehilangan state seperti pencarian
        },
        onError: (error) => {
            console.error("Gagal memperbarui nilai:", error);
            toast.error("Terjadi kesalahan saat menyimpan nilai.");
        }
    });

    const handleUpdate = () => {
        mutation.mutate(currentValue);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <TableCell className="text-center border-b border-r p-0 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/50 transition-colors bg-white dark:bg-slate-900">
                    <div className="h-full w-full flex items-center justify-center px-1.5 py-2">
                        {initialValue ?? '-'}
                    </div>
                </TableCell>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Edit Nilai: {student.name}</DialogTitle>
                    <DialogDescription>
                        Anda sedang mengubah nilai untuk {summativeKey} - {summativeValue.name}.
                    </DialogDescription>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                    <div className="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="score" className="text-right">
                            Nilai
                        </Label>
                        <Input
                            id="score"
                            value={currentValue}
                            onChange={(e) => setCurrentValue(e.target.value)}
                            className="col-span-3"
                            autoFocus
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" onClick={() => setOpen(false)}>Batal</Button>
                    <Button onClick={handleUpdate} disabled={mutation.isPending}>
                        {mutation.isPending ? 'Menyimpan...' : 'Simpan Perubahan'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
};


// --- FUNGSI UNTUK MEMBUAT DEFINISI TABEL SECARA DINAMIS ---
// Tambahkan parameter `routeParams` di sini
export const buildTableDefinitionFromData = (sampleStudent: StudentData | undefined, routeParams: RouteParams): { headerRows: HeaderCell[][]; dataColumns: DataColumn[] } => {
    if (!sampleStudent) return { headerRows: [], dataColumns: [] };

    const summativeKeys = Object.keys(sampleStudent.summatives);
    const descriptionKeys = Object.keys(sampleStudent.description);
    const headerRows: HeaderCell[][] = [[], [], []];
    const dataColumns: DataColumn[] = [];
    let dataColumnIndex = 0;

    // ... (kode untuk 'no', 'nomorInduk', 'namaSiswa' tetap sama) ...
    headerRows[0].push(
        { id: 'no', label: 'No', rowSpan: 3, isSticky: true, position: 'left-0', width: 'w-[40px]', minWidth: 'min-w-[40px]' },
        { id: 'nomorInduk', label: 'Nomor Induk', rowSpan: 3, isSticky: true, position: 'left-[40px]', width: 'w-[150px]', minWidth: 'min-w-[150px]' },
        { id: 'namaSiswa', label: 'Nama Siswa', rowSpan: 3, isSticky: true, position: 'left-[190px]', width: 'w-[250px]', minWidth: 'min-w-[250px]' }
    );
    dataColumns.push(
        { id: 'no', dataIndex: dataColumnIndex++, renderCell: (s: StudentData, index: number) => <TableCell className="sticky left-0 bg-white dark:bg-slate-950 z-20 text-center font-medium border-b border-r w-[40px] p-2">{index + 1}</TableCell> },
        { id: 'nomorInduk', dataIndex: dataColumnIndex++, renderCell: (s: StudentData) => <TableCell className="sticky left-[40px] bg-white dark:bg-slate-950 z-20 border-b border-r min-w-[150px] w-[150px] p-2">{s.nisn}</TableCell> },
        { id: 'namaSiswa', dataIndex: dataColumnIndex++, renderCell: (s: StudentData) => <TableCell className="sticky left-[190px] bg-white dark:bg-slate-950 z-20 font-semibold text-gray-800 dark:text-gray-200 border-b border-r min-w-[250px] w-[250px] p-2">{s.name}</TableCell> }
    );

    summativeKeys.forEach(key => {
        const summative = sampleStudent.summatives[key];
        headerRows[0].push({ id: key.replace(/\s+/g, ''), label: key.toUpperCase().replace('SUMATIF', 'S'), colSpan: summative.values.length + 1, tooltip: key, className: 'text-center' });
    });

    summativeKeys.forEach(key => {
        const summative = sampleStudent.summatives[key];
        const isMateri = key.includes('Materi');

        if (isMateri) {
            const materiGroups = summative.values.reduce((acc, { identifier }) => {
                if (identifier) {
                    const groupKey = identifier.charAt(0).toUpperCase() + identifier.slice(1).toLowerCase();
                    acc[groupKey] = (acc[groupKey] || 0) + 1;
                }
                return acc;
            }, {} as { [key: string]: number });
            Object.entries(materiGroups).forEach(([label, span]) => headerRows[1].push({ id: `group${label}`, label, colSpan: span, className: 'text-center font-normal' }));

            summative.values.forEach((m, i) => {
                headerRows[2].push({ id: m.name, label: m.name, className: 'text-center font-normal' });
                dataColumns.push({
                    id: `${key}-${m.name}`, dataIndex: dataColumnIndex++,
                    // Teruskan `routeParams` ke EditableCell
                    renderCell: (s: StudentData) => <EditableCell student={s} summativeKey={key} valueIndex={i} routeParams={routeParams} />
                });
            });
        } else {
            summative.values.forEach((v, i) => {
                headerRows[1].push({ id: `${key}${v.name}`, label: v.name.toUpperCase(), rowSpan: 2, className: 'text-center align-middle' });
                dataColumns.push({
                    id: `${key}-${v.name}`, dataIndex: dataColumnIndex++,
                    // Teruskan `routeParams` ke EditableCell
                    renderCell: (s: StudentData) => <EditableCell student={s} summativeKey={key} valueIndex={i} routeParams={routeParams} />
                });
            });
        }
        const meanLabel = isMateri ? '(S)' : 'NA';
        headerRows[1].push({ id: `${key}Mean`, label: meanLabel, rowSpan: 2, tooltip: `Rata-rata ${key}`, className: 'text-center font-bold align-middle' });
        dataColumns.push({
            id: `${key}-mean`, dataIndex: dataColumnIndex++,
            renderCell: (s: StudentData) => <TableCell className="text-center font-bold bg-orange-50 dark:bg-orange-900/50 border-b border-r p-2">{isMateri ? s.summatives[key].mean.toFixed(1) : s.summatives[key].mean}</TableCell>
        });
    });

    headerRows[0].push(
        { id: 'nr', label: 'NR', rowSpan: 3, tooltip: 'Nilai Rapor Akhir', className: 'text-center' },
        { id: 'deskripsi', label: 'Deskripsi', colSpan: descriptionKeys.length, className: 'text-center' }
    );
    dataColumns.push({
        id: 'nr', dataIndex: dataColumnIndex++,
        renderCell: (s: StudentData) => <TableCell className="text-center font-extrabold text-lg bg-orange-50 dark:bg-orange-900/50 border-b border-r p-2">{s.nr}</TableCell>
    });

    descriptionKeys.forEach(key => {
        const isLongText = key.includes('Menonjol') || key.includes('Ditingkatkan');
        headerRows[1].push({ id: `desc${key.replace(/\s+/g, '')}`, label: key, rowSpan: 2, className: `text-center align-middle ${isLongText ? 'w-[300px]' : ''}` });
        dataColumns.push({
            id: `desc-${key}`, dataIndex: dataColumnIndex++,
            renderCell: (s: StudentData) => (
                <TableCell className={cn(
                    'bg-orange-50 dark:bg-orange-900/50 border-b border-r p-2',
                    isLongText
                        ? 'text-xs text-gray-600 align-top w-[300px] whitespace-normal'
                        : 'text-center'
                )}>
                    {s.description[key] || '-'}
                </TableCell>
            )
        });
    });

    const sortedDataColumns = dataColumns.sort((a, b) => a.dataIndex - b.dataIndex);
    const finalHeaderRows = headerRows.filter(row => row.length > 0);
    return { headerRows: finalHeaderRows, dataColumns: sortedDataColumns };
};


// Komponen StudentSummativeValues tidak perlu diubah sama sekali
export const StudentSummativeValues = ({ studentData, headerRows, dataColumns }: { studentData: StudentData[], headerRows: HeaderCell[][], dataColumns: DataColumn[] }) => {
    // ... (kode di dalam komponen ini tetap sama) ...
    const [searchTerm, setSearchTerm] = useState<string>('');
    const tableRef = useRef<HTMLTableElement>(null);

    const filteredData = useMemo(() => {
        if (!searchTerm) return studentData;
        return studentData.filter(student =>
            student.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            student.nisn.includes(searchTerm)
        );
    }, [searchTerm, studentData]);

    const handleExport = () => {
        // Pastikan tabel sudah ada di DOM
        if (!tableRef.current) {
            toast.error("Tabel tidak ditemukan untuk diekspor.");
            return;
        }

        // 1. Konversi elemen tabel HTML langsung menjadi workbook
        // Fungsi ini secara otomatis menghargai rowspan dan colspan!
        const workbook = XLSX.utils.table_to_book(tableRef.current, { sheet: "Nilai Siswa" });

        // 2. Buat file buffer dan trigger download (sama seperti sebelumnya)
        const excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' });
        const blob = new Blob([excelBuffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8" });

        saveAs(blob, "rekap_nilai_siswa.xlsx");
        toast.success("Data berhasil diekspor sesuai tampilan!");
    };

    return (
        <div className="bg-gray-50 dark:bg-slate-900 min-h-screen p-4 sm:p-6 lg:p-8">
            <div className="max-w-full mx-auto">
                <Card className="w-full">
                    <CardContent>
                        <TooltipProvider>
                            <div className="relative overflow-x-auto border rounded-md">
                                <Table ref={tableRef}>
                                    <TableHeader>
                                        {headerRows.map((row, rowIndex) => (
                                            <TableRow key={`header-row-${rowIndex}`} className="bg-gray-50 hover:bg-gray-100 dark:bg-slate-800 dark:hover:bg-slate-700 border-none">
                                                {row.map((cell) => {
                                                    const stickyClasses = cell.isSticky ? `sticky ${cell.position} bg-gray-50 dark:bg-slate-800 z-30` : '';
                                                    const widthClasses = `${cell.width || ''} ${cell.minWidth || ''}`;
                                                    const borderClasses = 'border-b border-r border-slate-200 dark:border-slate-700 p-2';

                                                    return (
                                                        <TableHead
                                                            key={`header-cell-${rowIndex}-${cell.id}`}
                                                            rowSpan={cell.rowSpan}
                                                            colSpan={cell.colSpan}
                                                            className={cn(`${stickyClasses} ${widthClasses} ${borderClasses}`, cell.className)}
                                                        >
                                                            {cell.tooltip ? (
                                                                <Tooltip>
                                                                    <TooltipTrigger className="cursor-help">{cell.label}</TooltipTrigger>
                                                                    <TooltipContent>{cell.tooltip}</TooltipContent>
                                                                </Tooltip>
                                                            ) : (
                                                                cell.label
                                                            )}
                                                        </TableHead>
                                                    );
                                                })}
                                            </TableRow>
                                        ))}
                                    </TableHeader>
                                    <TableBody>
                                        {filteredData.map((student, index) => (
                                            <TableRow key={student.id} className="hover:bg-gray-100 dark:hover:bg-slate-800 border-none">
                                                {dataColumns.map(column => (
                                                    <React.Fragment key={`${student.id}-${column.id}`}>
                                                        {column.renderCell(student, index)}
                                                    </React.Fragment>
                                                ))}
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        </TooltipProvider>
                        {filteredData.length === 0 && (
                            <div className="text-center py-10 text-gray-500">
                                <p>Tidak ada data siswa yang cocok dengan pencarian Anda.</p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </div>
    );
};
