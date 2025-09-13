import { Button } from '@/components/ui/button';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
// import { Route } from 'lucide-react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Sistem Informasi Rapor SDIT Ummatan Wahidah">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
                <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
                    <nav className="flex items-center justify-end gap-4">
                        {/* test */}
                    </nav>
                </header>
                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                    <main className="flex w-full max-w-[335px] flex-col-reverse lg:max-w-4xl lg:flex-row">
                        <div className="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                            <h1 className="mb-1 text-3xl font-bold">Selamat Datang di Sistem Informasi Rapor SDIT Ummatan Wahidah</h1>
                            <p className="mb-5 text-[#706f6c] dark:text-[#A1A09A]">
                                Sistem Informasi rapor SDIT Ummatan Wahidah adalah
                                aplikasi berbasis website untuk manajemen nilai sumatif akhir siswa.
                            </p>
                            <ul className="flex gap-3 text-sm leading-normal">
                                {auth.user ? (
                                    <li>
                                        <Link
                                            href={route('protected.dashboard.index')}
                                        >
                                            <Button className='hover:bg-gray-700 hover:text-white'>
                                                Kembali Ke Dashboard
                                            </Button>
                                        </Link>
                                    </li>
                                ) : (
                                    <>
                                            <li>
                                                <Link
                                                    href={route('login')}
                                                >
                                                    <Button className='hover:bg-gray-300 hover:text-black dark:hover:bg-gray-700 dark:hover:text-white'>
                                                        Masuk
                                                    </Button>
                                                </Link>
                                            </li>
                                            <li>
                                                <Link
                                                    href={route('register')}
                                                >
                                                    <Button className='hover:bg-gray-300 hover:text-black dark:hover:bg-gray-700 dark:hover:text-dark'>
                                                        Daftar
                                                    </Button>
                                                </Link>
                                            </li>
                                    </>
                                )}
                            </ul>
                        </div>
                        <div className="relative -mb-px aspect-[335/376] w-full shrink-0 overflow-hidden rounded-t-lg bg-gray-300 lg:mb-0 lg:-ml-px lg:aspect-auto lg:w-[438px] lg:rounded-t-none lg:rounded-r-lg dark:bg-gray[900]">
                            <img
                                src="/assets/logo/sdit_ummatan_wahidah_logo.png"
                                alt="Logo SDIT Ummatan Wahidah"
                                className="object-cover absolute inset-0 w-90 ms-10 mt-10"
                            />
                            <div className="absolute inset-0 rounded-t-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-t-none lg:rounded-r-lg dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]" />
                        </div>
                    </main>
                </div>
                <div className="hidden h-14.5 lg:block"></div>
            </div>
        </>
    );
}
