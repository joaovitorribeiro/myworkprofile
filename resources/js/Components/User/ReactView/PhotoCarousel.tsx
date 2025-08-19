import { useState } from 'react';
import type { PhotoCarouselProps } from './types';

export default function PhotoCarousel({
    photos,
    isPremiumUser,
    actionHeight,
}: PhotoCarouselProps) {
    const [photoIndex, setPhotoIndex] = useState<number>(0);
    const [fade, setFade] = useState<boolean>(true);

    const changePhoto = (direction: number) => {
        setFade(false);
        setTimeout(() => {
            setPhotoIndex(
                (prev) => (prev + direction + photos.length) % photos.length,
            );
            setFade(true);
        }, 150);
    };

    const headerHeight = 150; // altura aproximada do FooterButtons + UserProfile

    return (
        <div
            className="relative mx-auto mt-3 flex w-[94%] max-w-md items-center justify-center overflow-hidden rounded-2xl shadow-2xl"
            style={{
                height: `calc(90vh - ${headerHeight + actionHeight + 120}px)`,
                minHeight: '300px',
                marginBottom: `${actionHeight + 12}px`,
            }}
        >
            <img
                src={photos[photoIndex]}
                alt="Foto do usuário"
                className={`absolute inset-0 h-full w-full object-cover transition-opacity duration-300 ${
                    fade ? 'opacity-100' : 'opacity-0'
                }`}
                draggable={false}
            />

            <div className="absolute inset-0 rounded-2xl bg-gradient-to-t from-black/70 via-black/30 to-transparent" />

            <button
                onClick={() => changePhoto(-1)}
                className="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-black/40 p-2.5 text-2xl text-white backdrop-blur-md transition hover:scale-110 active:scale-90"
            >
                ←
            </button>
            <button
                onClick={() => changePhoto(1)}
                className="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-black/40 p-2.5 text-2xl text-white backdrop-blur-md transition hover:scale-110 active:scale-90"
            >
                →
            </button>

            <div className="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
                {photos.map((_, index) => (
                    <div
                        key={index}
                        className={`h-2 w-2 rounded-full transition-all ${
                            index === photoIndex
                                ? 'w-6 bg-white'
                                : 'bg-white/40'
                        }`}
                    />
                ))}
            </div>

            {!isPremiumUser && (
                <div className="absolute left-3 top-3 rounded-full bg-gradient-to-r from-amber-400 to-amber-500 px-2 py-1 text-xs font-bold text-amber-900 shadow-md">
                    PLANO GRATUITO
                </div>
            )}
        </div>
    );
}
