import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import {
    Users,
    UsersRound,
    GraduationCap,
    Building,
    BookOpen,
    FileCheck,
    TrendingUp,
    School,
    User,
    Calendar,
    MapPin,
    Globe,
    Mail,
    Phone
} from 'lucide-react';
import {
    LineChart,
    Line,
    AreaChart,
    Area,
    BarChart,
    Bar,
    PieChart,
    Pie,
    Cell,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer,
} from 'recharts';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: route('protected.dashboard.index'),
    },
];

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8', '#82CA9D'];

interface DashboardData {
    school_info: {
        name: string;
        npsn: string;
        address: string;
        website?: string;
        email?: string;
        principal?: string;
        current_academic_year?: string;
    };
    overview_stats: {
        total_academic_years: number;
        total_students: number;
        total_teachers: number;
        total_classrooms: number;
        total_subjects: number;
        total_summatives: number;
    };
    academic_years_data: Array<{
        id: string;
        year: string;
        academic_year_name: string;
        students_count: number;
        teachers_count: number;
        classrooms_count: number;
        subjects_count: number;
        summatives_count: number;
    }>;
    chart_data: {
        students_by_academic_year: Array<{ name: string; value: number }>;
        teachers_by_academic_year: Array<{ name: string; value: number }>;
        growth_trends: Array<{
            year: string;
            students_growth: number;
            teachers_growth: number;
            classrooms_growth: number;
        }>;
        year_comparison: Array<{
            year: string;
            students: number;
            teachers: number;
            classrooms: number;
            subjects: number;
            summatives: number;
        }>;
    };
}

interface IndexProps {
    school: any;
    dashboardData: DashboardData | null;
    error?: string;
}

export default function Index({ school, dashboardData, error }: IndexProps) {
    if (error || !dashboardData) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Dashboard" />
                <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-red-600">
                                <School className="h-5 w-5" />
                                Dashboard Error
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

    const { school_info, overview_stats, chart_data } = dashboardData;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${school_info.name} - Dashboard`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {/* Header with School Information */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <School className="h-6 w-6" />
                            {school_info.name}
                        </CardTitle>
                        <CardDescription>
                            NPSN: {school_info.npsn}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                            <div className="flex items-center gap-2">
                                <MapPin className="h-4 w-4 text-muted-foreground" />
                                <span>{school_info.address}</span>
                            </div>
                            {school_info.email && (
                                <div className="flex items-center gap-2">
                                    <Mail className="h-4 w-4 text-muted-foreground" />
                                    <span>{school_info.email}</span>
                                </div>
                            )}
                            {school_info.website && (
                                <div className="flex items-center gap-2">
                                    <Globe className="h-4 w-4 text-muted-foreground" />
                                    <a href={school_info.website} target="_blank" rel="noopener noreferrer"
                                       className="text-blue-600 hover:underline">{school_info.website}</a>
                                </div>
                            )}
                            {school_info.principal && (
                                <div className="flex items-center gap-2">
                                    <User className="h-4 w-4 text-muted-foreground" />
                                    <span>Kepala Sekolah: {school_info.principal}</span>
                                </div>
                            )}
                            {school_info.current_academic_year && (
                                <div className="flex items-center gap-2 lg:col-span-2">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    <span>Semester Aktif: {school_info.current_academic_year}</span>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* Overview Statistics */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tahun Ajaran</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{overview_stats.total_academic_years}</div>
                            <p className="text-xs text-muted-foreground">Total periode</p>
                        </CardContent>
                    </Card>

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

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Penilaian</CardTitle>
                            <FileCheck className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{overview_stats.total_summatives.toLocaleString('id-ID')}</div>
                            <p className="text-xs text-muted-foreground">Total penilaian</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Charts Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {/* Line Chart - Year Comparison */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <TrendingUp className="h-5 w-5" />
                                Tren Perbandingan Tahunan
                            </CardTitle>
                            <CardDescription>
                                Perkembangan jumlah siswa, guru, dan kelas per tahun ajaran
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <LineChart data={chart_data.year_comparison}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="year" />
                                    <YAxis />
                                    <Tooltip />
                                    <Legend />
                                    <Line type="monotone" dataKey="students" stroke="#8884d8" strokeWidth={2} name="Siswa" />
                                    <Line type="monotone" dataKey="teachers" stroke="#82ca9d" strokeWidth={2} name="Guru" />
                                    <Line type="monotone" dataKey="classrooms" stroke="#ffc658" strokeWidth={2} name="Kelas" />
                                    <Line type="monotone" dataKey="subjects" stroke="#ff7300" strokeWidth={2} name="Mata Pelajaran" />
                                </LineChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>

                    {/* Bar Chart - Subjects and Summatives */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <BookOpen className="h-5 w-5" />
                                Mata Pelajaran & Penilaian
                            </CardTitle>
                            <CardDescription>
                                Jumlah mata pelajaran dan penilaian per tahun ajaran
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <BarChart data={chart_data.year_comparison}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="year" />
                                    <YAxis />
                                    <Tooltip />
                                    <Legend />
                                    <Bar dataKey="subjects" fill="#00C49F" name="Mata Pelajaran" />
                                    <Bar dataKey="summatives" fill="#FF8042" name="Penilaian" />
                                </BarChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>

                    {/* Pie Chart - Students Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Users className="h-5 w-5" />
                                Distribusi Siswa per Tahun Ajaran
                            </CardTitle>
                            <CardDescription>
                                Persentase jumlah siswa per tahun ajaran
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <PieChart>
                                    <Pie
                                        data={chart_data.students_by_academic_year}
                                        cx="50%"
                                        cy="50%"
                                        labelLine={false}
                                        label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                                        outerRadius={80}
                                        fill="#8884d8"
                                        dataKey="value"
                                    >
                                        {chart_data.students_by_academic_year.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip />
                                </PieChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>

                    {/* Pie Chart - Teachers Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <UsersRound className="h-5 w-5" />
                                Distribusi Guru per Tahun Ajaran
                            </CardTitle>
                            <CardDescription>
                                Persentase jumlah guru per tahun ajaran
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <PieChart>
                                    <Pie
                                        data={chart_data.teachers_by_academic_year}
                                        cx="50%"
                                        cy="50%"
                                        labelLine={false}
                                        label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                                        outerRadius={80}
                                        fill="#8884d8"
                                        dataKey="value"
                                    >
                                        {chart_data.teachers_by_academic_year.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip />
                                </PieChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                </div>

                {/* Growth Trends Chart */}
                {chart_data.growth_trends.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <TrendingUp className="h-5 w-5" />
                                Tren Pertumbuhan (%)
                            </CardTitle>
                            <CardDescription>
                                Persentase pertumbuhan tahunan dari tahun sebelumnya
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ResponsiveContainer width="100%" height={300}>
                                <AreaChart data={chart_data.growth_trends}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="year" />
                                    <YAxis />
                                    <Tooltip formatter={(value) => [`${Number(value).toFixed(1)}%`, '']} />
                                    <Legend />
                                    <Area type="monotone" dataKey="students_growth" stackId="1" stroke="#8884d8" fill="#8884d8" name="Pertumbuhan Siswa" />
                                    <Area type="monotone" dataKey="teachers_growth" stackId="1" stroke="#82ca9d" fill="#82ca9d" name="Pertumbuhan Guru" />
                                    <Area type="monotone" dataKey="classrooms_growth" stackId="1" stroke="#ffc658" fill="#ffc658" name="Pertumbuhan Kelas" />
                                </AreaChart>
                            </ResponsiveContainer>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
