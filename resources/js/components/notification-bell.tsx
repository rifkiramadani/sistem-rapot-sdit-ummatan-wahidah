import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ScrollArea } from '@/components/ui/scroll-area';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { Bell, CheckCheck, Loader2, Trash } from 'lucide-react';

export type Notification = {
    id: string | number;
    title: string;
    body?: string | null;
    href?: string | null;
    read?: boolean;
    createdAt?: string | Date;
};

type Props = {
    items: Notification[];
    onMarkAllRead?: () => Promise<void> | void;
    onItemClick?: (n: Notification) => Promise<void> | void;
    onDelete?: (n: Notification) => Promise<void> | void;
    className?: string;
    loading?: boolean;
};

export function NotificationBell({ items, onMarkAllRead, onItemClick, onDelete, className, loading = false }: Props) {
    const unreadCount = items.filter((n) => !n.read).length;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className={cn('relative', className)} aria-label={`Open notifications (${unreadCount} unread)`}>
                    {loading ? <Loader2 className="h-5 w-5 animate-spin" /> : <Bell className="h-5 w-5" />}
                    {unreadCount > 0 && (
                        <span
                            className="absolute -top-0.5 -right-0.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-destructive px-1 text-[10px] leading-none font-medium text-destructive-foreground"
                            aria-hidden="true"
                        >
                            {unreadCount > 99 ? '99+' : unreadCount}
                        </span>
                    )}
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end" className="w-80 p-0">
                <div className="flex items-center justify-between px-3 py-2">
                    <DropdownMenuLabel className="p-0">Notifications</DropdownMenuLabel>
                    <Button variant="ghost" size="sm" className="gap-1" onClick={() => onMarkAllRead?.()} disabled={unreadCount === 0 || loading}>
                        <CheckCheck className="h-4 w-4" />
                        <span className="text-xs">Mark all read</span>
                    </Button>
                </div>
                <DropdownMenuSeparator />

                {items.length === 0 ? (
                    <div className="px-3 py-8 text-center text-sm text-muted-foreground">You’re all caught up 🎉</div>
                ) : (
                    <ScrollArea className="max-h-80">
                        <ul className="p-1">
                            {items.map((n) => {
                                const content = (
                                    <div className="flex w-full items-start gap-2">
                                        <div className="mt-1">
                                            {!n.read ? (
                                                <span className="block h-2 w-2 rounded-full bg-primary" />
                                            ) : (
                                                <span className="block h-2 w-2 rounded-full border border-muted-foreground/30 bg-transparent" />
                                            )}
                                        </div>

                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center gap-2">
                                                <p className={cn('truncate text-sm font-medium', !n.read && 'font-semibold')}>{n.title}</p>
                                                {!n.read && <Badge variant="secondary">New</Badge>}
                                            </div>

                                            {n.body && <p className="mt-0.5 line-clamp-2 text-xs text-muted-foreground">{n.body}</p>}

                                            {n.createdAt && (
                                                <p className="mt-1 text-[10px] tracking-wide text-muted-foreground uppercase">
                                                    {formatTime(n.createdAt)}
                                                </p>
                                            )}
                                        </div>

                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="h-6 w-6 text-muted-foreground hover:text-destructive"
                                            onClick={(e) => {
                                                e.preventDefault();
                                                e.stopPropagation();
                                                onDelete?.(n);
                                            }}
                                        >
                                            <Trash className="h-4 w-4" />
                                        </Button>
                                    </div>
                                );

                                return (
                                    <li key={n.id}>
                                        {n.href ? (
                                            <DropdownMenuItem asChild onClick={() => onItemClick?.(n)}>
                                                <Link href={n.href} className="w-full focus-visible:outline-none">
                                                    {content}
                                                </Link>
                                            </DropdownMenuItem>
                                        ) : (
                                            <DropdownMenuItem onClick={() => onItemClick?.(n)}>{content}</DropdownMenuItem>
                                        )}
                                    </li>
                                );
                            })}
                        </ul>
                    </ScrollArea>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function formatTime(date: string | Date) {
    const d = typeof date === 'string' ? new Date(date) : date;
    const now = new Date();
    const sameDay = d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth() && d.getDate() === now.getDate();

    return sameDay ? d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : d.toLocaleDateString();
}
