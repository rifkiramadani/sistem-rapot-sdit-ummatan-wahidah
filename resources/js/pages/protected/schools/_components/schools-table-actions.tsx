'use client';
import { School } from '@/types/models/schools';
import { router } from '@inertiajs/react';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Eye, MoreHorizontal, Settings2, Trash2 } from 'lucide-react';

// Terima 'school' sebagai prop
interface SchoolsTableActionsProps {
    school: School;
}

// Ini adalah React Component yang valid (nama diawali huruf kapital)
export function SchoolsTableActions({ school }: SchoolsTableActionsProps) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="h-8 w-8 p-0">
                    <span className="sr-only">Buka menu</span>
                    <MoreHorizontal />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuLabel>Aksi</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem onClick={() => router.get(route('protected.schools.show', { school: school.id }))}>
                    <Eye className="mr-2 h-4 w-4" /> Lihat
                </DropdownMenuItem>
                <DropdownMenuItem onClick={() => router.get(route('protected.schools.edit', { school: school.id }))}>
                    <Settings2 className="mr-2 h-4 w-4" /> Edit
                </DropdownMenuItem>
                <AlertDialog>
                    <AlertDialogTrigger asChild>
                        <DropdownMenuItem className="text-red-600" onSelect={(e) => e.preventDefault()}>
                            <Trash2 className="mr-2 h-4 w-4" /> Hapus
                        </DropdownMenuItem>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Apakah Anda benar-benar yakin?</AlertDialogTitle>
                            <AlertDialogDescription>
                                Tindakan ini tidak dapat dibatalkan. Ini akan menghapus data secara permanen.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Batal</AlertDialogCancel>
                            <AlertDialogAction
                                className="bg-destructive text-white hover:bg-destructive/80 hover:text-white"
                                onClick={() => {
                                    router.delete(route('protected.schools.destroy', { school: school.id }));
                                }}
                            >
                                Lanjutkan
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
