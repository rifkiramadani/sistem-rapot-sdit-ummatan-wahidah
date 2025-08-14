// Di file: app/protected/schools/_components/schools-table-filters.tsx

'use client';

import { Input } from '@/components/ui/input';
import { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

// Tipe untuk objek filter
interface FilterState {
    q: string;
    status: string;
    [key: string]: string;
}

export function SchoolsTableFilters() {
    const { props } = usePage<SharedData>();
    const { queryParams } = props;

    const [filters, setFilters] = useState<FilterState>(() => ({
        q: queryParams?.filter?.q || '',
        status: queryParams?.filter?.status || '',
    }));

    const handleFilterChange = (key: keyof FilterState, value: string) => {
        setFilters((prev) => ({
            ...prev,
            [key]: value,
        }));
    };

    useEffect(() => {
        // Cek apakah ini render awal atau state sudah sinkron
        if (filters.q === (queryParams?.filter?.q || '')) {
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                window.location.pathname,
                { ...queryParams, filter: { ...queryParams?.filter, q: filters.q } },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 500);

        return () => clearTimeout(timeout);
    }, [filters.q]); // Hanya bergantung pada input pengguna (filters.q)

    return (
        <>
            <Input
                placeholder="Filter by name or npsn..."
                value={filters.q}
                onChange={(event) => handleFilterChange('q', event.target.value)}
                className="h-9 max-w-sm"
            />
        </>
    );
}
