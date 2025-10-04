'use client';

import { Input } from '@/components/ui/input';
import { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export function SummativesTableFilters() {
    const { props } = usePage<SharedData>();
    const { queryParams } = props;
    const [query, setQuery] = useState(() => queryParams?.filter?.q || '');

    useEffect(() => {
        if (query === (queryParams?.filter?.q || '')) return;
        const timeout = setTimeout(() => {
            router.get(
                window.location.pathname,
                { ...queryParams, filter: { ...queryParams?.filter, q: query }, page: 1 },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 500);
        return () => clearTimeout(timeout);
    }, [query]);

    return (
        <Input
            placeholder="Cari nama, identifier, atau jenis sumatif..."
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            className="h-9 max-w-sm"
        />
    );
}
