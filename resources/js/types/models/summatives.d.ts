import { Paginated } from '@/types';
import { StudentSummative } from './student-summatives';
import { Subject } from './subjects';

export type Summative = {
    id: string; // Kunci utama (ULID)
    name: string;
    description: string | null;
    identifier: string | null;
    prominent: string | null;
    improvement: string | null;
    subject_id: string;
    summative_type_id: string;
    created_at: string;
    updated_at: string;

    // Properti relasi (opsional)
    subject?: Subject;
    summative_type?: SummativeType;
    student_summatives?: StudentSummative[];
};

export type SummativesPaginated = Paginated<Summative>;
