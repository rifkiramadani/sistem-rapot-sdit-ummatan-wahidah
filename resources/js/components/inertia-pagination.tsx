import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';

// Tentukan struktur objek Paginator lengkap dari Laravel
interface PaginatorInfo {
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    total: number;
    from: number;
    to: number;
    per_page: number;
}

interface InertiaPaginationProps {
    paginateItems: PaginatorInfo;
}

const InertiaPagination = ({ paginateItems }: InertiaPaginationProps) => {
    const { props } = usePage<SharedData>();
    const { queryParams } = props;
    const { links, total, from, to, per_page } = paginateItems;

    // Di Laravel, link pertama selalu "previous" dan yang terakhir adalah "next"
    const previousLink = links[0];
    const nextLink = links[links.length - 1];
    // Ambil hanya link nomor halaman (dan elipsis)
    const pageLinks = links.slice(1, -1);

    // Fungsi untuk menangani perubahan jumlah item per halaman
    const handlePerPageChange = (newPerPageValue: string) => {
        router.get(
            window.location.pathname,
            { ...queryParams, per_page: newPerPageValue, page: 1 }, // Reset ke halaman 1 saat item per halaman diubah
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <div className="flex flex-col items-center justify-between gap-4 px-2 sm:flex-row">
            {/* Tampilkan teks "Showing X to Y of Z results" */}
            <div className="text-sm text-muted-foreground">
                Showing <strong>{from}</strong> to <strong>{to}</strong> of <strong>{total}</strong> results
            </div>

            {/* Kontrol di sisi kanan */}
            <div className="flex flex-col items-center gap-4 sm:flex-row sm:gap-6 lg:gap-8">
                {/* Dropdown untuk memilih ukuran halaman */}
                <div className="flex items-center space-x-2">
                    <span className="text-sm font-medium whitespace-nowrap">Rows per page</span>
                    <Select value={`${per_page}`} onValueChange={handlePerPageChange}>
                        <SelectTrigger className="h-8 w-[70px]">
                            <SelectValue placeholder={per_page} />
                        </SelectTrigger>
                        <SelectContent side="top">
                            {[10, 20, 30, 40, 50].map((pageSize) => (
                                <SelectItem key={pageSize} value={`${pageSize}`}>
                                    {pageSize}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                {/* Tautan halaman */}
                <Pagination>
                    <PaginationContent>
                        {/* Tombol Previous */}
                        <PaginationItem>
                            <PaginationPrevious
                                href={previousLink.url ?? '#'}
                                // Tambahkan style disabled jika tidak ada URL (halaman pertama)
                                className={!previousLink.url ? 'pointer-events-none opacity-50' : ''}
                            />
                        </PaginationItem>

                        {/* Tautan Nomor Halaman dan Elipsis */}
                        {pageLinks.map((link, index) =>
                            link.label.includes('...') ? (
                                <PaginationItem key={`ellipsis-${index}`}>
                                    <PaginationEllipsis />
                                </PaginationItem>
                            ) : (
                                <PaginationItem key={link.label}>
                                    <PaginationLink href={link.url ?? '#'} isActive={link.active}>
                                        {link.label}
                                    </PaginationLink>
                                </PaginationItem>
                            ),
                        )}

                        {/* Tombol Next */}
                        <PaginationItem>
                            <PaginationNext
                                href={nextLink.url ?? '#'}
                                // Tambahkan style disabled jika tidak ada URL (halaman terakhir)
                                className={!nextLink.url ? 'pointer-events-none opacity-50' : ''}
                            />
                        </PaginationItem>
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
    );
};

export default InertiaPagination;
