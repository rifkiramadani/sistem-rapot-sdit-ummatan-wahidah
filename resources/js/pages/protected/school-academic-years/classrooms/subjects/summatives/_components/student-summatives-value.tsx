import React, { useState, useMemo } from 'react';

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

// --- Komponen Tambahan untuk Sel yang Bisa Diciutkan ---
const CollapsibleCell = ({ text }: { text: string }) => {
    const [isExpanded, setIsExpanded] = useState(false);
    const maxLength = 50; // Jumlah karakter sebelum disembunyikan

    // Jika teksnya pendek, tidak perlu diciutkan
    if (text.length <= maxLength) {
        return <TableCell className="text-sm text-gray-600">{text}</TableCell>;
    }

    return (
        <TableCell className="text-sm text-gray-600 align-top">
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


// --- Data Siswa ---
const studentData = [
    { no: 1, nomorInduk: '13131097134', nisn: '', nama: 'Aisyah Aqila Ahda', m: [69, 75, 88, 69, 88, null, null, null, null, null, null, null, null, null, null, null], s: 77.8, sts: { tes: 69, nonTes: 69, na: 69 }, sas: { tes: 88, nonTes: 88, na: 88 }, nr: 78, unggulId: 'M3', kurangId: 'M1', menonjol: 'Menunjukkan penguasaan yang baik tentang memahami makna menghargai berbagai perbedaan (suku, agama, dan pendapat)', peningkatan: "Perlu bantuan pemahaman mengenai memahami makna isi pokok surah al-'Alaq dengan benar" },
    { no: 2, nomorInduk: '3145391613', nisn: '', nama: 'Al-Azka Habib Wibowo', m: [69, 70, 70, 69, 70, null, null, null, null, null, null, null, null, null, null, null], s: 69.6, sts: { tes: 69, nonTes: 69, na: 69 }, sas: { tes: 70, nonTes: 70, na: 70 }, nr: 70, unggulId: 'M2', kurangId: 'M1', menonjol: 'Menunjukkan penguasaan yang baik tentang menemukan keterkaitan asmaulhusna al-ahad, al-qayyum, al-muhyi, dan al-mumit dengan prilaku sehari-hari', peningkatan: "Perlu bantuan pemahaman mengenai memahami makna isi pokok surah al-'Alaq dengan benar" },
    { no: 3, nomorInduk: '146571512', nisn: '', nama: 'Alif Satriya Amrozi', m: [73, 65, 70, 73, 70, null, null, null, null, null, null, null, null, null, null, null], s: 70.2, sts: { tes: 73, nonTes: 73, na: 73 }, sas: { tes: 70, nonTes: 70, na: 70 }, nr: 71, unggulId: 'M1', kurangId: 'M2', menonjol: "Menunjukkan penguasaan yang baik tentang memahami makna isi pokok surah al-'Alaq dengan benar", peningkatan: 'Perlu bantuan pemahaman mengenai menemukan keterkaitan asmaulhusna al-ahad, al-qayyum, al-muhyi, dan al-mumit dengan prilaku sehari-hari' },
    { no: 4, nomorInduk: '3144590110', nisn: '', nama: 'Aqila Andini', m: [69, 70, 70, 69, 70, null, null, null, null, null, null, null, null, null, null, null], s: 69.6, sts: { tes: 69, nonTes: 69, na: 69 }, sas: { tes: 70, nonTes: 70, na: 70 }, nr: 70, unggulId: 'M2', kurangId: 'M1', menonjol: 'Menunjukkan penguasaan yang baik tentang menemukan keterkaitan asmaulhusna al-ahad, al-qayyum, al-muhyi, dan al-mumit dengan prilaku sehari-hari', peningkatan: "Perlu bantuan pemahaman mengenai memahami makna isi pokok surah al-'Alaq dengan benar" },
    { no: 5, nomorInduk: '3140554902', nisn: '', nama: 'Arganta Yuda A.Z', m: [65, 70, 75, 65, 75, null, null, null, null, null, null, null, null, null, null, null], s: 70, sts: { tes: 65, nonTes: 65, na: 65 }, sas: { tes: 75, nonTes: 75, na: 75 }, nr: 70, unggulId: 'M3', kurangId: 'M1', menonjol: 'Menunjukkan penguasaan yang baik tentang memahami makna menghargai berbagai perbedaan (suku, agama, dan pendapat)', peningkatan: "Perlu bantuan pemahaman mengenai memahami makna isi pokok surah al-'Alaq dengan benar" },
    { no: 6, nomorInduk: '3141669787', nisn: '', nama: 'Deby Geisya Putri', m: [85, 75, 93, 85, 93, null, null, null, null, null, null, null, null, null, null, null], s: 86.2, sts: { tes: 85, nonTes: 85, na: 85 }, sas: { tes: 93, nonTes: 93, na: 93 }, nr: 88, unggulId: 'M3', kurangId: 'M2', menonjol: 'Menunjukkan penguasaan yang baik tentang memahami makna menghargai berbagai perbedaan (suku, agama, dan pendapat)', peningkatan: 'Perlu bantuan pemahaman mengenai menemukan keterkaitan asmaulhusna al-ahad, al-qayyum, al-muhyi, dan al-mumit dengan prilaku sehari-hari' },
    { no: 7, nomorInduk: '3141956430', nisn: '', nama: 'Dioba Rizki Sapawi', m: [73, 70, 70, 73, 70, null, null, null, null, null, null, null, null, null, null, null], s: 71.2, sts: { tes: 73, nonTes: 73, na: 73 }, sas: { tes: 70, nonTes: 70, na: 70 }, nr: 71, unggulId: 'M1', kurangId: 'M2', menonjol: "Menunjukkan penguasaan yang baik tentang memahami makna isi pokok surah al-'Alaq dengan benar", peningkatan: 'Perlu bantuan pemahaman mengenai menemukan keterkaitan asmaulhusna al-ahad, al-qayyum, al-muhyi, dan al-mumit dengan prilaku sehari-hari' },
    { no: 8, nomorInduk: '3145395000', nisn: '', nama: 'Er. Hafidzah Jihani Saputri', m: [85, 75, 88, 85, 88, null, null, null, null, null, null, null, null, null, null, null], s: 84.2, sts: { tes: 85, nonTes: 85, na: 85 }, sas: { tes: 88, nonTes: 88, na: 88 }, nr: 86, unggulId: 'M3', kurangId: 'M2', menonjol: 'Menunjukkan penguasaan yang baik tentang memahami makna menghargai berbagai perbedaan (suku, agama, dan pendapat)', peningkatan: 'Perlu bantuan pemahaman mengenai menemukan keterkaitan asmaulhusna al-ahad, al-qayyum, al-muhyi, dan al-mumit dengan prilaku sehari-hari' },
    { no: 9, nomorInduk: '3143453595', nisn: '', nama: 'Fariz Naufal', m: [65, 70, 75, 65, 75, null, null, null, null, null, null, null, null, null, null, null], s: 70, sts: { tes: 65, nonTes: 65, na: 65 }, sas: { tes: 75, nonTes: 75, na: 75 }, nr: 70, unggulId: 'M3', kurangId: 'M1', menonjol: 'Menunjukkan penguasaan yang baik tentang memahami makna menghargai berbagai perbedaan (suku, agama, dan pendapat)', peningkatan: "Perlu bantuan pemahaman mengenai memahami makna isi pokok surah al-'Alaq dengan benar" },
    { no: 10, nomorInduk: '137283607', nisn: '', nama: 'Iman Yusuf Satriyo', m: [69, 70, 70, 69, 70, null, null, null, null, null, null, null, null, null, null, null], s: 69.6, sts: { tes: 69, nonTes: 69, na: 69 }, sas: { tes: 70, nonTes: 70, na: 70 }, nr: 70, unggulId: 'M2', kurangId: 'M1', menonjol: 'Menunjukkan penguasaan yang baik tentang menemukan keterkaitan asmaulhusna al-ahad, al-qayyum, al-muhyi, dan al-mumit dengan prilaku sehari-hari', peningkatan: "Perlu bantuan pemahaman mengenai memahami makna isi pokok surah al-'Alaq dengan benar" },
];


export default function App() {
    const [searchTerm, setSearchTerm] = useState('');

    const filteredData = useMemo(() => {
        if (!searchTerm) return studentData;
        return studentData.filter(student =>
            student.nama.toLowerCase().includes(searchTerm.toLowerCase()) ||
            student.nomorInduk.includes(searchTerm)
        );
    }, [searchTerm]);

    const tableHeaders = {
        s: "Nilai Rata-rata Sumatif Lingkup Materi",
        sts: "Sumatif Tengah Semester",
        sas: "Sumatif Akhir Semester",
        nr: "Nilai Rapor Akhir (S+STS+SAS)/3",
        na: "Nilai Akhir"
    };

    return (
        <div className="bg-gray-50 min-h-screen p-4 sm:p-6 lg:p-8">
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="mr-2 h-4 w-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><polyline points="7 10 12 15 17 10" /><line x1="12" x2="12" y1="15" y2="3" /></svg>
                                Ekspor Data
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <TooltipProvider>
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-gray-50 hover:bg-gray-100">
                                        <TableHead rowSpan={2} className="sticky left-0 bg-gray-50 dark:bg-gray-800 z-30 min-w-[40px] w-[40px] shadow-[1px_0_0_0_theme(colors.slate.200)] dark:shadow-[1px_0_0_0_theme(colors.slate.700)]">No</TableHead>
                                        <TableHead rowSpan={2} className="sticky left-[40px] bg-gray-50 dark:bg-gray-800 z-30 min-w-[150px] w-[150px] shadow-[1px_0_0_0_theme(colors.slate.200)] dark:shadow-[1px_0_0_0_theme(colors.slate.700)]">Nomor Induk</TableHead>
                                        <TableHead rowSpan={2} className="sticky left-[190px] bg-gray-50 dark:bg-gray-800 z-30 min-w-[250px] w-[250px] shadow-[1px_0_0_0_theme(colors.slate.200)] dark:shadow-[1px_0_0_0_theme(colors.slate.700)]">Nama Siswa</TableHead>
                                        <TableHead colSpan={17}>SUMATIF LINGKUP MATERI</TableHead>
                                        <TableHead colSpan={3}>
                                            <Tooltip>
                                                <TooltipTrigger>{tableHeaders.sts}</TooltipTrigger>
                                                <TooltipContent>{tableHeaders.sts}</TooltipContent>
                                            </Tooltip>
                                        </TableHead>
                                        <TableHead colSpan={3}>
                                            <Tooltip>
                                                <TooltipTrigger>{tableHeaders.sas}</TooltipTrigger>
                                                <TooltipContent>{tableHeaders.sas}</TooltipContent>
                                            </Tooltip>
                                        </TableHead>
                                        <TableHead rowSpan={2}>
                                            <Tooltip>
                                                <TooltipTrigger>{tableHeaders.nr}</TooltipTrigger>
                                                <TooltipContent>{tableHeaders.nr}</TooltipContent>
                                            </Tooltip>
                                        </TableHead>
                                        <TableHead colSpan={4}>Deskripsi</TableHead>
                                    </TableRow>
                                    <TableRow className="bg-gray-50 hover:bg-gray-100">
                                        {/* Sumatif Materi */}
                                        {[...Array(16)].map((_, i) => <TableHead key={`m${i + 1}`}>M{i + 1}</TableHead>)}
                                        <TableHead className="font-bold bg-gray-100">
                                            <Tooltip>
                                                <TooltipTrigger>{tableHeaders.s}</TooltipTrigger>
                                                <TooltipContent>{tableHeaders.s}</TooltipContent>
                                            </Tooltip>
                                        </TableHead>

                                        {/* STS */}
                                        <TableHead>TES</TableHead>
                                        <TableHead>NONTES</TableHead>
                                        <TableHead className="font-bold bg-gray-100">
                                            <Tooltip>
                                                <TooltipTrigger>{tableHeaders.na}</TooltipTrigger>
                                                <TooltipContent>{tableHeaders.na}</TooltipContent>
                                            </Tooltip>
                                        </TableHead>

                                        {/* SAS */}
                                        <TableHead>TES</TableHead>
                                        <TableHead>NONTES</TableHead>
                                        <TableHead className="font-bold bg-gray-100">
                                            <Tooltip>
                                                <TooltipTrigger>{tableHeaders.na}</TooltipTrigger>
                                                <TooltipContent>{tableHeaders.na}</TooltipContent>
                                            </Tooltip>
                                        </TableHead>

                                        {/* Deskripsi */}
                                        <TableHead>Materi Unggul</TableHead>
                                        <TableHead>Materi Kurang</TableHead>
                                        <TableHead className="min-w-[300px]">Materi Paling Menonjol</TableHead>
                                        <TableHead className="min-w-[300px]">Materi Yang Perlu Ditingkatkan</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredData.map((student) => (
                                        <TableRow key={student.no} className="hover:bg-gray-100 dark:hover:bg-slate-800">
                                            <TableCell className="sticky left-0 bg-white dark:bg-slate-950 z-20 text-center font-medium shadow-[1px_0_0_0_theme(colors.slate.200)] dark:shadow-[1px_0_0_0_theme(colors.slate.700)] w-[40px]">{student.no}</TableCell>
                                            <TableCell className="sticky left-[40px] bg-white dark:bg-slate-950 z-20 shadow-[1px_0_0_0_theme(colors.slate.200)] dark:shadow-[1px_0_0_0_theme(colors.slate.700)] min-w-[150px] w-[150px]">{student.nomorInduk}</TableCell>
                                            <TableCell className="sticky left-[190px] bg-white dark:bg-slate-950 z-20 font-semibold text-gray-800 dark:text-gray-200 shadow-[1px_0_0_0_theme(colors.slate.200)] dark:shadow-[1px_0_0_0_theme(colors.slate.700)] min-w-[250px] w-[250px]">{student.nama}</TableCell>

                                            {/* Nilai Sumatif Materi */}
                                            {student.m.map((score, i) => (
                                                <TableCell key={`score-${i}`} className="text-center">{score !== null ? score : '-'}</TableCell>
                                            ))}
                                            <TableCell className="text-center font-bold bg-gray-50">{student.s.toFixed(1)}</TableCell>

                                            {/* Nilai STS */}
                                            <TableCell className="text-center">{student.sts.tes}</TableCell>
                                            <TableCell className="text-center">{student.sts.nonTes}</TableCell>
                                            <TableCell className="text-center font-bold bg-gray-50">{student.sts.na}</TableCell>

                                            {/* Nilai SAS */}
                                            <TableCell className="text-center">{student.sas.tes}</TableCell>
                                            <TableCell className="text-center">{student.sas.nonTes}</TableCell>
                                            <TableCell className="text-center font-bold bg-gray-50">{student.sas.na}</TableCell>

                                            {/* Nilai Rapor */}
                                            <TableCell className="text-center font-extrabold text-lg text-blue-600 bg-blue-50">{student.nr}</TableCell>

                                            {/* Deskripsi */}
                                            <TableCell className="text-center">{student.unggulId}</TableCell>
                                            <TableCell className="text-center">{student.kurangId}</TableCell>
                                            <CollapsibleCell text={student.menonjol} />
                                            <CollapsibleCell text={student.peningkatan} />
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
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
}

