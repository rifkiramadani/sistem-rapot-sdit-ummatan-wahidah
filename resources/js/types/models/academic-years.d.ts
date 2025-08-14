type AcademicYear = {
    schools: unknown;
    id: string;
    name: string;
};

// resources/js/types/models/academic-years-table.ts
export interface AcademicYearsTable {
    id: number;
    year: string;
    semester: string;
    startDate: string;
    endDate: string;
}

export interface AcademicYearsPaginated {
    per_page: number;
    to: number;
    links: { url: string | null; label: string; active: boolean; }[];
    from: unknown;
    data: AcademicYear[];
    current_page: number;
    last_page: number;
    total: number;
}
