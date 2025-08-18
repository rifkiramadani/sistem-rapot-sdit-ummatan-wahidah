import { Breadcrumbs } from '@/components/breadcrumbs';
import { NotificationBell, type Notification } from '@/components/notification-bell';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType } from '@/types';
import * as React from 'react';

export function AppSidebarHeader({ breadcrumbs = [] }: { breadcrumbs?: BreadcrumbItemType[] }) {
    // Dummy data in local state (so delete/read updates the UI immediately)
    const [notifications, setNotifications] = React.useState<Notification[]>([
        {
            id: 1,
            title: 'Report Finished',
            body: 'Your sales report is ready to download.',
            href: '/reports/1',
            read: false,
            createdAt: new Date(),
        },
        {
            id: 2,
            title: 'Job Failed',
            body: 'Image processing failed due to timeout.',
            href: '/jobs/2',
            read: false,
            createdAt: new Date(Date.now() - 1000 * 60 * 30), // 30 min ago
        },
        {
            id: 3,
            title: 'Backup Completed',
            body: 'Nightly backup completed successfully.',
            href: '/backups',
            read: true,
            createdAt: new Date(Date.now() - 1000 * 60 * 60 * 5), // 5 hours ago
        },
    ]);

    const markAllRead = () => setNotifications((prev) => prev.map((n) => ({ ...n, read: true })));

    const handleItemClick = (n: Notification) => setNotifications((prev) => prev.map((x) => (x.id === n.id ? { ...x, read: true } : x)));

    const handleDelete = (n: Notification) => setNotifications((prev) => prev.filter((x) => x.id !== n.id));

    return (
        <header className="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>

            <div className="ml-auto flex items-center gap-2">
                <NotificationBell items={notifications} onMarkAllRead={markAllRead} onItemClick={handleItemClick} onDelete={handleDelete} />
            </div>
        </header>
    );
}
