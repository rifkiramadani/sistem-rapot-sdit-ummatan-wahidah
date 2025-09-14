import { Button } from '@/components/ui/button';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Sistem Informasi Rapor SDIT Ummatan Wahidah">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div
                className="relative flex min-h-screen flex-col items-center justify-center p-6 lg:p-8"
                style={{
                    backgroundImage: `url('/assets/images/foto_guru.jpg')`,
                    backgroundSize: 'cover',
                    backgroundPosition: 'center',
                    backgroundRepeat: 'no-repeat',
                }}
            >
                {/* Opacity Overlay */}
                <div className="absolute inset-0 bg-black opacity-60"></div>

                {/* Content Container */}
                <div className="relative z-10 flex w-full flex-col items-center text-[#1b1b18] dark:text-[#EDEDEC]">
                    <div className='mb-5 flex justify-between opacity-100 transition-opacity duration-750 starting:opacity-0 lg:grow'>
                        <img
                            src="/assets/logo/sdit_ummatan_wahidah_logo.png"
                            alt="Logo SDIT Ummatan Wahidah"
                            className="w-65 h-65 mb-5"
                        />
                        <img
                            src="/assets/logo/yayasan_assalam_logo.png"
                            alt="Logo Yayasan Assalam"
                            className="w-55 h-55 mt-10"
                        />
                    </div>
                    <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 starting:opacity-0 lg:grow">
                        <main className="flex w-full max-w-[335px] flex-col-reverse lg:max-w-4xl lg:flex-row">
                            <div className="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-10 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                                <h1 className="mb-1 text-center text-4xl font-bold uppercase bg-clip-text text-transparent bg-gradient-to-r from-[#1DF01A] from-10% to-[#0E9351] to-90%">Selamat Datang di Sistem Informasi Rapor SDIT Ummatan Wahidah</h1>
                                <p className="mb-5 text-center uppercase text-[#706f6c] dark:text-white">
                                    Sistem Informasi rapor SDIT Ummatan Wahidah adalah
                                    aplikasi berbasis website untuk manajemen nilai sumatif akhir siswa.
                                </p>
                                <ul className="flex justify-center gap-3 text-sm leading-normal">
                                    {auth.user ? (
                                        <li>
                                            <Link href={route('protected.dashboard.index')}>
                                                <Button className='hover:bg-gray-700 hover:text-white'>
                                                    Kembali Ke Dashboard
                                                </Button>
                                            </Link>
                                        </li>
                                    ) : (
                                        <>
                                            <li>
                                                    <Link href={route('login')}>
                                                        <Button className='bg-[#773DCE] text-white hover:bg-[#3D138C] hover:text-white dark:hover:bg-[#3D138C] dark:hover:text-white'>
                                                        Masuk
                                                    </Button>
                                                </Link>
                                            </li>
                                            <li>
                                                    <Link href={route('register')}>
                                                        <Button className='bg-[#773DCE] text-white hover:bg-[#3D138C] hover:text-white dark:hover:bg-[#3D138C] dark:hover:text-white'>
                                                        Daftar
                                                    </Button>
                                                </Link>
                                            </li>
                                        </>
                                    )}
                                </ul>
                            </div>
                        </main>
                    </div>
                    <div className="hidden h-14.5 lg:block"></div>
                </div>
            </div>
        </>
    );
}
