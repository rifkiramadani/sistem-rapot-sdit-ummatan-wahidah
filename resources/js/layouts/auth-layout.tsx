import * as React from 'react';
import { PropsWithChildren } from 'react';

// Hapus semua import Shadcn Carousel
// Hapus type CarouselApi

const AuthLayout = ({ children }: PropsWithChildren) => {
    // State untuk mengontrol slide (fade) - SAMA dengan Welcome
    const [currentSlide, setCurrentSlide] = React.useState(0);

    const guruImages = [
        '/assets/images/foto_guru.jpg',
        '/assets/images/foto_guru_2.jpg',
        '/assets/images/foto_guru_3.jpg',
    ];

    // Auto slide effect (fade) yang stabil - SAMA dengan Welcome
    React.useEffect(() => {
        const timer = setInterval(() => {
            setCurrentSlide((prev) =>
                prev === guruImages.length - 1 ? 0 : prev + 1
            );
        }, 2000);

        return () => clearInterval(timer);
    }, [guruImages.length]);

    return (
        // Menggunakan struktur dasar yang sama dengan Welcome
        <div className="relative min-h-screen w-full flex items-center justify-center">

            {/* Background Auto-Sliding (Fade) | Z-index: z-0 */}
            {/* Menggunakan 'fixed' dan dimensi screen - SAMA dengan Welcome */}
            <div className="fixed inset-0 w-screen h-screen z-0 overflow-hidden">
                {/* Slides - SAMA dengan Welcome */}
                {guruImages.map((imageSrc, index) => (
                    <div
                        key={index}
                        className={`absolute inset-0 transition-opacity duration-1000 ${index === currentSlide ? 'opacity-100' : 'opacity-0'
                            }`}
                    >
                        <img
                            src={imageSrc}
                            alt={`Background ${index + 1}`}
                            className="w-full h-full object-cover"
                        />
                        {/* Overlay gelap */}
                        <div className="absolute inset-0 bg-black opacity-50 md:opacity-60 lg:opacity-70"></div>
                    </div>
                ))}
            </div>

            {/* Content Container (Login/Register Form) */}
            {/* Z-index: z-10 memastikan form berada di atas background */}
            {/* Di sini, kita letakkan children (form) di lapisan z-10 */}
            <div className="relative z-10 py-10 flex min-h-screen items-center justify-center w-full">
                {/* Pembungkus semi-transparan untuk keterbacaan (seperti yang disarankan sebelumnya) */}
                <div className='p-8 rounded-lg shadow-2xl'>
                    {children}
                </div>
            </div>

        </div>
    );
};

export default AuthLayout;
