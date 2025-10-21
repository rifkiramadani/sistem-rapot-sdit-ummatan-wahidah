import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { BookOpen, ArrowLeft, User, FileText } from 'lucide-react';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';

interface Subject {
    id: string;
    name: string;
    description?: string;
}

interface Student {
    id: string;
    name: string;
    nisn: string;
}

interface SchoolAcademicYear {
    id: string;
    academic_year: {
        name: string;
    };
}

interface StudentSummative {
    id: string;
    value?: number;
    summative: {
        id: string;
        name: string;
        identifier?: string;
        description?: string;
        classroomSubject: {
            subject: {
                id: string;
                name: string;
            };
        };
    };
}

interface SubjectDetailProps {
    schoolAcademicYear: SchoolAcademicYear;
    student: Student;
    subject: Subject;
    studentSummatives: StudentSummative[];
}

export default function SubjectDetail({
    schoolAcademicYear,
    student,
    subject,
    studentSummatives
}: SubjectDetailProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id })
        },
        {
            title: 'Siswa',
            href: route('protected.school-academic-years.students.index', { schoolAcademicYear: schoolAcademicYear.id })
        },
        {
            title: 'Mata Pelajaran',
            href: route('protected.school-academic-years.students.subjects', {
                schoolAcademicYear: schoolAcademicYear.id,
                student: student.id,
            }),
        },
        {
            title: 'Detail',
            href: '#', // Current page
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail ${subject.name} - ${student.name}`} />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">{subject.name}</h1>
                        <p className="text-muted-foreground">
                            Detail nilai untuk {student.name} ({student.nisn})
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <User className="h-4 w-4" />
                        <Badge variant="secondary">{schoolAcademicYear.academic_year.name}</Badge>
                    </div>
                </div>

                {/* Subject Info */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <BookOpen className="h-5 w-5" />
                            Informasi Mata Pelajaran
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <p className="font-semibold">Nama Mata Pelajaran</p>
                                <p className="text-muted-foreground">{subject.name}</p>
                            </div>
                            {subject.description && (
                                <div>
                                    <p className="font-semibold">Deskripsi</p>
                                    <p className="text-muted-foreground">{subject.description}</p>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
