// resources/js/Components/schools/SchoolsForm.tsx

import { useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { type School } from '@/types/models/schools';

interface SchoolsFormProps {
    school?: School;
}

export default function SchoolsForm({ school }: SchoolsFormProps) {
    const isEditMode = !!school;

    const { data, setData, post, put, processing, errors } = useForm({
        name: school?.name ?? '',
        npsn: school?.npsn ?? '',
        address: school?.address ?? '',
        postal_code: school?.postal_code ?? '',
        website: school?.website ?? '',
        email: school?.email ?? '',
        place_date_raport: school?.place_date_raport ?? '',
        place_date_sts: school?.place_date_sts ?? '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditMode) {
            put(route('protected.schools.update', school.id));
        } else {
            post(route('protected.schools.store'));
        }
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>{isEditMode ? 'Edit Sekolah' : 'Tambah Sekolah Baru'}</CardTitle>
                <CardDescription>
                    {isEditMode ? 'Lakukan perubahan pada data sekolah yang sudah ada.' : 'Isi formulir di bawah ini untuk menambahkan sekolah baru.'}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="grid gap-6 md:grid-cols-2">
                    {/* Nama Sekolah */}
                    <div className="space-y-2 md:col-span-2">
                        <Label htmlFor="name">
                            Nama Sekolah <span className="text-red-500">*</span>
                        </Label>
                        <Input id="name" name="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        {/* ++ 2. Gunakan komponen InputError */}
                        <InputError message={errors.name} />
                    </div>

                    {/* Alamat */}
                    <div className="space-y-2 md:col-span-2">
                        <Label htmlFor="address">
                            Alamat <span className="text-red-500">*</span>
                        </Label>
                        <Textarea id="address" name="address" value={data.address} onChange={(e) => setData('address', e.target.value)} />
                        <InputError message={errors.address} />
                    </div>

                    {/* NPSN Input */}
                    <div className="space-y-2">
                        <Label htmlFor="npsn">NPSN</Label>
                        <Input id="npsn" name="npsn" type="number" value={data.npsn} onChange={(e) => setData('npsn', e.target.value)} />
                        <InputError message={errors.npsn} />
                    </div>

                    {/* Postal Code Input */}
                    <div className="space-y-2">
                        <Label htmlFor="postal_code">Kode Pos</Label>
                        <Input
                            id="postal_code"
                            name="postal_code"
                            type="number"
                            value={data.postal_code}
                            onChange={(e) => setData('postal_code', e.target.value)}
                        />
                        <InputError message={errors.postal_code} />
                    </div>

                    {/* Website Input */}
                    <div className="space-y-2">
                        <Label htmlFor="website">Website</Label>
                        <Input
                            id="website"
                            name="website"
                            type="url"
                            value={data.website}
                            onChange={(e) => setData('website', e.target.value)}
                            placeholder="https://contoh.com"
                        />
                        <InputError message={errors.website} />
                    </div>

                    {/* Email Input */}
                    <div className="space-y-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="info@contoh.com"
                        />
                        <InputError message={errors.email} />
                    </div>

                    {/* Tempat & Tanggal Rapor */}
                    <div className="space-y-2">
                        <Label htmlFor="place_date_raport">Tempat & Tanggal Rapor</Label>
                        <Input
                            id="place_date_raport"
                            name="place_date_raport"
                            value={data.place_date_raport}
                            onChange={(e) => setData('place_date_raport', e.target.value)}
                            placeholder="Contoh: Palembang, 25 Desember 2025"
                        />
                        <InputError message={errors.place_date_raport} />
                    </div>

                    {/* Tempat & Tanggal STS */}
                    <div className="space-y-2">
                        <Label htmlFor="place_date_sts">Tempat & Tanggal STS</Label>
                        <Input
                            id="place_date_sts"
                            name="place_date_sts"
                            value={data.place_date_sts}
                            onChange={(e) => setData('place_date_sts', e.target.value)}
                            placeholder="Contoh: Palembang, 20 Juni 2026"
                        />
                        <InputError message={errors.place_date_sts} />
                    </div>

                    {/* Tombol Submit */}
                    <div className="flex justify-end md:col-span-2">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : isEditMode ? 'Simpan Perubahan' : 'Buat Sekolah'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
