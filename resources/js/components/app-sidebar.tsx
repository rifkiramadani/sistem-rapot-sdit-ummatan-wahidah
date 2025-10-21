import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarGroup, SidebarGroupLabel, SidebarGroupContent, SidebarGroupAction } from '@/components/ui/sidebar';
import { getSchoolAcademicYearNavItems, mainNavItems } from '@/constants/protected-sidebar';
import { SharedData, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Folder } from 'lucide-react';
import AppLogo from './app-logo';

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Repository',
    //     href: 'https://github.com/laravel/react-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#react',
    //     icon: BookOpen,
    // },
];

export function AppSidebar() {
    const { props } = usePage<SharedData>();
    const { schoolAcademicYear } = props;
    let navItems: NavItem[];

    if (schoolAcademicYear && route().current()?.startsWith('protected.school-academic-years.')) {
        navItems = getSchoolAcademicYearNavItems(schoolAcademicYear);
    } else {
        navItems = mainNavItems;
    }

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link
                                href={
                                    schoolAcademicYear
                                        ? route('protected.school-academic-years.dashboard.index', {
                                            schoolAcademicYear: schoolAcademicYear.id,
                                        })
                                        : route('protected.dashboard.index')
                                }
                                prefetch
                            >
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {/* Dashboard Section */}
                <SidebarGroup>
                    <SidebarGroupLabel>Dashboard</SidebarGroupLabel>
                    <SidebarGroupContent>
                        <NavMain items={navItems.filter(item => item.href.includes('dashboard'))} />
                    </SidebarGroupContent>
                </SidebarGroup>

                {/* Master Data Group */}
                <SidebarGroup>
                    <SidebarGroupLabel>Data Master</SidebarGroupLabel>
                    <SidebarGroupContent>
                        <NavMain items={navItems.filter(item =>
                            item.href.includes('teachers') ||
                            item.href.includes('students')
                        )} />
                    </SidebarGroupContent>
                </SidebarGroup>

                {/* Academic Structure Group */}
                <SidebarGroup>
                    <SidebarGroupLabel>Struktur Akademik</SidebarGroupLabel>
                    <SidebarGroupContent>
                        <NavMain items={navItems.filter(item =>
                            item.href.includes('classrooms') ||
                            item.href.includes('subjects')
                        )} />
                    </SidebarGroupContent>
                </SidebarGroup>
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
