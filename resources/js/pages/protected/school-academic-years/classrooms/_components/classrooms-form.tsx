import { useForm } from '@inertiajs/react';
import { Check, ChevronsUpDown } from 'lucide-react'; // <-- 2. Import ikon
import { type FormEvent, useState } from 'react'; // <-- 1. Import useState

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command'; // <-- 3. Import Command
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'; // <-- 4. Import Popover
import { cn } from '@/lib/utils'; // <-- 5. Import cn
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Teacher } from '@/types/models/teachers';

interface ClassroomsFormProps {
    classroom?: Classroom;
    schoolAcademicYear: SchoolAcademicYear;
    teachers: Teacher[];
}

export default function ClassroomsForm({ classroom, schoolAcademicYear, teachers }: ClassroomsFormProps) {
    const isEditMode = !!classroom;
    // 6. Tambahkan state untuk mengontrol Popover
    const [open, setOpen] = useState(false);

    const { data, setData, post, put, processing, errors } = useForm({
        name: classroom?.name ?? '',
        teacher_id: classroom?.teacher_id ?? '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditMode) {
            // [UBAH] Tambahkan logika untuk update
            put(
                route('protected.school-academic-years.classrooms.update', {
                    schoolAcademicYear: schoolAcademicYear.id,
                    classroom: classroom.id,
                }),
            );
        } else {
            post(route('protected.school-academic-years.classrooms.store', { schoolAcademicYear: schoolAcademicYear.id }));
        }
    };

    return (
        <Card>
            <CardHeader>{/* ... */}</CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="grid gap-6">
                    <div className="space-y-2">
                        <Label htmlFor="name">
                            Nama Kelas <span className="text-red-500">*</span>
                        </Label>
                        <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="Contoh: Kelas 1A" />
                        <InputError message={errors.name} />
                    </div>

                    {/* [UBAH] Ganti Select standar dengan Combobox */}
                    <div className="space-y-2">
                        <Label htmlFor="teacher_id">
                            Wali Kelas <span className="text-red-500">*</span>
                        </Label>
                        <Popover open={open} onOpenChange={setOpen}>
                            <PopoverTrigger asChild>
                                <Button variant="outline" role="combobox" aria-expanded={open} className="w-full justify-between">
                                    {data.teacher_id ? teachers.find((teacher) => teacher.id === data.teacher_id)?.name : 'Pilih wali kelas...'}
                                    <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-[--radix-popover-trigger-width] p-0">
                                <Command>
                                    <CommandInput placeholder="Cari nama guru..." />
                                    <CommandList>
                                        <CommandEmpty>Guru tidak ditemukan.</CommandEmpty>
                                        <CommandGroup>
                                            {teachers.map((teacher) => (
                                                <CommandItem
                                                    key={teacher.id}
                                                    value={teacher.name}
                                                    onSelect={() => {
                                                        setData('teacher_id', teacher.id);
                                                        setOpen(false);
                                                    }}
                                                >
                                                    <Check
                                                        className={cn('mr-2 h-4 w-4', data.teacher_id === teacher.id ? 'opacity-100' : 'opacity-0')}
                                                    />
                                                    {teacher.name}
                                                </CommandItem>
                                            ))}
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                        <InputError message={errors.teacher_id} />
                    </div>

                    <div className="flex justify-end">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : isEditMode ? 'Simpan Perubahan' : 'Buat Kelas'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
