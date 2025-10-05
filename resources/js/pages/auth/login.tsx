// resources/js/Pages/Auth/Login.tsx
import { Head, useForm, usePage } from '@inertiajs/react';
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
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';

interface LoginPageProps {
    roles: string[];
    canResetPassword: boolean;
    status?: string;
}

// PERBAIKAN: Perluas PageProps untuk mengatasi error TypeScript dan mendapatkan user object
interface SharedData extends LoginPageProps {
    app: {
        env: string;
    }
    auth: {
        user: {
            id: number;
            role: string; // Tambahkan role untuk akses di frontend
        } | null;
    }
}

// Tambahkan field 'role' ke dalam type LoginForm
type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
    role: 'admin' | 'guru' | 'superadmin';
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    //  AMBIL app.env DARI usePage
    const { props: { app, auth } } = usePage<SharedData>(); // Menggunakan SharedData yang diperluas
    const isDevelopment = app.env !== 'production';

    // TAMBAHKAN clearErrors
    const { data, setData, post, processing, errors, reset, clearErrors } = useForm<Required<LoginForm>>({
        email: '',
        password: '',
        remember: false,
        role: 'admin',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        // Pastikan error form di klien dibersihkan SEBELUM permintaan dikirim
        clearErrors();

        post(route('login'), {
            onSuccess: () => {
                // Ketika otentikasi berhasil, redirect akan terjadi.
                clearErrors(); // Bersihkan semua error
                reset('password');
            },

            onError: (errors) => {
                // Jika ada error (baik role, email, atau password salah)
                reset('password');
            },

            onFinish: () => {
                // Memastikan password direset setelah form submission selesai
                reset('password');
            },
        });
    };

    return (
        <AuthLayout>
            <Head title="Log in" />
            <div className="flex flex-col items-center gap-6 lg:justify-start">
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
                        title="Yayasan Assalam"
                        className="w-40 h-40 object-contain mt-5"
                    />
                </a>
                <Card className="min-w-sm border-muted bg-background flex w-full max-w-sm flex-col items-center gap-y-4 rounded-md border px-6 py-8 shadow-md">
                    <div className="text-center">
                        <h1 className="text-xl font-semibold">Silahkan Masuk</h1>
                        <p className="text-sm text-muted-foreground">
                            Masukkan email, password, dan pilih peran untuk masuk.
                        </p>
                        {status && <div className="mt-2 text-sm font-medium text-green-600">{status}</div>}
                    </div>
                    <form className="flex w-full flex-col gap-y-4" onSubmit={submit}>

                        {/* FIELD PEMILIHAN ROLE DENGAN TABS SHADCN UI */}
                        <div className="flex w-full flex-col gap-2">
                            <Label htmlFor="role">Masuk Sebagai</Label>
                            <Tabs
                                value={data.role}
                                onValueChange={(value) => setData('role', value as 'admin' | 'guru' | 'superadmin')}
                                className="w-full"
                            >
                                {/* PENTING: Mengubah grid-cols untuk mengakomodasi 3 opsi saat dev */}
                                <TabsList
                                    className={`grid w-full
                                        ${isDevelopment ? 'grid-cols-3' : 'grid-cols-2'}
                                        h-10`}
                                >
                                    <TabsTrigger
                                        className='text-sm'
                                        value="admin"
                                        tabIndex={1}
                                    >
                                        Admin Sekolah
                                    </TabsTrigger>
                                    <TabsTrigger
                                        className='text-sm'
                                        value="guru"
                                    >
                                        Guru
                                    </TabsTrigger>
                                    {/*  KONDISIONAL UNTUK SUPER ADMIN */}
                                    {isDevelopment && (
                                        <TabsTrigger
                                            className='text-sm'
                                            value="superadmin"
                                        >
                                            Super Admin (Dev)
                                        </TabsTrigger>
                                    )}
                                </TabsList>
                            </Tabs>
                            {/*  PERBAIKAN KRITIS: Abaikan error role jika user sudah terotentikasi */}
                            <InputError message={auth.user ? undefined : errors.role} />
                        </div>
                        {/* END FIELD PEMILIHAN ROLE BARU */}

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
                                placeholder="email@example.com"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="flex w-full flex-col gap-2">
                            <div className="flex items-center">
                                <Label htmlFor="password">Password</Label>
                                {canResetPassword && (
                                    <TextLink href={route('password.request')} className="ml-auto text-sm" tabIndex={6}>
                                        Lupa password?
                                    </TextLink>
                                )}
                            </div>
                            <Input
                                id="password"
                                type="password"
                                required
                                tabIndex={3}
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
                                    tabIndex={4}
                                />
                                <Label htmlFor="remember">Ingat saya</Label>
                            </div>
                        </div>

                        <Button type="submit" className="w-full bg-[#773DCE] text-white hover:bg-[#3D138C] hover:text-white dark:hover:bg-[#3D138C] dark:hover:text-white" tabIndex={5} disabled={processing}>
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
                        tabIndex={7}
                    >
                        Daftar
                    </TextLink>
                </div>
            </div>
        </AuthLayout>
    );
}
