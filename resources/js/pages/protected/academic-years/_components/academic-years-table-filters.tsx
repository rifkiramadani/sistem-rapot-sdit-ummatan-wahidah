// Di file: app/protected/academicYear/_components/academic-year-table-filters.tsx

'use client';

import { Input } from '@/components/ui/input';
import { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

// Tipe state filter
interface FilterState {
    q: string;
    [key: string]: string;
}

export function AcademicYearTableFilters() {
    const { props } = usePage<SharedData>();
    const { queryParams } = props;

    // State awal diambil dari queryParams agar sinkron dengan backend
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
        // Hanya jalankan request jika ada perubahan pada filters.q
        if (filters.q === (queryParams?.filter?.q || '')) return;

        const timeout = setTimeout(() => {
            router.get(
                window.location.pathname,
                { ...queryParams, filter: { ...queryParams?.filter, q: filters.q } },
                { preserveState: true, replace: true, preserveScroll: true }
            );
        }, 500); // debounce 500ms

        return () => clearTimeout(timeout);
    }, [filters.q, queryParams]);

    return (
        <Input
            placeholder="Filter by name, start year, or end year..."
            value={filters.q}
            onChange={(event) => handleFilterChange('q', event.target.value)}
            className="h-9 max-w-sm"
        />
    );
}
