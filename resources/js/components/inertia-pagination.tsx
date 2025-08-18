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

enum PerPageEnum {
    P10 = '10',
    P20 = '20',
    P30 = '30',
    P40 = '40',
    P50 = '50',
    P100 = '100',
    Pall = 'all',
}

interface InertiaPaginationProps {
    paginateItems: PaginatorInfo;
}

const InertiaPagination = ({ paginateItems }: InertiaPaginationProps) => {
    const { props } = usePage<SharedData>();
    const { queryParams } = props;
    const { links, total, from, to, per_page } = paginateItems;

    const previousLink = links[0];
    const nextLink = links[links.length - 1];
    const pageLinks = links.slice(1, -1);

    const perPageOptions = Object.values(PerPageEnum);

    // [FIX 1] STATE DERIVED DIRECTLY FROM URL QUERY PARAMS
    // Nilai dropdown diambil dari URL sebagai prioritas utama.
    // Jika tidak ada di URL, baru gunakan nilai dari data server (`per_page`).
    const selectValue = queryParams?.per_page || `${per_page}`;

    // Logika ini harus tetap menggunakan data dari server (`paginateItems`)
    // karena hanya server yang tahu jumlah total item.
    const isShowingAll = total > 0 && per_page >= total;

    // Handler ini sudah benar, tugasnya hanya mengubah URL.
    const handlePerPageChange = (newPerPageValue: string) => {
        router.get(
            window.location.pathname,
            { ...queryParams, per_page: newPerPageValue, page: 1 },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <div className="flex flex-col items-center justify-between gap-4 px-2 sm:flex-row">
            <div className="text-sm text-muted-foreground">
                Showing <strong>{from}</strong> to <strong>{to}</strong> of <strong>{total}</strong> results
            </div>

            <div className="flex flex-col items-center gap-4 sm:flex-row sm:gap-6 lg:gap-8">
                <div className="flex items-center space-x-2">
                    <span className="text-sm font-medium whitespace-nowrap">Rows per page</span>
                    <Select value={selectValue} onValueChange={handlePerPageChange}>
                        <SelectTrigger className="h-8 w-[70px]">
                            <SelectValue placeholder={selectValue === 'all' ? 'All' : selectValue} />
                        </SelectTrigger>
                        <SelectContent side="top">
                            {perPageOptions.map((pageSize) => (
                                <SelectItem key={pageSize} value={pageSize}>
                                    {pageSize === PerPageEnum.Pall ? 'Semua' : pageSize}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                {/* [FIX 2] HIDE NAVIGATION WHEN ALL ITEMS ARE SHOWN */}
                {/* Navigasi hanya muncul jika TIDAK semua item ditampilkan. */}
                {!isShowingAll && total > 0 && (
                    <Pagination>
                        <PaginationContent>
                            <PaginationItem>
                                <PaginationPrevious
                                    href={previousLink.url ?? '#'}
                                    className={!previousLink.url ? 'pointer-events-none opacity-50' : ''}
                                />
                            </PaginationItem>

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

                            <PaginationItem>
                                <PaginationNext href={nextLink.url ?? '#'} className={!nextLink.url ? 'pointer-events-none opacity-50' : ''} />
                            </PaginationItem>
                        </PaginationContent>
                    </Pagination>
                )}
            </div>
        </div>
    );
};

export default InertiaPagination;
