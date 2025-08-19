import { Paginator } from '..';
import { Teacher } from './teachers';

/**
 * Tipe data utama untuk model Classroom.
 */
export type Classroom = {
    id: string; // Kunci utama (ULID)
    name: string;
    teacher_id: string;
    school_academic_year_id: string;
    created_at: string;
    updated_at: string;

    // Properti relasi (opsional, hanya ada jika di-load dari backend)
    teacher?: Teacher;
};

/**
 * Tipe data untuk hasil paginasi dari Laravel yang berisi array Classroom.
 */
export type ClassroomsPaginated = Paginator<Classroom>;
