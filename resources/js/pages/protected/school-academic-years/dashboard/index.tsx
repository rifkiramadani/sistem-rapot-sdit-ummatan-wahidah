import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Head } from '@inertiajs/react';
import {
    Users,
    UsersRound,
    Building,
    BookOpen,
    School,
    User,
    Calendar,
    MapPin,
    Award,
    BarChart3,
    PieChart,
} from 'lucide-react';
import {
    BarChart,
    Bar,
    PieChart as RechartsPieChart,
    Pie,
    Cell,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
} from 'recharts';

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8', '#82CA9D'];

interface DashboardData {
    school_info: {
        name: string;
        npsn: string;
        address: string;
        principal: string;
        academic_year: string;
        year: string;
    };
    overview_stats: {
        total_students: number;
        total_teachers: number;
        total_classrooms: number;
        total_subjects: number;
        total_summatives: number;
        completed_summatives: number;
    };
    student_demographics: {
        gender_distribution: {
            male: number;
            female: number;
        };
        average_age: number;
    };
    performance_stats: {
        average_score: number;
        completion_rate: number;
        total_assessments: number;
        completed_assessments: number;
    };
    chart_data: {
        classroom_distribution: Array<{ name: string; students: number }>;
        gender_distribution: Array<{ name: string; value: number }>;
    };
    classroom_details: Array<{
        name: string;
        students_count: number;
        teacher: string;
    }>;
}

interface IndexProps {
    schoolAcademicYear: SchoolAcademicYear;
    dashboardData: DashboardData | null;
    error?: string;
}

export default function Index({ schoolAcademicYear, dashboardData, error }: IndexProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dasbor',
            href: route('protected.school-academic-years.dashboard.index', {
                schoolAcademicYear: schoolAcademicYear.id,
            }),
        },
    ];

    if (error || !dashboardData) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title={`${schoolAcademicYear.academicYear?.name || 'Dasbor'}`} />
                <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-red-600">
                                <School className="h-5 w-5" />
                                Dasbor Error
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-muted-foreground">{error || 'No dashboard data available'}</p>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    const { school_info, overview_stats, student_demographics, performance_stats, chart_data, classroom_details } = dashboardData;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${school_info.academic_year} - Dasbor`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Header with School and Academic Year Information */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <School className="h-6 w-6" />
                            {school_info.name} - {school_info.academic_year}
                        </CardTitle>
                        <CardDescription>
                            NPSN: {school_info.npsn} | Kepala Sekolah: {school_info.principal}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                            <div className="flex items-center gap-2">
                                <MapPin className="h-4 w-4 text-muted-foreground" />
                                <span>{school_info.address}</span>
                            </div>
                            {school_info.principal && (
                                <div className="flex items-center gap-2">
                                    <User className="h-4 w-4 text-muted-foreground" />
                                    <span>{school_info.principal}</span>
                                </div>
                            )}
                            <div className="flex items-center gap-2">
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                <span>{school_info.academic_year}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Overview Statistics */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Siswa</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{overview_stats.total_students.toLocaleString('id-ID')}</div>
                            <p className="text-xs text-muted-foreground">Total siswa</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Guru</CardTitle>
                            <UsersRound className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{overview_stats.total_teachers.toLocaleString('id-ID')}</div>
                            <p className="text-xs text-muted-foreground">Total guru</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Kelas</CardTitle>
                            <Building className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{overview_stats.total_classrooms.toLocaleString('id-ID')}</div>
                            <p className="text-xs text-muted-foreground">Total kelas</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Mata Pelajaran</CardTitle>
                            <BookOpen className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{overview_stats.total_subjects.toLocaleString('id-ID')}</div>
                            <p className="text-xs text-muted-foreground">Total mata pelajaran</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Charts Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {/* Bar Chart - Classroom Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <BarChart3 className="h-5 w-5" />
                                Distribusi Siswa per Kelas
                            </CardTitle>
                            <CardDescription>
                                Jumlah siswa di setiap kelas
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <BarChart data={chart_data.classroom_distribution}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip />
                                    <Bar dataKey="students" fill="#8884d8" name="Jumlah Siswa" />
                                </BarChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>

                    {/* Pie Chart - Gender Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <PieChart className="h-5 w-5" />
                                Distribusi Jenis Kelamin
                            </CardTitle>
                            <CardDescription>
                                Persentase siswa berdasarkan jenis kelamin
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <RechartsPieChart>
                                    <Pie
                                        data={chart_data.gender_distribution}
                                        cx="50%"
                                        cy="50%"
                                        labelLine={false}
                                        label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                                        outerRadius={80}
                                        fill="#8884d8"
                                        dataKey="value"
                                    >
                                        {chart_data.gender_distribution.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip />
                                </RechartsPieChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                </div>

                {/* Student Demographics and Performance */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {/* Student Demographics */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Users className="h-5 w-5" />
                                Demografi Siswa
                            </CardTitle>
                            <CardDescription>
                                Informasi demografis siswa
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Laki-laki</p>
                                    <p className="text-2xl font-bold text-blue-600">
                                        {student_demographics.gender_distribution.male}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Perempuan</p>
                                    <p className="text-2xl font-bold text-pink-600">
                                        {student_demographics.gender_distribution.female}
                                    </p>
                                </div>
                                <div className="col-span-2">
                                    <p className="text-sm font-medium text-muted-foreground">Rata-rata Usia</p>
                                    <p className="text-xl font-bold">{student_demographics.average_age} tahun</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Performance Statistics */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Award className="h-5 w-5" />
                                Statistik Prestasi
                            </CardTitle>
                            <CardDescription>
                                Statistik nilai dan penyelesaian tugas
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Rata-rata Nilai</p>
                                    <p className="text-2xl font-bold text-green-600">
                                        {performance_stats.average_score}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Tingkat Penyelesaian</p>
                                    <p className="text-2xl font-bold text-blue-600">
                                        {performance_stats.completion_rate}%
                                    </p>
                                </div>
                                <div className="col-span-2">
                                    <p className="text-sm font-medium text-muted-foreground">
                                        Penilaian Selesai ({performance_stats.completed_assessments}/{performance_stats.total_assessments})
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Classroom Details */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Building className="h-5 w-5" />
                            Detail Kelas
                        </CardTitle>
                        <CardDescription>
                            Informasi lengkap setiap kelas
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {classroom_details.map((classroom, index) => (
                                <div key={index} className="border rounded-lg p-4">
                                    <div className="flex justify-between items-start mb-2">
                                        <h3 className="font-semibold">{classroom.name}</h3>
                                        <Badge variant="secondary">
                                            {classroom.students_count} siswa
                                        </Badge>
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        <span className="font-medium">Wali Kelas:</span> {classroom.teacher}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
