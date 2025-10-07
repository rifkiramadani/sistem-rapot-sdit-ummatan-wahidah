import React, { FormEventHandler } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

// Import komponen Shadcn UI
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { CalendarClock } from 'lucide-react';

// --- Interface untuk Props ---

/**
 * Interface School (Definisi yang lebih lengkap sebaiknya di file '@/types/models/schools' Anda)
 */
interface SchoolProps {
    id: number;
    name: string;
    npsn: string | null;
    address: string | null;
    postal_code: string | null;
    website: string | null;
    email: string | null;
    place_date_raport: string | null;
    place_date_sts: string | null;
}

// Props yang diterima oleh komponen
interface MainSchoolDetailProps {
    school: SchoolProps;
}

// --- Komponen Helper InputField ---

interface InputFieldProps extends React.InputHTMLAttributes<HTMLInputElement> {
    label: string;
    id: string;
    value: string | number | null | undefined;
    onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
    error: string | undefined;
}

/**
 * Komponen InputField Helper
 * Menggunakan type eksplisit untuk prop dan event handler
 */
const InputField: React.FC<InputFieldProps> = ({ label, id, value, onChange, type = 'text', error, ...props }) => (
    <div className="space-y-2">
        <Label htmlFor={id}>{label}</Label>
        <Input
            type={type}
            id={id}
            name={id}
            value={value ?? ''} // Menggunakan nullish coalescing untuk default string kosong
            onChange={onChange}
            className={error ? 'border-red-500' : ''}
            {...props}
        />
        {error && <p className="text-sm font-medium text-red-500">{error}</p>}
    </div>
);


// --- Komponen Utama ---

export default function MainSchoolDetail({ school }: MainSchoolDetailProps) {

    // Mendefinisikan type untuk state form
    const { data, setData, put, processing, errors } = useForm<SchoolProps>({
        name: school.name || '',
        npsn: school.npsn || '',
        address: school.address || '',
        postal_code: school.postal_code || '',
        website: school.website || '',
        email: school.email || '',
        place_date_raport: school.place_date_raport || '',
        place_date_sts: school.place_date_sts || '',
    });

    // Explicitly type the submit handler
    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('protected.schools.update.detail'), {
            preserveScroll: true,
        });
    };

    // Tentukan URL Tahun Ajaran
    const academicYearIndexUrl = route('protected.schools.academic-years.index', school.id);

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Sekolah', href: route('protected.schools.detail') },
                { title: 'Detail Sekolah Utama', href: route('protected.schools.detail') }
            ]}
        >
            <Head title="Detail Sekolah Utama" />

            <div className="py-8">
                <div className="max-w-4xl mx-auto space-y-6">

                    {/* Tombol Navigasi Tahun Ajaran */}
                    <div className="flex justify-end">
                        {/* Menggunakan Link dari Inertia untuk navigasi */}
                        <Link href={academicYearIndexUrl}>
                            <Button variant="outline">
                                <CalendarClock className="mr-2 h-4 w-4" />
                                Tahun Ajaran
                            </Button>
                        </Link>
                    </div>

                    <form onSubmit={submit} className="space-y-6">

                        {/* Card: Informasi Dasar */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Informasi Dasar</CardTitle>
                                <CardDescription>Data identitas utama sekolah.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <InputField
                                    label="Nama Sekolah"
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    error={errors.name}
                                    required
                                />
                                <InputField
                                    label="NPSN (Nomor Pokok Sekolah Nasional)"
                                    id="npsn"
                                    value={data.npsn}
                                    onChange={(e) => setData('npsn', e.target.value)}
                                    error={errors.npsn}
                                />
                                <InputField
                                    label="Website"
                                    id="website"
                                    value={data.website}
                                    onChange={(e) => setData('website', e.target.value)}
                                    error={errors.website}
                                    type="url"
                                />
                                <InputField
                                    label="Email"
                                    id="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    error={errors.email}
                                    type="email"
                                />
                            </CardContent>
                        </Card>

                        {/* Card: Alamat */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Alamat</CardTitle>
                                <CardDescription>Alamat lengkap sekolah.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">

                                <div className="space-y-2">
                                    <Label htmlFor="address">Alamat Lengkap</Label>
                                    {/* Menggunakan Textarea, jadi event dan errors.address harus ditangani secara terpisah */}
                                    <Textarea
                                        id="address"
                                        name="address"
                                        value={data.address ?? ''} // Type aman
                                        onChange={(e) => setData('address', e.target.value)}
                                        className={errors.address ? 'border-red-500' : ''}
                                        required
                                    />
                                    {errors.address && <p className="text-sm font-medium text-red-500">{errors.address}</p>}
                                </div>

                                <InputField
                                    label="Kode Pos"
                                    id="postal_code"
                                    value={data.postal_code}
                                    onChange={(e) => setData('postal_code', e.target.value)}
                                    error={errors.postal_code}
                                />
                            </CardContent>
                        </Card>

                        {/* Card: Data Pelaporan (Rapor & STS) */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Data Pelaporan (Rapor & STS)</CardTitle>
                                <CardDescription>Data yang akan dicetak di dokumen rapor dan STS.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <InputField
                                    label="Tempat dan Tanggal Penulisan Rapor (Contoh: Jakarta, 30 Juni 2025)"
                                    id="place_date_raport"
                                    value={data.place_date_raport}
                                    onChange={(e) => setData('place_date_raport', e.target.value)}
                                    error={errors.place_date_raport}
                                />
                                <InputField
                                    label="Tempat dan Tanggal Penulisan STS (Contoh: Jakarta, 30 September 2025)"
                                    id="place_date_sts"
                                    value={data.place_date_sts}
                                    onChange={(e) => setData('place_date_sts', e.target.value)}
                                    error={errors.place_date_sts}
                                />
                            </CardContent>
                        </Card>

                        <div className="flex justify-end">
                            <Button
                                type="submit"
                                disabled={processing}
                            >
                                {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
