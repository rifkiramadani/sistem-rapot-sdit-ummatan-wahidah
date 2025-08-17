import { useForm } from '@inertiajs/react'

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type AcademicYear } from '@/types/models/academic-years';
import { FormEvent } from 'react';

interface AcademicYearsFormProps {
    academicYear?: AcademicYear;
}


export default function AcademicYearsForm({ academicYear }: AcademicYearsFormProps) {

    const isEditMode = !!academicYear;

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        start: '',
        end: ''
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post(route('protected.academic-years.store'), {
            preserveScroll: false
        });
        // if (isEditMode) {
        //     // put(route('protected.schools.update', school.id));
        // } else {
        //     post(route('protected.academic-years.store'));
        // }
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>{isEditMode ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran Baru'}</CardTitle>
                <CardDescription>
                    {isEditMode ? 'Lakukan perubahan pada data sekolah yang sudah ada.' : 'Isi formulir di bawah ini untuk menambahkan tahun ajaran baru.'}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="grid gap-6 md:grid-cols-2">
                    {/* Nama / Tahun Ajaran */}
                    <div className="space-y-2 md:col-span-2">
                        <Label htmlFor="name">
                            Tahun Ajaran<span className="text-red-500">*</span>
                        </Label>
                        <Input id="name" name="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        {/* ++ 2. Gunakan komponen InputError */}
                        <InputError message={errors.name} />
                    </div>

                    {/* Mulai */}
                    <div className="space-y-2 md:col-span-2">
                        <Label htmlFor="start">
                            Mulai <span className="text-red-500">*</span>
                        </Label>
                        <Input type='date' id='start' name='start' value={data.start} onChange={(e) => setData('start', e.target.value)} />
                        <InputError message={errors.start} />
                    </div>

                    {/* Selesai */}
                    <div className="space-y-2 md:col-span-2">
                        <Label htmlFor="end">
                            Selesai <span className="text-red-500">*</span>
                        </Label>
                        <Input id="end" name="end" type="date" value={data.end} onChange={(e) => setData('end', e.target.value)} />
                        <InputError message={errors.end} />
                    </div>

                    {/* Tombol Submit */}
                    <div className="flex justify-end md:col-span-2">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : isEditMode ? 'Simpan Perubahan' : 'Buat Tahun Ajaran'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    )
}

