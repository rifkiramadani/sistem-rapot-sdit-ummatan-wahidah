import { Paginator } from '..';
import { Classroom } from './classrooms';
import { Student } from './students';

export type ClassroomStudent = {
    id: string; // Kunci utama (ULID) dari record pivot
    student_id: string;
    classroom_id: string;
    created_at: string;
    updated_at: string;

    // Relasi yang bisa di-load dari backend (bersifat opsional)
    student?: Student;
    classroom?: Classroom;
};

/**
 * Tipe data untuk hasil paginasi dari Laravel yang berisi
 * array ClassroomStudent.
 */
export type ClassroomStudentsPaginated = Paginator<ClassroomStudent>;
