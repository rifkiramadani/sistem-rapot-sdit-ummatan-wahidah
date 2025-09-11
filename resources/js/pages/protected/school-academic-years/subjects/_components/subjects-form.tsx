// resources/js/Pages/protected/school-academic-years/teachers/_components/TeachersForm.tsx

import { useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years';
import { type Subject } from '@/types/models/subjects';

interface SubjectsFormProps {
    subject?: Subject;
    schoolAcademicYear: SchoolAcademicYear;
    // 'users' prop tidak lagi diperlukan
}

export default function SubjectsForm({ subject, schoolAcademicYear }: SubjectsFormProps) {
    const isEditMode = !!subject;

    const { data, setData, post, put, processing, errors } = useForm({
        name: subject?.name ?? '', // 'name' is defined
        description: subject?.description ?? '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditMode) {
            put(
                route('protected.school-academic-years.subjects.update', {
                    schoolAcademicYear: schoolAcademicYear.id,
                    subject: subject.id,
                }),
            );
        } else {
            post(route('protected.school-academic-years.subjects.store', { schoolAcademicYear: schoolAcademicYear.id }));
        }
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>{isEditMode ? 'Edit Subject' : 'Tambah Subject Baru'}</CardTitle>
                <CardDescription>
                    {isEditMode
                        ? 'Lakukan perubahan pada data subject.'
                        : 'Formulir ini akan membuat data subject.'}
                </CardDescription>
            </CardHeader>
            <CardContent>
                {/* [UBAH] Tambahkan md:grid-cols-2 untuk membuat layout 2 kolom di layar medium */}
                <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {/* Nama Guru (untuk Teacher & User) */}
                    {/* [UBAH] Gunakan md:col-span-2 agar input ini memenuhi 2 kolom */}
                    <div className="space-y-2 md:col-span-1">
                        <Label htmlFor="name">
                            Nama Subject <span className="text-red-500">*</span>
                        </Label>
                        <Input id="name" name="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        <InputError message={errors.name} />
                    </div>

                    {/* description (deskripsi) */}
                    {/* Input ini akan otomatis menempati kolom pertama */}
                    <div className="space-y-2">
                        <Label htmlFor="description">
                            Deskripsi <span className="text-red-500">*</span>
                        </Label>
                        <Input id="description" name="description" type="text" value={data.description} onChange={(e) => setData('description', e.target.value)} />
                        <InputError message={errors.description} />
                    </div>

                    {/* [UBAH] Bungkus tombol di div yang memenuhi 2 kolom untuk alignment yang benar */}
                    <div className="flex justify-end md:col-span-2">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : isEditMode ? 'Simpan Perubahan' : 'Buat Subject'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
