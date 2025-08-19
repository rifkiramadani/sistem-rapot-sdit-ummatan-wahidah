import { SchoolAcademicYear } from './school-academic-years';

export type Subject = {
    id: string; // Kunci utama (ULID)
    name: string;
    description: string | null;
    school_academic_year_id: string;

    // Properti relasi (opsional, hanya ada jika di-load dari backend)
    school_academic_year?: SchoolAcademicYear;
    // summatives?: Summative[];
    // classroom_subjects?: ClassroomSubject[];
};

/**
 * Tipe data untuk hasil paginasi dari Laravel yang berisi array Subject.
 */
export type SubjectsPaginated = Paginated<Subject>;
