import * as React from "react";
import { HandHelping, Users, Zap } from "lucide-react";

import { Badge } from "@/components/ui/badge";
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Button } from "@/components/ui/button";

const Welcome = () => {
    const { auth } = usePage<SharedData>().props;

    const [currentSlide, setCurrentSlide] = React.useState(0);

    const features = [
        {
            icon: <Users className="h-auto w-5" />,
            title: "Manajemen Data Guru dan Siswa",
            description: "Kelola data guru, siswa, dan wali murid dengan mudah. Tambahkan, edit, atau hapus informasi secara terpusat untuk akurasi data yang lebih baik.",
        },
        {
            icon: <HandHelping className="h-auto w-5" />,
            title: "Pengisian Nilai Rapor Otomatis",
            description: "Permudah proses pengisian nilai rapor dengan antarmuka yang intuitif. Sistem akan mengelola perhitungan nilai sumatif akhir secara otomatis.",
        },
        {
            icon: <Zap className="h-auto w-5" />,
            title: "Akses Rapor Digital",
            description: "Guru dapat mengakses dan mengunduh rapor digital siswa kapan saja dan di mana saja. Rapor tersimpan aman dan terorganisir di database.",
        },
    ];

    const guruImages = [
        '/assets/images/foto_guru.jpg',
        '/assets/images/foto_guru_2.jpg',
        '/assets/images/foto_guru_3.jpg',
    ];

    React.useEffect(() => {
        // Mengurangi interval menjadi 1500ms agar slide lebih cepat.
        // Transisi opacity 500ms akan memberikan efek cepat "fade to black".
        const timer = setInterval(() => {
            setCurrentSlide((prev) =>
                prev === guruImages.length - 1 ? 0 : prev + 1
            );
        }, 1500);

        return () => clearInterval(timer);
    }, [guruImages.length]);

    return (
        <>
            <Head title="Sistem Informasi Rapor SDIT Ummatan Wahidah" />

            <div className="relative min-h-screen w-full">

                {/* Background Auto-Sliding (Fade to Black) | Z-index: z-0 */}
                <div
                    // ✅ PENYESUAIAN PENTING: Tambahkan 'bg-black' sebagai latar belakang dasar.
                    // Saat gambar transisi dari opacity 100 ke 0, yang terlihat adalah warna hitam ini.
                    className="fixed inset-0 w-screen h-screen z-0 overflow-hidden bg-black"
                >
                    {/* Slides */}
                    {guruImages.map((imageSrc, index) => (
                        <div
                            key={index}
                            // ✅ PENYESUAIAN PENTING: Durasi Transisi Dipercepat (duration-500)
                            // Ini membuat gambar cepat menghilang (fade) ke latar belakang hitam.
                            className={`absolute inset-0 transition-opacity duration-500 ${index === currentSlide ? 'opacity-100' : 'opacity-0'
                                }`}
                        >
                            <img
                                src={imageSrc}
                                alt={`Background ${index + 1}`}
                                className="w-full h-full object-cover"
                            />
                            {/* Overlay gelap (dipertahankan untuk kontras teks) */}
                            <div className="absolute inset-0 bg-black opacity-50 md:opacity-60 lg:opacity-70"></div>
                        </div>
                    ))}
                </div>

                {/* Konten Utama | Z-index: z-10 */}
                <div className="relative z-10 min-h-screen w-full flex items-center py-10">
                    <section className="w-full">
                        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
                            <div className="flex flex-col items-center gap-6 text-center">
                                <div className='flex flex-col md:flex-row justify-center items-center gap-5 mb-8'>
                                    <img
                                        src="/assets/logo/sdit_ummatan_wahidah_logo.png"
                                        alt="Logo SDIT Ummatan Wahidah"
                                        className="w-32 h-32 md:w-45 md:h-45 object-contain"
                                    />
                                    <img
                                        src="/assets/logo/yayasan_assalam_logo.png"
                                        alt="Logo Yayasan Assalam"
                                        className="w-28 h-28 md:w-40 md:h-40 object-contain"
                                    />
                                </div>
                                <div className="max-w-4xl">
                                    {/* Badge tetap disesuaikan untuk kontras di Dark/Light Mode */}
                                    <Badge className="mb-6 px-4 py-2 bg-white dark:bg-black" variant="default">
                                        <span className="text-black dark:text-white text-sm md:text-base">Yayasan As-salam Curup</span>
                                    </Badge>
                                    <h1 className="text-3xl md:text-4xl lg:text-5xl mb-6 font-extrabold uppercase bg-clip-text text-transparent bg-gradient-to-r from-[#1DF01A] from-30% to-[#0E9351] to-70%">
                                        Selamat Datang di Sistem Informasi Rapor SDIT Ummatan Wahidah
                                    </h1>
                                    <p className="mb-3 text-center uppercase text-gray-200 text-sm md:text-base lg:text-lg">
                                        Sistem Informasi Rapor SDIT Ummatan Wahidah adalah <br className="hidden md:block" />
                                        aplikasi berbasis website untuk manajemen nilai sumatif akhir siswa.
                                    </p>
                                </div>
                            </div>

                        <div className="flex w-full items-center justify-center">
                            <main className="flex justify-center w-full max-w-[335px] flex-col-reverse lg:max-w-4xl lg:flex-row">
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
                                                        <Button className='w-50 bg-[#773DCE] text-white hover:bg-[#3D138C] hover:text-white dark:hover:bg-[#3D138C] dark:hover:text-white'>
                                                        Masuk
                                                    </Button>
                                                </Link>
                                            </li>
                                                <li>
                                                    <Link href={route('register')}>
                                                        <Button className='w-50 bg-[#773DCE] text-white hover:bg-[#3D138C] hover:text-white dark:hover:bg-[#3D138C] dark:hover:text-white'>
                                                        Daftar
                                                    </Button>
                                                </Link>
                                            </li>
                                        ) : (
                                            <>
                                                    <li className="w-full sm:w-auto">
                                                        <Link href={route('login')} className="w-full block">
                                                            <Button className='w-full bg-[#773DCE] text-white hover:bg-[#3D138C] hover:text-white dark:hover:bg-[#3D138C] dark:hover:text-white py-2 px-15'>
                                                                Masuk
                                                            </Button>
                                                        </Link>
                                                    </li>
                                                <li className="w-full sm:w-auto">
                                                    <Link href={route('register')} className="w-full block">
                                                            <Button className='w-full bg-[#773DCE] text-white hover:bg-[#3D138C] hover:text-white dark:hover:bg-[#3D138C] dark:hover:text-white py-2 px-15'>
                                                            Daftar
                                                        </Button>
                                                    </Link>
                                                </li>
                                            </>
                                        )}
                                    </ul>
                                </div>
                            </div>

                            {/* Features Section - KARTU DIOOPTIMALKAN UNTUK DARK/LIGHT MODE */}
                            <div className="mx-auto max-w-6xl flex flex-col md:flex-row gap-6 md:gap-6">
                                {features.map((feature, index) => (
                                    <div
                                        key={index}
                                        // Background Card yang buram dan adaptif Dark/Light Mode
                                        className="flex flex-col rounded-lg
                                                   bg-white/60 backdrop-blur-sm dark:bg-black/40
                                                   p-6 flex-1 shadow-lg
                                                   border border-gray-300/50 dark:border-white/20"
                                    >
                                        <div
                                            //PERBAIKAN PENTING: Latar belakang ikon harus selalu putih (light mode)
                                            // atau abu-abu/hitam terang (dark mode) agar ikonnya (hitam) kontras.
                                            // Kita buat latar belakang ikon selalu putih, dan ikon di dalamnya hitam.
                                            className="mb-4 flex size-12 items-center justify-center rounded-full bg-white dark:bg-black drop-shadow-lg"
                                        >
                                            {/* Ikon secara default berwarna hitam/dark-gray di `lucide-react` kecuali diberi warna spesifik */}
                                            {feature.icon}
                                        </div>
                                        {/* Teks Judul dan Deskripsi yang adaptif Dark/Light Mode */}
                                        <h3 className="mb-3 font-semibold text-lg text-gray-900 dark:text-white">{feature.title}</h3>
                                        <p className="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                            {feature.description}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </>
    );
};

export default Welcome;
