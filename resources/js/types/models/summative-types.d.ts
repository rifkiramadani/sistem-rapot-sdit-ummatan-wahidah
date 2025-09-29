import { SchoolAcademicYear } from './school-academic-years';
import { Summative } from './summatives';

export type SummativeType = {
    id: string; // Kunci utama (ULID)
    name: string;
    school_academic_year_id: string;
    created_at: string;
    updated_at: string;

    // Properti relasi (opsional)
    school_academic_year?: SchoolAcademicYear;
    summatives?: Summative[];
};

export type SummativeTypesPaginated = Paginated<SummativeType>;
