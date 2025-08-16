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
import { ReactNode } from 'react';

interface BulkDeleteAlertDialogProps {
    // `children` akan menjadi tombol pemicu (trigger) dialog
    children: ReactNode;
    // Jumlah item yang akan dihapus
    itemCount: number;
    // Nama item (e.g., "sekolah", "siswa") untuk pesan yang dinamis
    itemName?: string;
    // Fungsi yang akan dijalankan saat tombol "Ya, Hapus" diklik
    onConfirm: () => void;
}

export function BulkDeleteAlertDialog({ children, itemCount, itemName = 'data', onConfirm }: BulkDeleteAlertDialogProps) {
    // Jangan render apa-apa jika tidak ada item yang dipilih
    if (itemCount === 0) {
        return null;
    }

    return (
        <AlertDialog>
            <AlertDialogTrigger asChild>{children}</AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Apakah Anda benar-benar yakin?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Tindakan ini akan menghapus <strong>{itemCount}</strong> <strong>{itemName}</strong> secara permanen. Tindakan ini tidak dapat
                        dibatalkan.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Batal</AlertDialogCancel>
                    <AlertDialogAction className="bg-destructive text-white hover:bg-destructive/80 hover:text-white" onClick={onConfirm}>
                        Ya, Hapus
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
