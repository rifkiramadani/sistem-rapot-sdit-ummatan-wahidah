import { Paginator } from "..";

export type Teacher = {
    id: string; // Primary key (ULID)
    name: string;
    niy: string; // Nomor Induk Yayasan
    user_id: string;
    school_academic_year_id: string;
    created_at: string;
    updated_at: string;

    user?: User;
    school_academic_year?: SchoolAcademicYear;
    classrooms?: Classroom[];
};

export type TeachersPaginated = Paginator<Teacher>;
