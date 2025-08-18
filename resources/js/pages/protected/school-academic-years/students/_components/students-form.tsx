import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Student } from '@/types/models/students';
import { useForm } from '@inertiajs/react';
import { useEffect, type FormEvent } from 'react';

// Enum ini sudah cocok dengan backend
enum GenderEnum {
    Male = 'male',
    Female = 'female',
}

// [UBAH] Sesuaikan enum ini agar cocok dengan backend PHP
enum ReligionEnum {
    Muslim = 'muslim',
    Christian = 'christian',
    Catholic = 'catholic',
    Hindu = 'hindu',
    Buddhist = 'buddhist',
    Other = 'other',
}

enum GuardianTypeEnum {
    Father = 'father',
    Mother = 'mother',
    Other = 'other',
}

const genderLabels: Record<GenderEnum, string> = {
    [GenderEnum.Male]: 'Laki-laki',
    [GenderEnum.Female]: 'Perempuan',
};

// [UBAH] Sesuaikan label untuk enum Religion yang baru
const religionLabels: Record<ReligionEnum, string> = {
    [ReligionEnum.Muslim]: 'Islam',
    [ReligionEnum.Christian]: 'Kristen Protestan',
    [ReligionEnum.Catholic]: 'Kristen Katolik',
    [ReligionEnum.Hindu]: 'Hindu',
    [ReligionEnum.Buddhist]: 'Buddha',
    [ReligionEnum.Other]: 'Lainnya',
};

// [UBAH] Tambahkan label untuk enum tipe wali
const guardianTypeLabels: Record<GuardianTypeEnum, string> = {
    [GuardianTypeEnum.Father]: 'Ayah',
    [GuardianTypeEnum.Mother]: 'Ibu',
    [GuardianTypeEnum.Other]: 'Lainnya (Isi Manual)',
};

interface StudentsFormProps {
    student?: Student; // Untuk mode edit nanti
    schoolAcademicYear: SchoolAcademicYear;
}

// Komponen helper untuk membuat sub-judul di dalam form
function FormSectionTitle({ title, description }: { title: string; description?: string }) {
    return (
        <div className="mt-6 first:mt-0 md:col-span-2">
            <h3 className="text-lg font-medium">{title}</h3>
            {description && <p className="text-sm text-muted-foreground">{description}</p>}
            <hr className="mt-2" />
        </div>
    );
}

export default function StudentsForm({ student, schoolAcademicYear }: StudentsFormProps) {
    const isEditMode = !!student;

    const { data, setData, post, processing, errors } = useForm({
        // Data Siswa
        nisn: student?.nisn ?? '',
        name: student?.name ?? '',
        gender: student?.gender ?? '',
        birth_place: student?.birth_place ?? '',
        birth_date: student?.birth_date ?? '',
        religion: student?.religion ?? '',
        address: student?.address ?? '',

        // Data Orang Tua
        father_name: student?.parent?.father_name ?? '',
        mother_name: student?.parent?.mother_name ?? '',
        father_job: student?.parent?.father_job ?? '',
        mother_job: student?.parent?.mother_job ?? '',
        parent_address: student?.parent?.address ?? '',

        guardian_type: GuardianTypeEnum.Other,

        // Data Wali
        guardian_name: student?.guardian?.name ?? '',
        guardian_job: student?.guardian?.job ?? '',
        guardian_phone_number: student?.guardian?.phone_number ?? '',
        guardian_address: student?.guardian?.address ?? '',
        // TODO: Optional current class input
    });

    useEffect(() => {
        if (data.guardian_type === GuardianTypeEnum.Father) {
            setData('guardian_name', data.father_name);
            setData('guardian_job', data.father_job);
            setData('guardian_address', data.parent_address);
        } else if (data.guardian_type === GuardianTypeEnum.Mother) {
            setData('guardian_name', data.mother_name);
            setData('guardian_job', data.mother_job);
            setData('guardian_address', data.parent_address);
        }
    }, [data.guardian_type, data.father_name, data.mother_name, data.father_job, data.mother_job, data.parent_address]);

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        // Untuk saat ini hanya handle create
        post(route('protected.school-academic-years.students.store', { schoolAcademicYear: schoolAcademicYear.id }));
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>{isEditMode ? 'Edit Siswa' : 'Tambah Siswa Baru'}</CardTitle>
                <CardDescription>Isi semua data yang diperlukan di bawah ini.</CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <FormSectionTitle title="Data Diri Siswa" />
                    <div className="space-y-2">
                        <Label htmlFor="nisn">
                            NISN <span className="text-red-500">*</span>
                        </Label>
                        <Input id="nisn" value={data.nisn} onChange={(e) => setData('nisn', e.target.value)} />
                        <InputError message={errors.nisn} />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="name">
                            Nama Lengkap <span className="text-red-500">*</span>
                        </Label>
                        <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        <InputError message={errors.name} />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="gender">
                            Jenis Kelamin <span className="text-red-500">*</span>
                        </Label>
                        <Select value={data.gender} onValueChange={(value) => setData('gender', value)}>
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih jenis kelamin..." />
                            </SelectTrigger>
                            {/* [UBAH] Gunakan enum dan map label untuk me-render pilihan */}
                            <SelectContent>
                                {Object.values(GenderEnum).map((value) => (
                                    <SelectItem key={value} value={value}>
                                        {genderLabels[value]}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.gender} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="religion">
                            Agama <span className="text-red-500">*</span>
                        </Label>
                        <Select value={data.religion} onValueChange={(value) => setData('religion', value)}>
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih agama..." />
                            </SelectTrigger>
                            {/* [UBAH] Gunakan enum dan map label untuk me-render pilihan */}
                            <SelectContent>
                                {Object.values(ReligionEnum).map((value) => (
                                    <SelectItem key={value} value={value}>
                                        {religionLabels[value]}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.religion} />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="birth_place">
                            Tempat Lahir <span className="text-red-500">*</span>
                        </Label>
                        <Input id="birth_place" value={data.birth_place} onChange={(e) => setData('birth_place', e.target.value)} />
                        <InputError message={errors.birth_place} />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="birth_date">
                            Tanggal Lahir <span className="text-red-500">*</span>
                        </Label>
                        <Input id="birth_date" type="date" value={data.birth_date} onChange={(e) => setData('birth_date', e.target.value)} />
                        <InputError message={errors.birth_date} />
                    </div>
                    <div className="space-y-2 md:col-span-2">
                        <Label htmlFor="address">
                            Alamat Siswa <span className="text-red-500">*</span>
                        </Label>
                        <Textarea id="address" value={data.address} onChange={(e) => setData('address', e.target.value)} />
                        <InputError message={errors.address} />
                    </div>

                    <FormSectionTitle title="Data Orang Tua" />
                    <div className="space-y-2">
                        <Label htmlFor="father_name">
                            Nama Ayah <span className="text-red-500">*</span>
                        </Label>
                        <Input id="father_name" value={data.father_name} onChange={(e) => setData('father_name', e.target.value)} />
                        <InputError message={errors.father_name} />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="mother_name">
                            Nama Ibu <span className="text-red-500">*</span>
                        </Label>
                        <Input id="mother_name" value={data.mother_name} onChange={(e) => setData('mother_name', e.target.value)} />
                        <InputError message={errors.mother_name} />
                    </div>

                    {/* ++ Tambahkan Input untuk Pekerjaan Orang Tua di sini ++ */}
                    <div className="space-y-2">
                        <Label htmlFor="father_job">Pekerjaan Ayah</Label>
                        <Input id="father_job" value={data.father_job} onChange={(e) => setData('father_job', e.target.value)} />
                        <InputError message={errors.father_job} />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="mother_job">Pekerjaan Ibu</Label>
                        <Input id="mother_job" value={data.mother_job} onChange={(e) => setData('mother_job', e.target.value)} />
                        <InputError message={errors.mother_job} />
                    </div>

                    <div className="space-y-2 md:col-span-2">
                        <Label htmlFor="parent_address">
                            Alamat Orang Tua <span className="text-red-500">*</span>
                        </Label>
                        <Textarea id="parent_address" value={data.parent_address} onChange={(e) => setData('parent_address', e.target.value)} />
                        <InputError message={errors.parent_address} />
                    </div>

                    <div className="space-y-2 md:col-span-2">
                        <Label htmlFor="guardian_type">
                            Wali Siswa Adalah <span className="text-red-500">*</span>
                        </Label>
                        <Select
                            value={data.guardian_type}
                            onValueChange={(value) => {
                                setData((prevData) => ({
                                    ...prevData,
                                    guardian_type: value as GuardianTypeEnum, // Gunakan enum
                                    guardian_name: '',
                                    guardian_job: '',
                                    guardian_phone_number: '',
                                    guardian_address: '',
                                }));
                            }}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih wali..." />
                            </SelectTrigger>
                            {/* [UBAH] Render pilihan dari enum */}
                            <SelectContent>
                                {Object.values(GuardianTypeEnum).map((value) => (
                                    <SelectItem key={value} value={value}>
                                        {guardianTypeLabels[value]}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    {/* [UBAH] Buat input wali menjadi dinamis (disabled/enabled) */}
                    <div className="space-y-2">
                        <Label htmlFor="guardian_name">
                            Nama Wali <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="guardian_name"
                            value={data.guardian_name}
                            onChange={(e) => setData('guardian_name', e.target.value)}
                            disabled={data.guardian_type === 'father' || data.guardian_type === 'mother'}
                        />
                        <InputError message={errors.guardian_name} />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="guardian_job">Pekerjaan Wali</Label>
                        <Input
                            id="guardian_job"
                            value={data.guardian_job}
                            onChange={(e) => setData('guardian_job', e.target.value)}
                            disabled={data.guardian_type === 'father' || data.guardian_type === 'mother'}
                        />
                        <InputError message={errors.guardian_job} />
                    </div>
                    <div className="space-y-2 md:col-span-2">
                        <Label htmlFor="guardian_address">
                            Alamat Wali <span className="text-red-500">*</span>
                        </Label>
                        <Textarea
                            id="guardian_address"
                            value={data.guardian_address}
                            onChange={(e) => setData('guardian_address', e.target.value)}
                            disabled={data.guardian_type === 'father' || data.guardian_type === 'mother'}
                        />
                        <InputError message={errors.guardian_address} />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="guardian_phone_number">No. Telp Wali</Label>
                        <Input
                            id="guardian_phone_number"
                            value={data.guardian_phone_number}
                            onChange={(e) => setData('guardian_phone_number', e.target.value)}
                            disabled={data.guardian_type === 'other' ? false : true}
                        />
                        <InputError message={errors.guardian_phone_number} />
                    </div>

                    <div className="flex justify-end md:col-span-2">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : 'Simpan Data Siswa'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
