type AcademicYear = {
    schools: unknown;
    id: string;
    name: string;
};

// resources/js/types/models/academic-years-table.ts

export interface Paginator<T> {
    data: T[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
    per_page: number;
}

export type AcademicYearsPaginated = Paginator<AcademicYear>;
