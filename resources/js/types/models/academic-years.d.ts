import { School } from "./schools";

export type AcademicYear = {
    id: string;
    name: string;
    start: string;
    end: string;

    schools?: School[];
};
