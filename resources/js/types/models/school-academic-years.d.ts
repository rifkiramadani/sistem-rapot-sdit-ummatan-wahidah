import { AcademicYear } from './academic-years';
import { School } from './schools';
import { Paginator } from '..';

export type SchoolAcademicYear = {
    id: string;
    school_id: string;
    academic_year_id: string;
    academic_year: AcademicYear;
    school?: School;
};

export type SchoolAcademicYearsPaginated = Paginator<SchoolAcademicYear>;
