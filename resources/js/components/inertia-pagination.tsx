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
import { router } from '@inertiajs/react';

// Define the shape of the full Paginator object from Laravel
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
    const { links, total, from, to, per_page } = paginateItems;

    // Function to handle changing the number of items per page
    const handlePerPageChange = (newPerPageValue: string) => {
        router.get(
            window.location.pathname, // Stay on the current page
            { per_page: newPerPageValue }, // Add the new per_page parameter
            {
                preserveState: true, // Keep component state (e.g., search filters)
                replace: true, // Update the URL without adding to browser history
            },
        );
    };

    return (
        <div className="mt-4 flex items-center justify-between px-2">
            {/* Display "Showing X to Y of Z results" */}
            <div className="flex-1 text-sm text-muted-foreground">
                Showing <strong>{from}</strong> to <strong>{to}</strong> of <strong>{total}</strong> results
            </div>

            <div className="flex items-center space-x-6 lg:space-x-8">
                {/* Dropdown to select page size */}
                <div className="flex items-center space-x-2">
                    <p className="text-sm font-medium">Rows per page</p>
                    <Select value={`${per_page}`} onValueChange={handlePerPageChange}>
                        <SelectTrigger className="h-8 w-[70px]">
                            <SelectValue placeholder={per_page} />
                        </SelectTrigger>
                        <SelectContent side="top">
                            {[1, 50, 100].map((pageSize) => (
                                <SelectItem key={pageSize} value={`${pageSize}`}>
                                    {pageSize}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                {/* The actual page links */}
                {links.length > 3 && (
                    <Pagination>
                        <PaginationContent>
                            {links.map((link, index) => {
                                if (index === 0) {
                                    return (
                                        <PaginationItem key={index}>
                                            <PaginationPrevious href={link.url ?? '#'} />
                                        </PaginationItem>
                                    );
                                }
                                if (index === links.length - 1) {
                                    return (
                                        <PaginationItem key={index}>
                                            <PaginationNext href={link.url ?? '#'} />
                                        </PaginationItem>
                                    );
                                }
                                if (link.label.includes('...')) {
                                    return (
                                        <PaginationItem key={index}>
                                            <PaginationEllipsis />
                                        </PaginationItem>
                                    );
                                }
                                return (
                                    <PaginationItem key={index}>
                                        <PaginationLink href={link.url ?? '#'} isActive={link.active}>
                                            {link.label}
                                        </PaginationLink>
                                    </PaginationItem>
                                );
                            })}
                        </PaginationContent>
                    </Pagination>
                )}
            </div>
        </div>
    );
};

export default InertiaPagination;
