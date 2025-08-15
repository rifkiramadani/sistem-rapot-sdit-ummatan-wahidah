import { NavItem } from '@/types';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { LayoutGrid, School } from 'lucide-react';

export const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('protected.dashboard.index'),
        icon: LayoutGrid,
    },
    {
        title: 'Schools',
        href: route('protected.schools.index'),
        icon: School,
    },
];

export const getSchoolAcademicYearNavItems = (schoolAcademicYear: SchoolAcademicYear): NavItem[] => [
    {
        title: 'Dashboard TA',
        href: route('protected.school-academic-years.dashboard.index', { schoolAcademicYear: schoolAcademicYear.id }),
        icon: LayoutGrid,
    },
    // Contoh item menu lain di dalam konteks tahun ajaran
    // {
    //     title: 'Guru',
    //     href: route('protected.school-academic-years.teachers.index', { schoolAcademicYear: schoolAcademicYear.id }),
    //     icon: Users,
    // },
];
