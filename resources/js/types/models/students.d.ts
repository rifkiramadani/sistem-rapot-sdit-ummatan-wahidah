import { Paginator } from '..';
import { SchoolAcademicYear } from './school-academic-years';
import { StudentGuardian } from './student-guardian';
import { StudentParent } from './student-parent';

/**
 * Tipe data untuk Gender, sesuai dengan GenderEnum di backend.
 */
export type Gender = 'male' | 'female';

/**
 * Tipe data untuk Agama, sesuai dengan ReligionEnum di backend.
 * Anda mungkin perlu menyesuaikan nilai-nilai ini agar cocok persis.
 */
export type Religion = 'muslim' | 'christian' | 'catholic' | 'hindu' | 'buddhist' | 'other'; // TODO: change other to konghuchu

export type Student = {
    id: string; // Kunci utama (ULID)
    nisn: string; // Nomor Induk Siswa Nasional
    name: string;
    gender: Gender;
    birth_place: string;
    birth_date: string; // Format: 'YYYY-MM-DD'
    religion: Religion;
    last_education: string | null;
    address: string;
    school_academic_year_id: string;
    created_at: string;
    updated_at: string;

    // Properti relasi (opsional, hanya ada jika di-load dari backend)
    school_academic_year?: SchoolAcademicYear;
    parent?: StudentParent;
    guardian?: StudentGuardian;
    // Anda bisa menambahkan tipe untuk relasi lain di sini jika diperlukan
    // classroom_student?: ClassroomStudent[];
    // student_summatives?: StudentSummative[];
};

/**
 * Tipe data untuk hasil paginasi dari Laravel yang berisi array Student.
 */
export type StudentsPaginated = Paginator<Student>;
