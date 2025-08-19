import type { UserProfileProps } from './types';

export default function UserProfile({
    name,
    age,
    city,
    photo,
}: UserProfileProps) {
    return (
        <div className="z-10 mx-auto flex w-full max-w-md items-center justify-between bg-white/90 px-4 py-3 shadow-sm backdrop-blur-md">
            <div className="flex items-center gap-3">
                <div className="relative h-10 w-10">
                    <img
                        src={photo}
                        alt={name}
                        className="h-full w-full rounded-full object-cover shadow-md"
                    />
                    <div className="absolute -bottom-1 -right-1 h-5 w-5 rounded-full border-2 border-white bg-green-500" />
                </div>
                <div>
                    <h2 className="text-base font-bold text-gray-900">
                        {name}, {age}
                    </h2>
                    <p className="text-xs text-gray-500">{city}</p>
                </div>
            </div>
            <div className="flex flex-wrap items-center gap-2">
                <button className="rounded-full border border-indigo-200 bg-white px-4 py-1.5 text-xs font-semibold text-indigo-600 shadow-sm transition hover:bg-indigo-50 active:scale-95">
                    Perfil
                </button>
                <button className="rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 px-4 py-1.5 text-xs font-semibold text-white shadow-md transition hover:opacity-90 active:scale-95">
                    Seguir
                </button>
            </div>
        </div>
    );
}
