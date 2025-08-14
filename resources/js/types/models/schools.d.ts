export type School = {
    id: string;
    name: string;
    npsn: string;
    principal: Principal | null;
    current_academic_year: AcademicYear | null;
};

export type SchoolsPaginated = Paginator<School>;
