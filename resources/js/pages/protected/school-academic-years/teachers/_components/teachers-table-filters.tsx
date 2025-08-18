// resources/js/Pages/protected/school-academic-years/teachers/_components/teachers-table-filters.tsx

'use client';

import { Input } from '@/components/ui/input';
import { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

// Tipe untuk objek filter
interface FilterState {
    q: string;
    [key: string]: string;
}

export function TeachersTableFilters() {
    const { props } = usePage<SharedData>();
    const { queryParams } = props;

    // State untuk menyimpan nilai filter dari input
    const [filters, setFilters] = useState<FilterState>(() => ({
        q: queryParams?.filter?.q || '',
    }));

    // Fungsi untuk mengubah state saat input berubah
    const handleFilterChange = (key: keyof FilterState, value: string) => {
        setFilters((prev) => ({
            ...prev,
            [key]: value,
        }));
    };

    // useEffect untuk mengirim request pencarian setelah pengguna berhenti mengetik
    useEffect(() => {
        // Mencegah request saat render pertama jika filter sudah ada di URL
        if (filters.q === (queryParams?.filter?.q || '')) {
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                window.location.pathname,
                { ...queryParams, filter: { ...queryParams?.filter, q: filters.q }, page: 1 }, // Reset ke halaman 1 saat filter baru
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }, 500); // Jeda 500ms

        return () => clearTimeout(timeout);
    }, [filters.q]); // Hanya dijalankan saat `filters.q` berubah

    return (
        <Input
            placeholder="Cari nama, NIY, atau email..."
            value={filters.q}
            onChange={(event) => handleFilterChange('q', event.target.value)}
            className="h-9 max-w-sm"
        />
    );
}
