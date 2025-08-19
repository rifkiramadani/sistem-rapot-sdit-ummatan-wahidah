// resources/js/Pages/protected/school-academic-years/classrooms/students/_components/ClassroomStudentForm.tsx

'use client';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { ClassroomStudent } from '@/types/models/classroom-students';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Student } from '@/types/models/students';
import { useForm } from '@inertiajs/react';
import { Check, ChevronsUpDown } from 'lucide-react';
import { type FormEvent, useState } from 'react';

// [UBAH] Tambahkan prop opsional classroomStudent
interface ClassroomStudentFormProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    availableStudents: Student[];
    classroomStudent?: ClassroomStudent;
}

export default function ClassroomStudentForm({ schoolAcademicYear, classroom, availableStudents, classroomStudent }: ClassroomStudentFormProps) {
    // [UBAH] Tambahkan flag isEditMode
    const isEditMode = !!classroomStudent;
    const [open, setOpen] = useState(false);

    const { data, setData, post, put, processing, errors } = useForm({
        // [UBAH] Inisialisasi data berdasarkan mode edit atau create
        student_id: classroomStudent?.student_id ?? '',
    });

    // [UBAH] Perbarui handler untuk menangani kedua mode
    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditMode) {
            put(
                route('protected.school-academic-years.classrooms.students.update', {
                    schoolAcademicYear: schoolAcademicYear.id,
                    classroom: classroom.id,
                    classroomStudent: classroomStudent.id,
                }),
            );
        } else {
            post(
                route('protected.school-academic-years.classrooms.students.store', {
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
                <CardTitle>{isEditMode ? `Ganti Siswa di Kelas ${classroom.name}` : `Tambah Siswa ke Kelas ${classroom.name}`}</CardTitle>
                <CardDescription>
                    {isEditMode
                        ? 'Pilih siswa baru untuk menggantikan siswa saat ini di dalam kelas.'
                        : 'Pilih siswa yang ingin ditambahkan ke dalam kelas ini.'}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="space-y-2">
                        <Label>
                            Siswa <span className="text-red-500">*</span>
                        </Label>
                        <Popover open={open} onOpenChange={setOpen}>
                            <PopoverTrigger asChild>
                                <Button variant="outline" role="combobox" aria-expanded={open} className="w-full justify-between">
                                    {data.student_id ? availableStudents.find((s) => s.id === data.student_id)?.name : 'Pilih siswa...'}
                                    <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-[--radix-popover-trigger-width] p-0">
                                <Command>
                                    <CommandInput placeholder="Cari nama atau NISN siswa..." />
                                    <CommandList>
                                        <CommandEmpty>Siswa tidak ditemukan.</CommandEmpty>
                                        <CommandGroup>
                                            {availableStudents.map((student) => (
                                                <CommandItem
                                                    key={student.id}
                                                    value={`${student.name} ${student.nisn}`}
                                                    onSelect={() => {
                                                        setData('student_id', student.id);
                                                        setOpen(false);
                                                    }}
                                                >
                                                    <Check
                                                        className={cn('mr-2 h-4 w-4', data.student_id === student.id ? 'opacity-100' : 'opacity-0')}
                                                    />
                                                    {student.name} ({student.nisn})
                                                </CommandItem>
                                            ))}
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                        <InputError message={errors.student_id} />
                    </div>
                    <div className="flex justify-end">
                        {/* [UBAH] Teks tombol dinamis */}
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : isEditMode ? 'Simpan Perubahan' : 'Tambahkan Siswa'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
