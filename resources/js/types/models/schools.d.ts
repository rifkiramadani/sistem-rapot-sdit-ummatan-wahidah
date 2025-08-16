import { AcademicYear } from './academic-years';
import { Principal } from './principals';

export type School = {
    id: string;
    name: string;
    address: string;
    npsn: string | null;
    postal_code: string | null;
    website: string | null;
    email: string | null;
    place_date_raport: string | null;
    place_date_sts: string | null;
    school_principal_id: string | null;
    current_academic_year_id: string | null;
    created_at: string;
    updated_at: string;

    // Relasi (jika di-load dari backend)
    principal?: Principal | null;
    current_academic_year?: AcademicYear | null;

    academic_years?: AcademicYear[];
};

export type SchoolsPaginated = Paginator<School>;
