import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { BookOpen, ArrowLeft, User } from 'lucide-react';

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

interface SubjectsIndexProps {
    schoolAcademicYear: SchoolAcademicYear;
    student: Student;
    subjects: Subject[];
}

export default function SubjectsIndex({ schoolAcademicYear, student, subjects }: SubjectsIndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dasbor',
            href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id })
        },
        {
            title: 'Siswa',
            href: route('protected.school-academic-years.students.index', { schoolAcademicYear: schoolAcademicYear.id })
        },
        {
            title: 'Mata Pelajaran',
            href: '#', // Current page
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Mata Pelajaran Siswa" />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div>
                            <h1 className="text-2xl font-bold">Mata Pelajaran</h1>
                            <p className="text-muted-foreground">
                                Daftar mata pelajaran untuk {student.name} ({student.nisn})
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <User className="h-4 w-4" />
                        <Badge variant="secondary">{schoolAcademicYear.academic_year.name}</Badge>
                    </div>
                </div>

                {/* Content */}
                <div className="flex-1 space-y-4">
                    {subjects.length === 0 ? (
                        <Card>
                            <CardContent className="flex flex-col items-center justify-center py-12">
                                <BookOpen className="h-12 w-12 text-muted-foreground mb-4" />
                                <CardTitle className="text-lg mb-2">Belum Ada Mata Pelajaran</CardTitle>
                                <CardDescription>
                                    Siswa ini belum terdaftar dalam mata pelajaran apapun.
                                </CardDescription>
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            {subjects.map((subject) => (
                                <Card key={subject.id} className="hover:shadow-md transition-shadow">
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2">
                                            <BookOpen className="h-5 w-5" />
                                            {subject.name}
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        {subject.description && (
                                            <CardDescription className="mb-4">
                                                {subject.description}
                                            </CardDescription>
                                        )}
                                        <Link href={route('protected.school-academic-years.students.subjects.detail', {
                                            schoolAcademicYear: schoolAcademicYear.id,
                                            student: student.id,
                                            subject: subject.id
                                        })}>
                                            <Button variant="outline" size="sm" className="w-full">
                                                Lihat Detail
                                            </Button>
                                        </Link>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
