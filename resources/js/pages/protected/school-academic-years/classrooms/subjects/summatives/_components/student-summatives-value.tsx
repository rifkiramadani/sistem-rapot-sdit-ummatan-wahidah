import React, { FC, useState, useMemo } from 'react';

// --- Komponen UI dari ShadCN ---
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

// --- Definisi Tipe Data ---
type SummativeValue = {
    name: string;
    identifier: string | null;
    score: number | null;
};

type SummativeCategory = {
    values: SummativeValue[];
    mean: number;
};

type StudentData = {
    no: number;
    nisn: string;
    nomorInduk: string;
    name: string;
    nr: number;
    summatives: {
        [key: string]: SummativeCategory;
    };
    description: {
        [key: string]: string;
    };
};

type CollapsibleCellProps = {
    text: string | null | undefined;
};

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
    renderCell: (student: StudentData) => React.ReactNode;
};


// --- Komponen Tambahan untuk Sel yang Bisa Diciutkan ---
const CollapsibleCell: FC<CollapsibleCellProps> = ({ text }) => {
    const [isExpanded, setIsExpanded] = useState(false);
    const maxLength = 50; // Jumlah karakter sebelum disembunyikan

    if (!text || text.length <= maxLength) {
        return <TableCell className="text-sm text-gray-600 border-r border-b p-2 align-top">{text || '-'}</TableCell>;
    }

    return (
        <TableCell className="text-sm text-gray-600 align-top border-r border-b p-2">
            <div>
                {isExpanded ? text : `${text.substring(0, maxLength)}...`}
                <Button
                    variant="link"
                    size="sm"
                    className="p-0 h-auto ml-1 text-blue-600 font-semibold"
                    onClick={() => setIsExpanded(!isExpanded)}
                >
                    {isExpanded ? 'Sembunyikan' : 'Lihat'}
                </Button>
            </div>
        </TableCell>
    );
};

// --- DATA UTAMA (Single Source of Truth) ---
const studentData: StudentData[] = [
    {
        no: 1,
        nisn: '',
        nomorInduk: '13131097134',
        name: 'Aisyah Aqila Ahda',
        nr: 78,
        summatives: {
            'Sumatif Materi': {
                values: [
                    { name: 'M1', identifier: 'ISLAM', score: 69 }, { name: 'M2', identifier: 'ISLAM', score: 75 }, { name: 'M3', identifier: 'ISLAM', score: 88 }, { name: 'M4', identifier: 'ISLAM', score: 69 }, { name: 'M5', identifier: 'ISLAM', score: 88 },
                    { name: 'M6', identifier: 'KRISTEN', score: null }, { name: 'M7', identifier: 'KRISTEN', score: null }, { name: 'M8', identifier: 'KRISTEN', score: null }, { name: 'M9', identifier: 'KRISTEN', score: null }, { name: 'M10', identifier: 'KRISTEN', score: null },
                    { name: 'M11', identifier: 'KATOLIK', score: null }, { name: 'M12', identifier: 'KATOLIK', score: null }, { name: 'M13', identifier: 'KATOLIK', score: null }, { name: 'M14', identifier: 'KATOLIK', score: null }, { name: 'M15', identifier: 'KATOLIK', score: null }, { name: 'M16', identifier: 'KATOLIK', score: null }
                ],
                mean: 77.8
            },
            'Sumatif Tengah Semester': {
                values: [{ name: 'TES', identifier: null, score: 69 }, { name: 'NONTES', identifier: null, score: 69 }],
                mean: 69
            },
            'Sumatif Akhir Semester': {
                values: [{ name: 'TES', identifier: null, score: 88 }, { name: 'NONTES', identifier: null, score: 88 }],
                mean: 88
            }
        },
        description: { 'Materi Unggul': 'M3', 'Materi Kurang': 'M1', 'Materi Paling Menonjol': 'Menunjukkan penguasaan yang baik tentang memahami makna menghargai berbagai perbedaan (suku, agama, dan pendapat)', 'Materi Yang Perlu Ditingkatkan': "Perlu bantuan pemahaman mengenai memahami makna isi pokok surah al-'Alaq dengan benar" }
    },
    // Data siswa lainnya akan mengikuti struktur yang sama
];

// --- FUNGSI UNTUK MEMBUAT DEFINISI TABEL SECARA DINAMIS ---
const buildTableDefinitionFromData = (sampleStudent: StudentData | undefined): { headerRows: HeaderCell[][]; dataColumns: DataColumn[] } => {
    if (!sampleStudent) return { headerRows: [], dataColumns: [] };

    // --- SETUP ---
    const summativeKeys = Object.keys(sampleStudent.summatives);
    const descriptionKeys = Object.keys(sampleStudent.description);
    const headerRows: HeaderCell[][] = [[], [], []];
    const dataColumns: DataColumn[] = [];
    let dataColumnIndex = 0;

    // --- 1. STATIC COLUMNS (No, Induk, Nama) ---
    headerRows[0].push(
        { id: 'no', label: 'No', rowSpan: 3, isSticky: true, position: 'left-0', width: 'w-[40px]', minWidth: 'min-w-[40px]' },
        { id: 'nomorInduk', label: 'Nomor Induk', rowSpan: 3, isSticky: true, position: 'left-[40px]', width: 'w-[150px]', minWidth: 'min-w-[150px]' },
        { id: 'namaSiswa', label: 'Nama Siswa', rowSpan: 3, isSticky: true, position: 'left-[190px]', width: 'w-[250px]', minWidth: 'min-w-[250px]' }
    );
    dataColumns.push(
        { id: 'no', dataIndex: dataColumnIndex++, renderCell: (s: StudentData) => <TableCell className="sticky left-0 bg-white dark:bg-slate-950 z-20 text-center font-medium border-b border-r w-[40px] p-2">{s.no}</TableCell> },
        { id: 'nomorInduk', dataIndex: dataColumnIndex++, renderCell: (s: StudentData) => <TableCell className="sticky left-[40px] bg-white dark:bg-slate-950 z-20 border-b border-r min-w-[150px] w-[150px] p-2">{s.nomorInduk}</TableCell> },
        { id: 'namaSiswa', dataIndex: dataColumnIndex++, renderCell: (s: StudentData) => <TableCell className="sticky left-[190px] bg-white dark:bg-slate-950 z-20 font-semibold text-gray-800 dark:text-gray-200 border-b border-r min-w-[250px] w-[250px] p-2">{s.name}</TableCell> }
    );

    // --- 2. DYNAMIC SUMMATIVE COLUMNS ---
    summativeKeys.forEach(key => {
        const summative = sampleStudent.summatives[key];
        headerRows[0].push({
            id: key.replace(/\s+/g, ''),
            label: key.toUpperCase().replace('SUMATIF', 'S'),
            colSpan: summative.values.length + 1,
            tooltip: key,
            className: 'text-center'
        });
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
            Object.entries(materiGroups).forEach(([label, span]) => {
                headerRows[1].push({ id: `group${label}`, label, colSpan: span, className: 'text-center font-normal' });
            });

            summative.values.forEach((m, i) => {
                headerRows[2].push({ id: m.name, label: m.name, className: 'text-center font-normal' });
                dataColumns.push({
                    id: `${key}-${m.name}`, dataIndex: dataColumnIndex++,
                    renderCell: (s: StudentData) => <TableCell className="text-center border-b border-r p-2">{s.summatives[key].values[i].score ?? '-'}</TableCell>
                });
            });
        } else {
            summative.values.forEach((v, i) => {
                headerRows[1].push({ id: `${key}${v.name}`, label: v.name.toUpperCase(), rowSpan: 2, className: 'text-center align-middle' });
                dataColumns.push({
                    id: `${key}-${v.name}`, dataIndex: dataColumnIndex++,
                    renderCell: (s: StudentData) => <TableCell className="text-center border-b border-r p-2">{s.summatives[key].values[i].score}</TableCell>
                });
            });
        }

        const meanLabel = isMateri ? '(S)' : 'NA';
        headerRows[1].push({ id: `${key}Mean`, label: meanLabel, rowSpan: 2, tooltip: `Rata-rata ${key}`, className: 'text-center font-bold align-middle' });
        dataColumns.push({
            id: `${key}-mean`, dataIndex: dataColumnIndex++,
            renderCell: (s: StudentData) => <TableCell className="text-center font-bold bg-gray-50 dark:bg-slate-800 border-b border-r p-2">{isMateri ? s.summatives[key].mean.toFixed(1) : s.summatives[key].mean}</TableCell>
        });
    });

    // --- 3. NR & DESCRIPTION COLUMNS ---
    headerRows[0].push(
        { id: 'nr', label: 'NR', rowSpan: 3, tooltip: 'Nilai Rapor Akhir', className: 'text-center' },
        { id: 'deskripsi', label: 'Deskripsi', colSpan: descriptionKeys.length, className: 'text-center' }
    );
    dataColumns.push({
        id: 'nr', dataIndex: dataColumnIndex++,
        renderCell: (s: StudentData) => <TableCell className="text-center font-extrabold text-lg text-blue-600 bg-blue-50 dark:bg-blue-900/20 border-b border-r p-2">{s.nr}</TableCell>
    });

    descriptionKeys.forEach(key => {
        const isLongText = key.includes('Menonjol') || key.includes('Ditingkatkan');
        headerRows[1].push({
            id: `desc${key.replace(/\s+/g, '')}`, label: key, rowSpan: 2,
            className: `text-center align-middle ${isLongText ? 'min-w-[300px]' : ''}`
        });
        dataColumns.push({
            id: `desc-${key}`, dataIndex: dataColumnIndex++,
            renderCell: (s: StudentData) => isLongText ? <CollapsibleCell text={s.description[key]} /> : <TableCell className="text-center border-b border-r p-2">{s.description[key]}</TableCell>
        });
    });

    const sortedDataColumns = dataColumns.sort((a, b) => a.dataIndex - b.dataIndex);
    const finalHeaderRows = headerRows.filter(row => row.length > 0);
    return { headerRows: finalHeaderRows, dataColumns: sortedDataColumns };
};


const { headerRows, dataColumns } = buildTableDefinitionFromData(studentData[0]);

const App: FC = () => {
    const [searchTerm, setSearchTerm] = useState<string>('');

    const filteredData = useMemo(() => {
        if (!searchTerm) return studentData;
        return studentData.filter(student =>
            student.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            student.nomorInduk.includes(searchTerm)
        );
    }, [searchTerm]);

    return (
        <div className="bg-gray-50 dark:bg-slate-900 min-h-screen p-4 sm:p-6 lg:p-8">
            <div className="max-w-full mx-auto">
                <Card className="w-full">
                    <CardHeader>
                        <CardTitle>Rekapitulasi Nilai Siswa</CardTitle>
                        <CardDescription>
                            Menampilkan rekap nilai sumatif, tengah semester, dan akhir semester untuk seluruh siswa.
                        </CardDescription>
                        <div className="mt-4 flex flex-col sm:flex-row items-center gap-2">
                            <Input
                                placeholder="Cari nama atau nomor induk..."
                                className="w-full sm:w-72"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                            />
                            <Button className="w-full sm:w-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="mr-2 h-4 w-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><polyline points="7 10 12 15 17 10" /><line x1="12" x2="12" y1="15" y2="3" /></svg>
                                Ekspor Data
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <TooltipProvider>
                            <div className="relative overflow-x-auto border rounded-md">
                                <Table>
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
                                                            className={`${stickyClasses} ${widthClasses} ${borderClasses} ${cell.className || ''}`}
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
                                        {filteredData.map((student) => (
                                            <TableRow key={student.no} className="hover:bg-gray-100 dark:hover:bg-slate-800 border-none">
                                                {dataColumns.map(column => (
                                                    <React.Fragment key={`${student.no}-${column.id}`}>
                                                        {column.renderCell(student)}
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

export default App;
