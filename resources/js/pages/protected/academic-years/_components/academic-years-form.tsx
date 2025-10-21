import { useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { CalendarIcon } from 'lucide-react';
import { FormEvent, useEffect } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { type AcademicYear } from '@/types/models/academic-years';

interface AcademicYearsFormProps {
    academicYear?: AcademicYear;
}

export default function AcademicYearsForm({ academicYear }: AcademicYearsFormProps) {
    const isEditMode = !!academicYear;

    const { data, setData, post, put, processing, errors } = useForm({
        name: academicYear?.name ?? '',
        start: academicYear?.start ? format(new Date(academicYear.start), 'yyyy-MM-dd') : '',
        end: academicYear?.end ? format(new Date(academicYear.end), 'yyyy-MM-dd') : '',
    });

    useEffect(() => {
        if (data.start && data.end) {
            try {
                const startYear = new Date(data.start).getFullYear();
                const endYear = new Date(data.end).getFullYear();

                if (!isNaN(startYear) && !isNaN(endYear)) {
                    const academicYearName = `${startYear}/${endYear}`;
                    setData('name', academicYearName);
                }
            // eslint-disable-next-line @typescript-eslint/no-unused-vars, @typescript-eslint/no-explicit-any
            } catch (error: any) {
                setData('name', '');
            }
        } else {
            setData('name', '');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.start, data.end]);

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        if (isEditMode) {
            put(route('protected.academic-years.update', academicYear.id));
        } else {
            post(route('protected.academic-years.store'));
        }
    }

    const currentYear = new Date().getFullYear();

    return (
        <Card>
            <CardHeader>
                <CardTitle>{isEditMode ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran Baru'}</CardTitle>
                <CardDescription>
                    {isEditMode
                        ? 'Lakukan perubahan pada data tahun ajaran yang sudah ada.'
                        : 'Isi formulir di bawah ini untuk menambahkan tahun ajaran baru.'}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form onSubmit={handleSubmit} className="grid gap-6 md:grid-cols-2">
                    <div className="space-y-2 md:col-span-2">
                        <Label htmlFor="name">
                            Tahun Ajaran<span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="name"
                            name="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            disabled
                            placeholder="Akan terisi otomatis..."
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="start">
                            Mulai <span className="text-red-500">*</span>
                        </Label>
                        <Popover>
                            <PopoverTrigger asChild>
                                <Button
                                    variant={'outline'}
                                    className={cn('w-full justify-start text-left font-normal', !data.start && 'text-muted-foreground')}
                                >
                                    <CalendarIcon className="mr-2 h-4 w-4" />
                                    {data.start ? format(new Date(data.start), 'PPP') : <span>Pilih tanggal</span>}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-auto p-0">
                                <Calendar
                                    mode="single"
                                    captionLayout="dropdown"
                                    fromYear={currentYear - 10}
                                    toYear={currentYear + 10}
                                    selected={data.start ? new Date(data.start) : undefined}
                                    onSelect={(date) => {
                                        if (!date) return;
                                        const newStartDateString = format(date, 'yyyy-MM-dd');
                                        setData('start', newStartDateString);
                                        // Clear end date if it's outside the 1-year range
                                        if (data.end) {
                                            const startDate = new Date(newStartDateString);
                                            const oneYearLater = new Date(startDate);
                                            oneYearLater.setFullYear(startDate.getFullYear() + 1);

                                            const currentEndDate = new Date(data.end);
                                            if (currentEndDate < startDate || currentEndDate > oneYearLater) {
                                                setData('end', '');
                                            }
                                        }
                                    }}
                                    initialFocus
                                />
                            </PopoverContent>
                        </Popover>
                        <InputError message={errors.start} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="end">
                            Selesai <span className="text-red-500">*</span>
                        </Label>
                        <p className="text-xs text-muted-foreground">
                            (Maksimal 1 tahun setelah tanggal mulai)
                        </p>
                        <Popover>
                            <PopoverTrigger asChild>
                                <Button
                                    variant={'outline'}
                                    className={cn('w-full justify-start text-left font-normal', !data.end && 'text-muted-foreground')}
                                    disabled={!data.start}
                                >
                                    <CalendarIcon className="mr-2 h-4 w-4" />
                                    {data.end ? format(new Date(data.end), 'PPP') : <span>Pilih tanggal</span>}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-auto p-0">
                                <Calendar
                                    mode="single"
                                    captionLayout="dropdown"
                                    fromYear={currentYear - 10}
                                    toYear={currentYear + 10}
                                    selected={data.end ? new Date(data.end) : undefined}
                                    onSelect={(date) => {
                                        if (!date) return;
                                        const newEndDateString = format(date, 'yyyy-MM-dd');
                                        setData('end', newEndDateString);
                                    }}
                                    disabled={(date) => {
                                        if (!data.start) return true;

                                        const startDate = new Date(data.start);
                                        const oneYearLater = new Date(startDate);
                                        oneYearLater.setFullYear(startDate.getFullYear() + 1);

                                        // Enable dates from start date to exactly 1 year after start date
                                        return date < startDate || date > oneYearLater;
                                    }}
                                    defaultMonth={data.start ? new Date(data.start) : undefined}
                                    initialFocus
                                />
                            </PopoverContent>
                        </Popover>
                        <InputError message={errors.end} />
                    </div>

                    <div className="flex justify-end md:col-span-2">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : isEditMode ? 'Simpan Perubahan' : 'Buat Tahun Ajaran'}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
