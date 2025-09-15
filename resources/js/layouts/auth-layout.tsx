import * as React from 'react';
import { PropsWithChildren } from 'react';

import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
    type CarouselApi,
} from '@/components/ui/carousel';

const AuthLayout = ({ children }: PropsWithChildren) => {
    const [api, setApi] = React.useState<CarouselApi>();

    const guruImages = [
        '/assets/images/foto_guru.jpg',
        '/assets/images/foto_guru_2.jpg',
        '/assets/images/foto_guru_3.jpg',
    ];

    React.useEffect(() => {
        if (!api) {
            return;
        }

        const timer = setInterval(() => {
            if (api.selectedScrollSnap() === api.scrollSnapList().length - 1) {
                api.scrollTo(0);
            } else {
                api.scrollNext();
            }
        }, 3000);

        return () => clearInterval(timer);
    }, [api]);

    return (
        <section className="bg-muted min-h-screen opacity-100 transition-opacity duration-750 starting:opacity-0 lg:grow">
            <div className="flex h-full items-center justify-center relative">
                <div className="absolute inset-0 w-full h-full overflow-hidden">
                    <Carousel className="w-full h-full" setApi={setApi}>
                        <CarouselContent className="w-[1550px] h-[850px]">
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

                <div className="relative z-10 py-10 flex h-full items-center justify-center w-full">
                    {children}
                </div>
            </div>
        </section>
    );
};

export default AuthLayout;
