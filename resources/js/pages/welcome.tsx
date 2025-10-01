import * as React from "react";
import { HandHelping, Users, Zap } from "lucide-react";

import { Badge } from "@/components/ui/badge";
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Button } from "@/components/ui/button";

import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
    type CarouselApi,
} from "@/components/ui/carousel";

const Welcome = () => {
    const { auth } = usePage<SharedData>().props;

    const [api, setApi] = React.useState<CarouselApi>();

    // Tambahkan useEffect untuk mengimplementasikan autoplay dengan loop
    React.useEffect(() => {
        if (!api) {
            return;
        }

        const timer = setInterval(() => {
            // Cek apakah sudah di slide terakhir
            if (api.selectedScrollSnap() === api.scrollSnapList().length - 1) {
                // Jika ya, kembali ke slide pertama
                api.scrollTo(0);
            } else {
                // Jika tidak, lanjutkan ke slide berikutnya
                api.scrollNext();
            }
        }, 3000); // Ganti nilai ini untuk mengatur kecepatan (dalam milidetik)

        // Bersihkan timer saat komponen di-unmount
        return () => clearInterval(timer);
    }, [api]);

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

    // Array path gambar guru untuk carousel
    const guruImages = [
        '/assets/images/foto_guru.jpg',
        '/assets/images/foto_guru_2.jpg',
        '/assets/images/foto_guru_3.jpg',
    ];

    return (
        <>
            <Head title="Sistem Informasi Rapor SDIT Ummatan Wahidah" />

            <div className="relative min-h-screen flex flex-col items-center justify-center opacity-100 transition-opacity duration-750 starting:opacity-0">
                {/* Background Carousel - Menggunakan min-h-screen untuk cover penuh */}
                <div className="absolute inset-0 w-full min-h-screen overflow-hidden z-0">
                    <Carousel className="w-full h-full" setApi={setApi}>
                        <CarouselContent className="w-full h-full">
                            {guruImages.map((imageSrc, index) => (
                                <CarouselItem key={index} className="w-full h-full">
                                    <div className="relative w-full h-full min-h-screen">
                                        <img
                                            src={imageSrc}
                                            alt={`Background ${index + 1}`}
                                            className="w-full h-full min-h-screen object-cover"
                                        />
                                        <div className="absolute inset-0 bg-black opacity-50 md:opacity-60 lg:opacity-70"></div>
                                    </div>
                                </CarouselItem>
                            ))}
                        </CarouselContent>
                        <CarouselPrevious className="absolute left-4 top-1/2 -translate-y-1/2 z-20 text-white border-white hover:bg-white/20" />
                        <CarouselNext className="absolute right-4 top-1/2 -translate-y-1/2 z-20 text-white border-white hover:bg-white/20" />
                    </Carousel>
                </div>

                {/* Content Section - Menggunakan min-h-screen dan padding yang cukup */}
                <section className="relative z-10 w-full min-h-screen flex items-center py-10">
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
                                <Badge className="mb-6 px-4 py-2" variant="outline">
                                    <span className="text-white text-sm md:text-base">Yayasan As-salam Curup</span>
                                </Badge>
                                <h1 className="text-3xl md:text-4xl lg:text-5xl mb-6 font-extrabold uppercase bg-clip-text text-transparent bg-gradient-to-r from-[#1DF01A] from-30% to-[#0E9351] to-70%">
                                    Selamat Datang di Sistem Informasi <br className="hidden" /> Rapor SDIT Ummatan Wahidah
                                </h1>
                                <p className="mb-8 text-center uppercase text-gray-200 dark:text-gray-300 text-sm md:text-base lg:text-lg">
                                    Sistem Informasi Rapor SDIT Ummatan Wahidah adalah <br className="hidden md:block" />
                                    aplikasi berbasis website untuk manajemen nilai sumatif akhir siswa.
                                </p>
                            </div>
                        </div>

                        {/* Buttons Section */}
                        <div className="flex w-full items-center justify-center mt-8 mb-12">
                            <div className="flex justify-center w-full max-w-md">
                                <ul className="flex flex-col sm:flex-row justify-center gap-4 text-sm leading-normal w-full">
                                    {auth.user ? (
                                        <li className="w-full sm:w-auto">
                                            <Link href={route('protected.dashboard.index')} className="w-full block">
                                                <Button className='w-full hover:bg-gray-700 hover:text-white bg-[#773DCE] text-white py-2 px-6'>
                                                    Kembali Ke Dashboard
                                                </Button>
                                            </Link>
                                        </li>
                                    ) : (
                                        <>
                                                <li className="w-full sm:w-auto">
                                                    <Link href={route('login')} className="w-full block">
                                                        <Button className='w-full bg-[#773DCE] text-white hover:bg-[#3D138C] hover:text-white dark:hover:bg-[#3D138C] dark:hover:text-white py-2 px-6'>
                                                        Masuk
                                                    </Button>
                                                </Link>
                                            </li>
                                                <li className="w-full sm:w-auto">
                                                    <Link href={route('register')} className="w-full block">
                                                        <Button className='w-full bg-[#773DCE] text-white hover:bg-[#3D138C] hover:text-white dark:hover:bg-[#3D138C] dark:hover:text-white py-2 px-6'>
                                                        Daftar
                                                    </Button>
                                                </Link>
                                            </li>
                                        </>
                                    )}
                                </ul>
                            </div>
                        </div>

                        {/* Features Section - Diposisikan lebih ke atas */}
                        <div className="mx-auto max-w-6xl flex flex-col md:flex-row text-black dark:text-white gap-6 md:gap-6">
                            {features.map((feature, index) => (
                                <div
                                    key={index}
                                    className="flex flex-col rounded-lg bg-background/80 backdrop-blur-sm p-6 flex-1 shadow-lg border border-white/20"
                                >
                                    <div className="mb-4 flex size-12 items-center justify-center rounded-full bg-white/20 drop-shadow-lg">
                                        {feature.icon}
                                    </div>
                                    <h3 className="mb-3 font-semibold text-lg text-white">{feature.title}</h3>
                                    <p className="text-sm text-gray-200 leading-relaxed">
                                        {feature.description}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
};

export default Welcome;
