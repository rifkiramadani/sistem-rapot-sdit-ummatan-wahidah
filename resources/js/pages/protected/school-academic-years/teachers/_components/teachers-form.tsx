// resources/js/Pages/protected/school-academic-years/teachers/_components/TeachersForm.tsx

import { useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type SchoolAcademicYear } from '@/types/models/school-academic-years';
import { type Teacher } from '@/types/models/teachers';

interface TeachersFormProps {
    teacher?: Teacher;
    schoolAcademicYear: SchoolAcademicYear;
    // 'users' prop tidak lagi diperlukan
}

export default function TeachersForm({ teacher, schoolAcademicYear }: TeachersFormProps) {
    const isEditMode = !!teacher;

    const { data, setData, post, put, processing, errors } = useForm({
        name: teacher?.name ?? '', // 'name' is defined
        niy: teacher?.niy ?? '',
        email: teacher?.user?.email ?? '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditMode) {
            put(
                route('protected.school-academic-years.teachers.update', {
                    schoolAcademicYear: schoolAcademicYear.id,
                    teacher: teacher.id,
                }),
            );
        } else {
            post(route('protected.school-academic-years.teachers.store', { schoolAcademicYear: schoolAcademicYear.id }));
        }
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>{isEditMode ? 'Edit Guru' : 'Tambah Guru Baru'}</CardTitle>
                <CardDescription>
                    {isEditMode
                        ? 'Lakukan perubahan pada data guru. Kosongkan password jika tidak ingin mengubahnya.'
                        : 'Formulir ini akan membuat data guru beserta akun pengguna baru.'}
                </CardDescription>
            </CardHeader>
            <CardContent>
                {/* [UBAH] Tambahkan md:grid-cols-2 untuk membuat layout 2 kolom di layar medium */}
                <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {/* Nama Guru (untuk Teacher & User) */}
                    {/* [UBAH] Gunakan md:col-span-2 agar input ini memenuhi 2 kolom */}
                    <div className="space-y-2 md:col-span-2">
                        <Label htmlFor="name">
                            Nama Lengkap <span className="text-red-500">*</span>
                        </Label>
                        <Input id="name" name="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        <InputError message={errors.name} />
                    </div>

                    {/* NIY (Nomor Induk Yayasan) */}
                    {/* Input ini akan otomatis menempati kolom pertama */}
                    <div className="space-y-2">
                        <Label htmlFor="niy">
                            Nomor Induk Yayasan (NIY) <span className="text-red-500">*</span>
                        </Label>
                        <Input id="niy" name="niy" type="number" value={data.niy} onChange={(e) => setData('niy', e.target.value)} />
                        <InputError message={errors.niy} />
                    </div>

                    {/* Email untuk akun User baru */}
                    {/* Input ini akan otomatis menempati kolom kedua */}
                    <div className="space-y-2">
                        <Label htmlFor="email">
                            Email Akun <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            disabled={isEditMode}
                        />
                        <InputError message={errors.email} />
                    </div>

                    {/* Password untuk akun User baru */}
                    <div className="space-y-2">
                        <Label htmlFor="password">Password {isEditMode ? '(Opsional)' : <span className="text-red-500">*</span>}</Label>
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        <InputError message={errors.password} />
                    </div>

                    {/* Konfirmasi Password */}
                    <div className="space-y-2">
                        <Label htmlFor="password_confirmation">Konfirmasi Password {isEditMode ? '' : <span className="text-red-500">*</span>}</Label>
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>

                    {/* [UBAH] Bungkus tombol di div yang memenuhi 2 kolom untuk alignment yang benar */}
                    <div className="flex justify-end md:col-span-2">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : isEditMode ? 'Simpan Perubahan' : 'Buat Guru'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
