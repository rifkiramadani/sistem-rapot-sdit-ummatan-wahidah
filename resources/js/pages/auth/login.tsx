import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { Card } from '@/components/ui/card';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<Required<LoginForm>>({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout>
            <Head title="Log in" />
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
                        <h1 className="text-xl font-semibold">Silahkan Masuk</h1>
                        <p className="text-sm text-muted-foreground">
                            Masukkan email dan password untuk masuk.
                        </p>
                        {status && <div className="mt-2 text-sm font-medium text-green-600">{status}</div>}
                    </div>
                    <form className="flex w-full flex-col gap-y-4" onSubmit={submit}>
                        <div className="flex w-full flex-col gap-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="email@example.com"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="flex w-full flex-col gap-2">
                            <div className="flex items-center">
                                <Label htmlFor="password">Password</Label>
                                {canResetPassword && (
                                    <TextLink href={route('password.request')} className="ml-auto text-sm" tabIndex={5}>
                                        Lupa password?
                                    </TextLink>
                                )}
                            </div>
                            <Input
                                id="password"
                                type="password"
                                required
                                tabIndex={2}
                                autoComplete="current-password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Password"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="flex w-full items-center justify-between space-x-3">
                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    checked={data.remember}
                                    onClick={() => setData('remember', !data.remember)}
                                    tabIndex={3}
                                />
                                <Label htmlFor="remember">Ingat saya</Label>
                            </div>
                        </div>

                        <Button type="submit" className="w-full bg-[#773DCE] text-white hover:bg-[#3D138C] hover:text-white dark:hover:bg-[#3D138C] dark:hover:text-white" tabIndex={4} disabled={processing}>
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin mr-2" />}
                            Masuk
                        </Button>
                    </form>
                </Card>

                <div className="text-white flex justify-center gap-1 text-sm">
                    <p>Tidak punya akun?</p>
                    <TextLink
                        href={route('register')}
                        className="text-white font-medium hover:underline"
                        tabIndex={6}
                    >
                        Daftar
                    </TextLink>
                </div>
            </div>
        </AuthLayout>
    );
}
