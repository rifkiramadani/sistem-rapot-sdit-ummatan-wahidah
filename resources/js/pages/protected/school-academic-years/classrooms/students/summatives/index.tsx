import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { ClassroomStudent } from '@/types/models/classroom-students';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Head, Link } from '@inertiajs/react';
import { User, FileText, ArrowLeft, Download } from 'lucide-react';

interface Student {
    id: string;
    name: string;
    nisn: string;
}

interface Summative {
    id: string;
    name: string;
    identifier?: string;
    description?: string;
    type: string;
    subject: string;
    student_value?: number;
    student_summative_id?: string;
    classroom_subject_id: string;
}

interface IndexProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    classroomStudent: ClassroomStudent & {
        student: Student;
    };
    summatives: Summative[];
}

export default function Index({ schoolAcademicYear, classroom, classroomStudent, summatives }: IndexProps) {
    const student = classroomStudent.student;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }),
        },
        {
            title: 'Kelas',
            href: route('protected.school-academic-years.classrooms.index', { schoolAcademicYear: schoolAcademicYear.id }),
        },
        {
            title: classroom.name,
            href: route('protected.school-academic-years.classrooms.show', { schoolAcademicYear: schoolAcademicYear.id, classroom: classroom.id }),
        },
        {
            title: 'Daftar Siswa',
            href: route('protected.school-academic-years.classrooms.students.index', {
                schoolAcademicYear: schoolAcademicYear.id,
                classroom: classroom.id,
            }),
        },
        {
            title: student.name,
            href: route('protected.school-academic-years.classrooms.students.show', {
                schoolAcademicYear: schoolAcademicYear.id,
                classroom: classroom.id,
                classroomStudent: classroomStudent.id,
            }),
        },
        {
            title: 'Nilai Sumatif',
            href: '#', // Current page
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Nilai Sumatif - ${student.name}`} />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div>
                            <h1 className="text-2xl font-bold">Nilai Sumatif</h1>
                            <p className="text-muted-foreground">
                                Daftar nilai untuk {student.name} ({student.nisn}) - Kelas {classroom.name}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-4">
                        <Button
                            asChild
                            variant="outline"
                            size="sm"
                        >
                            <Link
                                href={route('protected.school-academic-years.classrooms.students.summatives.export-word', {
                                    schoolAcademicYear: schoolAcademicYear.id,
                                    classroom: classroom.id,
                                    classroomStudent: classroomStudent.id,
                                })}
                            >
                                <Download className="mr-2 h-4 w-4" />
                                Export Word
                            </Link>
                        </Button>
                        <div className="flex items-center gap-2">
                            <User className="h-4 w-4" />
                            <Badge variant="secondary">{schoolAcademicYear.academic_year.name}</Badge>
                        </div>
                    </div>
                </div>

                {/* Content */}
                <div className="flex-1 space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5" />
                                Semua Nilai Sumatif
                            </CardTitle>
                            <CardDescription>
                                Nilai untuk semua mata pelajaran di kelas {classroom.name}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {summatives.length === 0 ? (
                                <div className="text-center py-8">
                                    <FileText className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                    <p className="text-muted-foreground">Belum ada nilai untuk siswa ini.</p>
                                </div>
                            ) : (
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Mata Pelajaran</TableHead>
                                            <TableHead>Nama Penilaian</TableHead>
                                            <TableHead>Identitas</TableHead>
                                            <TableHead>Tipe</TableHead>
                                            <TableHead>Nilai</TableHead>
                                            <TableHead>Keterangan</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {summatives.map((summative) => (
                                            <TableRow key={summative.id}>
                                                <TableCell className="font-medium">
                                                    {summative.subject}
                                                </TableCell>
                                                <TableCell>
                                                    {summative.name}
                                                </TableCell>
                                                <TableCell>
                                                    {summative.identifier || '-'}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="outline">
                                                        {summative.type}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    {summative.student_value !== null && summative.student_value !== undefined ? (
                                                        <div className="flex items-center gap-2">
                                                            <span className="font-medium">
                                                                {summative.student_value}
                                                            </span>
                                                        </div>
                                                    ) : (
                                                        <Badge variant="secondary">Belum Dinilai</Badge>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    {summative.description || '-'}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
