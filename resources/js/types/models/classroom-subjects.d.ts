import { Paginator } from '..';
import { Classroom } from './classrooms';
import { Subject } from './subjects';

export type ClassroomSubject = {
    id: string;
    subject_id: string;
    classroom_id: string;
    created_at: string;
    updated_at: string;

    // Relasi yang di-load
    subject?: Subject;
    classroom?: Classroom;
};

/**
 * Tipe data untuk hasil paginasi dari Laravel.
 */
export type ClassroomSubjectsPaginated = Paginator<ClassroomSubject>;
