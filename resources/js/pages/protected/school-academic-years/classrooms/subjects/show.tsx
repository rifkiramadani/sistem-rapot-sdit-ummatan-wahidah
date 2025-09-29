import { Head, Link } from '@inertiajs/react';

import DetailItem from '@/components/detail-item';
import SectionTitle from '@/components/section-title';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type ClassroomSubject } from '@/types/models/classroom-subjects';
import { type Classroom } from '@/types/models/classrooms';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years';
import { type Subject } from '@/types/models/subjects';
import { BookMarked } from 'lucide-react';

interface ShowProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    classroomSubject: ClassroomSubject & {
        subject: Subject;
    };
}

export default function Show({ schoolAcademicYear, classroom, classroomSubject }: ShowProps) {
    // Ekstrak data mata pelajaran agar lebih mudah diakses
    const subject = classroomSubject.subject;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear }) },
        { title: 'Kelas', href: route('protected.school-academic-years.classrooms.index', { schoolAcademicYear }) },
        { title: classroom.name, href: route('protected.school-academic-years.classrooms.show', { schoolAcademicYear, classroom }) },
        { title: 'Mata Pelajaran', href: route('protected.school-academic-years.classrooms.subjects.index', { schoolAcademicYear, classroom }) },
        { title: subject.name, href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Mapel: ${subject.name}`} />
            <div className="flex h-full flex-1 flex-col space-y-4 overflow-x-auto rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                {/* <Link
                                    href={route('protected.school-academic-years.subject.edit', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        subject: subject.id,
                                    })}
                                >
                                    <Button variant="outline" size="sm">
                                        <Pencil className="mr-2 h-4 w-4" />
                                        Edit Mata Pelajaran
                                    </Button>
                                </Link> */}

                                <Link
                                    href={route('protected.school-academic-years.classrooms.subjects.summatives.index', {
                                        schoolAcademicYear: schoolAcademicYear.id,
                                        classroom: classroom.id,
                                        classroomSubject: classroomSubject.id,
                                    })}
                                >
                                    <Button variant="outline" size="sm">
                                        <BookMarked className="mr-2 h-4 w-4" />
                                        Sumatif (Segera)
                                    </Button>
                                </Link>
                            </div>
                            {/* <Link
                                href={route('protected.school-academic-years.classrooms.subjects.edit', {
                                    schoolAcademicYear,
                                    classroom,
                                    classroomSubject: classroomSubject.id,
                                })}
                            >
                                <Button variant="outline" size="sm">
                                    <Pencil className="mr-2 h-4 w-4" />
                                    Ganti Mata Pelajaran
                                </Button>
                            </Link> */}
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-x-6 gap-y-8 md:grid-cols-2">
                            <SectionTitle title={`Detail Mata Pelajaran di Kelas ${classroom.name}`} />
                            <DetailItem label="Nama Mata Pelajaran" value={subject.name} />
                            <DetailItem label="Deskripsi" value={subject.description} />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
