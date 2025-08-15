import { NavItem } from "@/types";
import { LayoutGrid, School, CalendarRange } from "lucide-react";

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
    {
        title: 'Academic Years',
        href: route('protected.academic-years.index'),
        icon: CalendarRange,
    },
];
