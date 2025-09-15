import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { Card } from '@/components/ui/card';

type RegisterForm = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm<Required<RegisterForm>>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout>
            <Head title="Register" />
            <div className="flex flex-col items-center gap-6 lg:justify-start">
                {/* Logo Sekolah */}
                <a href="/" className='flex flex-col md:flex-row justify-center items-center gap-5 mb-5'>
                    <img
                        src="/assets/logo/sdit_ummatan_wahidah_logo.png"
                        alt="Logo SDIT Ummatan Wahidah"
                        title="SDIT Ummatan Wahidah"
                        className="h-50 w-50"
                    />
                    <img
                        src="/assets/logo/yayasan_assalam_logo.png"
                        alt="Logo Yayasan Assalam"
                        className="w-40 h-40 object-contain mt-5"
                    />
                </a>
                <Card className="min-w-sm border-muted bg-background flex w-full max-w-sm flex-col items-center gap-y-4 rounded-md border px-6 py-8 shadow-md">
                    <div className="text-center">
                        <h1 className="text-xl font-semibold">Silahkan Daftar</h1>
                        <p className="text-sm text-muted-foreground">
                            Masukkan detail di bawah untuk membuat akun.
                        </p>
                    </div>
                    <form className="flex w-full flex-col gap-y-4" onSubmit={submit}>
                        <div className="flex w-full flex-col gap-2">
                            <Label htmlFor="name">Nama Lengkap</Label>
                            <Input
                                id="name"
                                type="text"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                disabled={processing}
                                placeholder="Nama Lengkap"
                            />
                            <InputError message={errors.name} className="mt-2" />
                        </div>

                        <div className="flex w-full flex-col gap-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                tabIndex={2}
                                autoComplete="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                disabled={processing}
                                placeholder="email@example.com"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="flex w-full flex-col gap-2">
                            <Label htmlFor="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                required
                                tabIndex={3}
                                autoComplete="new-password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                disabled={processing}
                                placeholder="Password"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="flex w-full flex-col gap-2">
                            <Label htmlFor="password_confirmation">Konfirmasi Password</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                required
                                tabIndex={4}
                                autoComplete="new-password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                disabled={processing}
                                placeholder="Konfirmasi password"
                            />
                            <InputError message={errors.password_confirmation} />
                        </div>

                        <Button type="submit" className="w-full bg-[#773DCE] text-white hover:bg-[#3D138C] hover:text-white dark:hover:bg-[#3D138C] dark:hover:text-white" tabIndex={5} disabled={processing}>
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin mr-2" />}
                            Buat akun
                        </Button>
                    </form>
                </Card>
                <div className="text-muted-foreground flex justify-center gap-1 text-sm">
                    <p>Sudah punya akun?</p>
                    <TextLink
                        href={route('login')}
                        className="text-primary font-medium hover:underline"
                        tabIndex={6}
                    >
                        Masuk
                    </TextLink>
                </div>
            </div>
        </AuthLayout>
    );
}
