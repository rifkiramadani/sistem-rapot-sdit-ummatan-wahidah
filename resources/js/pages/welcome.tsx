import * as React from "react";
import { HandHelping, Users, Zap } from "lucide-react";

import { Badge } from "@/components/ui/badge";
import { Separator } from "@/components/ui/separator";
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

            <div className="relative min-h-screen flex flex-col items-center justify-center opacity-100 transition-opacity duration-750 starting:opacity-0 lg:grow">
                <div className="absolute inset-0 w-full h-full overflow-hidden">
                    <Carousel className="w-full h-full" setApi={setApi}>
                        <CarouselContent className="w-[1600px] h-[1000px]">
                            {guruImages.map((imageSrc, index) => (
                                <CarouselItem key={index} className="w-full h-full">
                                    <div
                                        className="relative w-full h-full bg-cover bg-center"
                                        style={{ backgroundImage: `url(${imageSrc})` }}
                                    >
                                        <div className="absolute inset-0 bg-black opacity-50 md:opacity-60 lg:opacity-70"></div>
                                    </div>
                                </CarouselItem>
                            ))}
                        </CarouselContent>
                        <CarouselPrevious className="absolute left-4 top-1/2 -translate-y-1/2 z-20" />
                        <CarouselNext className="absolute right-4 top-1/2 -translate-y-1/2 z-20" />
                    </Carousel>
                </div>

                <section className="relative z-10 py-10 text-white dark:text-white">
                    <div className="container overflow-hidden">
                        <div className="flex flex-col items-center gap-6 text-center">
                            <div className='flex flex-col md:flex-row justify-center items-center gap-5 mb-5'>
                                <img
                                    src="/assets/logo/sdit_ummatan_wahidah_logo.png"
                                    alt="Logo SDIT Ummatan Wahidah"
                                    className="w-45 h-45 object-contain"
                                />
                                <img
                                    src="/assets/logo/yayasan_assalam_logo.png"
                                    alt="Logo Yayasan Assalam"
                                    className="w-40 h-40 object-contain mt-3"
                                />
                            </div>
                            <div>
                                <Badge className="mb-3" variant="outline">Yayasan As-salam Curup</Badge>
                                <h1 className="text-5xl mb-3 font-extrabold uppercase bg-clip-text text-transparent bg-gradient-to-r from-[#1DF01A] from-30% to-[#0E9351] to-70%">
                                    Selamat Datang di Sistem Informasi <br /> Rapor SDIT Ummatan Wahidah
                                </h1>
                                <p className="mb-5 text-center uppercase text-gray-200 dark:text-gray-300">
                                    Sistem Informasi rapor SDIT Ummatan Wahidah adalah <br />
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
                                        </>
                                    )}
                                </ul>
                            </main>
                        </div>

                        <div className="mx-auto mt-10 flex max-w-5xl flex-col md:flex-row text-white dark:text-white">
                            {features.map((feature, index) => (
                                <React.Fragment key={feature.title}>
                                    {index > 0 && (
                                        <Separator
                                            orientation="vertical"
                                            className="mx-6 hidden h-auto w-[2px] bg-linear-to-b from-muted via-transparent to-muted md:block"
                                        />
                                    )}
                                    <div
                                        key={index}
                                        className="flex grow basis-0 flex-col rounded-md bg-background/50 p-4"
                                    >
                                        <div className="mb-6 flex size-10 items-center justify-center rounded-full bg-background/50 drop-shadow-lg">
                                            {feature.icon}
                                        </div>
                                        <h3 className="mb-2 font-semibold">{feature.title}</h3>
                                        <p className="text-sm text-gray-200 dark:text-gray-300">
                                            {feature.description}
                                        </p>
                                    </div>
                                </React.Fragment>
                            ))}
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
};

export default Welcome;
