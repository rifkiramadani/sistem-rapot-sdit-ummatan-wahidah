'use client';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { ClassroomSubject } from '@/types/models/classroom-subjects';
import { Classroom } from '@/types/models/classrooms';
import { SchoolAcademicYear } from '@/types/models/school-academic-years';
import { Subject } from '@/types/models/subjects';
import { SummativeType } from '@/types/models/summative-types';
import { Summative } from '@/types/models/summatives';
import { useForm } from '@inertiajs/react';
import React, { type FormEvent, useState, useEffect, useMemo } from 'react';

interface SummativeFormProps {
    schoolAcademicYear: SchoolAcademicYear;
    classroom: Classroom;
    classroomSubject: ClassroomSubject;
    subject: Subject;
    summativeTypes: SummativeType[];
    summative?: Summative;
}

export default function SummativeForm({ schoolAcademicYear, classroom, classroomSubject, subject, summativeTypes, summative }: SummativeFormProps) {
    const isEditMode = !!summative;

    const { data, setData, post, put, processing, errors } = useForm({
        name: summative?.name ?? '',
        identifier: summative?.identifier ?? '',
        description: summative?.description ?? '',
        summative_type_id: summative?.summative_type_id ?? '',
        prominent: summative?.prominent ?? '',
        improvement: summative?.improvement ?? '',
    });

    // Tentukan apakah jenis sumatif yang dipilih adalah 'Sumatif Materi'
    const isMateriType = useMemo(() => {
        const selectedType = summativeTypes.find(type => type.id === data.summative_type_id);
        return selectedType?.name === 'Sumatif Materi';
    }, [data.summative_type_id, summativeTypes]);

    // Hapus data untuk input yang disembunyikan ketika jenis sumatif berubah
    useEffect(() => {
        // Hanya jalankan di mode tambah, bukan di mode edit
        if (!isEditMode && !isMateriType) {
            setData({
                ...data,
                identifier: '',
                prominent: '',
                improvement: ''
            });
        }
    }, [isMateriType, isEditMode, setData]);

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        if (isEditMode && summative) {
            put(
                route('protected.school-academic-years.classrooms.subjects.summatives.update', {
                    schoolAcademicYear,
                    classroom,
                    classroomSubject,
                    summative,
                })
            );
        } else {
            post(
                route('protected.school-academic-years.classrooms.subjects.summatives.store', {
                    schoolAcademicYear,
                    classroom,
                    classroomSubject,
                })
            );
        }
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>{isEditMode ? 'Edit Sumatif' : 'Tambah Sumatif Baru'}</CardTitle>
                <CardDescription>Isi detail penilaian sumatif untuk mata pelajaran {subject.name}.</CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div className="space-y-2 md:col-span-2">
                        <Label>
                            Jenis Sumatif <span className="text-red-500">*</span>
                        </Label>
                        <Select
                            value={data.summative_type_id}
                            onValueChange={(value) => setData('summative_type_id', value)}
                            disabled={isEditMode}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih jenis sumatif..." />
                            </SelectTrigger>
                            <SelectContent>
                                {summativeTypes.map((type) => (
                                    <SelectItem key={type.id} value={type.id}>
                                        {type.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.summative_type_id} />
                    </div>

                    <div className="space-y-2 md:col-span-2">
                        <Label>
                            Nama Sumatif <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Contoh: Ujian Tengah Semester Ganjil"
                        />
                        <InputError message={errors.name} />
                    </div>

                    {isMateriType && (
                        <>
                            <div className="space-y-2">
                                <Label>
                                    Identifier {isMateriType && <span className="text-red-500">*</span>}
                                </Label>
                                <Input value={data.identifier ?? ''} onChange={(e) => setData('identifier', e.target.value)} placeholder="Contoh: M1" />
                                <InputError message={errors.identifier} />
                            </div>

                            <div className="space-y-2"></div>

                            <div className="space-y-2 md:col-span-2">
                                <Label>
                                    Materi Paling Menonjol {isMateriType && <span className="text-red-500">*</span>}
                                </Label>
                                <Textarea
                                    value={data.prominent || ''}
                                    onChange={(e) => setData('prominent', e.target.value)}
                                    placeholder="Contoh: Menunjukkan penguasaan yang baik tentang..."
                                />
                                <InputError message={errors.prominent} />
                            </div>

                            <div className="space-y-2 md:col-span-2">
                                <Label>
                                    Materi Yang Perlu Ditingkatkan {isMateriType && <span className="text-red-500">*</span>}
                                </Label>
                                <Textarea
                                    value={data.improvement || ''}
                                    onChange={(e) => setData('improvement', e.target.value)}
                                    placeholder="Contoh: Perlu bantuan pemahaman mengenai..."
                                />
                                <InputError message={errors.improvement} />
                            </div>
                        </>
                    )}

                    <div className="space-y-2 md:col-span-2">
                        <Label>Deskripsi (Opsional)</Label>
                        <Textarea
                            value={data.description || ''}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="Tulis deskripsi singkat mengenai penilaian sumatif ini..."
                        />
                        <InputError message={errors.description} />
                    </div>

                    <div className="flex justify-end md:col-span-2">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : isEditMode ? 'Simpan Perubahan' : 'Buat Sumatif'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}