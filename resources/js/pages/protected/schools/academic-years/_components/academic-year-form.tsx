// Di file: resources/js/pages/protected/schools/academic-years/_components/academic-year-form.tsx

'use client';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { type AcademicYear } from '@/types/models/academic-years';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years'; // Asumsi: Anda memiliki tipe ini
import { type School } from '@/types/models/schools';
import { useForm } from '@inertiajs/react';
import { Check, ChevronsUpDown } from 'lucide-react';
import { type FormEvent, useState } from 'react';

interface AcademicYearFormProps {
    school: School;
    academicYears: AcademicYear[];
    schoolAcademicYear?: SchoolAcademicYear; // Prop baru untuk mode edit
}

export default function AcademicYearForm({ school, academicYears, schoolAcademicYear }: AcademicYearFormProps) {
    // 1. Tambahkan flag isEditMode
    const isEditMode = !!schoolAcademicYear;

    const [open, setOpen] = useState(false);

    // 2. Inisialisasi data form berdasarkan mode
    const { data, setData, post, put, processing, errors } = useForm({
        academic_year_id: schoolAcademicYear?.academic_year_id ?? '',
    });

    // 3. Perbarui fungsi handleSubmit
    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditMode) {
            put(route('protected.schools.academic-years.update', { school: school.id, schoolAcademicYear: schoolAcademicYear.id }));
        } else {
            post(route('protected.schools.academic-years.store', { school: school.id }));
        }
    };

    return (
        <Card>
            <CardHeader>
                {/* 4. Buat judul dan deskripsi dinamis */}
                <CardTitle>{isEditMode ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran'}</CardTitle>
                <CardDescription>
                    {isEditMode
                        ? `Lakukan perubahan pada tahun ajaran yang tertaut ke sekolah ${school.name}.`
                        : `Pilih tahun ajaran yang ingin ditautkan ke sekolah ${school.name}.`}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="space-y-2">
                        <Label>
                            Tahun Ajaran <span className="text-red-500">*</span>
                        </Label>
                        <Popover open={open} onOpenChange={setOpen}>
                            <PopoverTrigger asChild>
                                <Button variant="outline" role="combobox" aria-expanded={open} className="w-full justify-between">
                                    {data.academic_year_id
                                        ? academicYears.find((ay) => ay.id === data.academic_year_id)?.name
                                        : 'Pilih tahun ajaran...'}
                                    <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-[--radix-popover-trigger-width] p-0">
                                <Command>
                                    <CommandInput placeholder="Cari tahun ajaran..." />
                                    <CommandList>
                                        <CommandEmpty>Tahun ajaran tidak ditemukan.</CommandEmpty>
                                        <CommandGroup>
                                            {academicYears.map((ay) => (
                                                <CommandItem
                                                    key={ay.id}
                                                    value={ay.name}
                                                    onSelect={() => {
                                                        setData('academic_year_id', ay.id);
                                                        setOpen(false);
                                                    }}
                                                >
                                                    <Check
                                                        className={cn('mr-2 h-4 w-4', data.academic_year_id === ay.id ? 'opacity-100' : 'opacity-0')}
                                                    />
                                                    {ay.name}
                                                </CommandItem>
                                            ))}
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                        <InputError message={errors.academic_year_id} />
                    </div>

                    <div className="flex justify-end">
                        {/* 5. Buat teks tombol dinamis */}
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : isEditMode ? 'Simpan Perubahan' : 'Simpan'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
