// Di file: resources/js/pages/protected/schools/academic-years/_components/academic-years-table-filters.tsx

'use client';

import { Input } from '@/components/ui/input';
import { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

// Tipe untuk objek filter
interface FilterState {
    q: string;
}

export function AcademicYearsTableFilters() {
    const { props } = usePage<SharedData>();
    const { queryParams } = props;

    const [filters, setFilters] = useState<FilterState>(() => ({
        q: queryParams?.filter?.q || '',
    }));

    const handleFilterChange = (key: keyof FilterState, value: string) => {
        setFilters((prev) => ({
            ...prev,
            [key]: value,
        }));
    };

    useEffect(() => {
        if (filters.q === (queryParams?.filter?.q || '')) {
            return;
        }
        const timeout = setTimeout(() => {
            router.get(
                window.location.pathname,
                { ...queryParams, filter: { ...queryParams?.filter, q: filters.q } },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 500); // Debounce untuk mengurangi request saat mengetik

        return () => clearTimeout(timeout);
    }, [filters.q, queryParams]);

    return (
        <>
            <Input
                placeholder="Filter berdasarkan nama tahun ajaran..."
                value={filters.q}
                onChange={(event) => handleFilterChange('q', event.target.value)}
                className="h-9 max-w-sm"
            />
        </>
    );
}
