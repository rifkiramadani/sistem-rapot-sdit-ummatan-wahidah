// resources/js/Pages/protected/school-academic-years/classrooms/subjects/_components/ClassroomSubjectForm.tsx

'use client';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { ClassroomSubject } from '@/types/models/classroom-subjects';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Subject } from '@/types/models/subjects';
import { useForm } from '@inertiajs/react';
import { Check, ChevronsUpDown } from 'lucide-react';
import { type FormEvent, useState } from 'react';

// [UBAH] Tambahkan prop opsional classroomSubject
interface ClassroomSubjectFormProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    availableSubjects: Subject[];
    classroomSubject?: ClassroomSubject;
}

export default function ClassroomSubjectForm({ schoolAcademicYear, classroom, availableSubjects, classroomSubject }: ClassroomSubjectFormProps) {
    // [UBAH] Tambahkan flag isEditMode
    const isEditMode = !!classroomSubject;
    const [open, setOpen] = useState(false);

    const { data, setData, post, put, processing, errors } = useForm({
        // [UBAH] Inisialisasi data berdasarkan mode edit atau create
        subject_id: classroomSubject?.subject_id ?? '',
    });

    // [UBAH] Perbarui handler untuk menangani kedua mode
    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditMode) {
            put(
                route('protected.school-academic-years.classrooms.subjects.update', {
                    schoolAcademicYear: schoolAcademicYear.id,
                    classroom: classroom.id,
                    classroomSubject: classroomSubject.id,
                }),
            );
        } else {
            post(
                route('protected.school-academic-years.classrooms.subjects.store', {
                    schoolAcademicYear: schoolAcademicYear.id,
                    classroom: classroom.id,
                }),
            );
        }
    };

    return (
        <Card>
            <CardHeader>
                {/* [UBAH] Judul dan deskripsi dinamis */}
                <CardTitle>{isEditMode ? 'Edit Mata Pelajaran' : `Tambah Mata Pelajaran ke Kelas ${classroom.name}`}</CardTitle>
                <CardDescription>
                    {isEditMode
                        ? 'Pilih mata pelajaran baru untuk menggantikan yang saat ini.'
                        : 'Pilih mata pelajaran yang ingin ditambahkan ke dalam kelas ini.'}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="space-y-2">
                        <Label>
                            Mata Pelajaran <span className="text-red-500">*</span>
                        </Label>
                        <Popover open={open} onOpenChange={setOpen}>
                            <PopoverTrigger asChild>
                                <Button variant="outline" role="combobox" aria-expanded={open} className="w-full justify-between">
                                    {data.subject_id ? availableSubjects.find((s) => s.id === data.subject_id)?.name : 'Pilih mata pelajaran...'}
                                    <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-[--radix-popover-trigger-width] p-0">
                                <Command>
                                    <CommandInput placeholder="Cari nama mata pelajaran..." />
                                    <CommandList>
                                        <CommandEmpty>Mata pelajaran tidak ditemukan.</CommandEmpty>
                                        <CommandGroup>
                                            {availableSubjects.map((subject) => (
                                                <CommandItem
                                                    key={subject.id}
                                                    value={subject.name}
                                                    onSelect={() => {
                                                        setData('subject_id', subject.id);
                                                        setOpen(false);
                                                    }}
                                                >
                                                    <Check
                                                        className={cn('mr-2 h-4 w-4', data.subject_id === subject.id ? 'opacity-100' : 'opacity-0')}
                                                    />
                                                    {subject.name}
                                                </CommandItem>
                                            ))}
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                        <InputError message={errors.subject_id} />
                    </div>
                    <div className="flex justify-end">
                        {/* [UBAH] Teks tombol dinamis */}
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : isEditMode ? 'Simpan Perubahan' : 'Tambahkan Mata Pelajaran'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
