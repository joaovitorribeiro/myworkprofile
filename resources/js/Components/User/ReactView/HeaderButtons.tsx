import type { UserPlan } from '@/types';
import { ChangeEvent, useState } from 'react';

interface HeaderButtonsProps {
    plan: UserPlan;
    onLocationClick: () => void;
    onUpgradeClick: () => void;
    onNotificationClick: () => void;
    notificationCount?: number;
    onSearch?: (value: string) => void; // callback opcional
}

export default function HeaderButtons({
    plan,
    onLocationClick,
    onUpgradeClick,
    onNotificationClick,
    notificationCount = 0,
    onSearch,
}: HeaderButtonsProps) {
    const isPremiumUser: boolean = plan === 'premium';
    const [searchValue, setSearchValue] = useState<string>('');

    const baseButton =
        'flex items-center justify-center rounded-full shadow-md active:scale-95 transition-all';
    const textSizes = 'text-xs md:text-sm';
    const spacing = 'px-3 py-2 md:px-4 md:py-2.5';

    const handleSearchChange = (e: ChangeEvent<HTMLInputElement>): void => {
        const value = e.target.value;
        setSearchValue(value);
        onSearch?.(value);
    };

    return (
        <div className="z-10 w-full space-y-3 px-4 py-3">
            {/* Linha dos botões */}
            <div className="mx-auto flex max-w-md items-center justify-between gap-2">
                {/* Botão Localização */}
                <button
                    type="button"
                    aria-label="Selecionar localização"
                    onClick={onLocationClick}
                    className={`${baseButton} flex-1 gap-1 ${spacing} ${textSizes} text-white ${
                        isPremiumUser
                            ? 'bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600'
                            : 'bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600'
                    }`}
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-4 w-4 md:h-5 md:w-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fillRule="evenodd"
                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                            clipRule="evenodd"
                        />
                    </svg>
                    <span>{isPremiumUser ? 'Área' : 'Localização'}</span>
                </button>

                {/* Botão Premium ou Upgrade */}
                {isPremiumUser ? (
                    <div
                        className={`${baseButton} flex-1 gap-1 ${spacing} ${textSizes} bg-gradient-to-r from-purple-600/20 to-pink-500/20 font-bold text-purple-700 backdrop-blur-md`}
                        aria-label="Usuário premium"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-4 w-4 text-purple-600 md:h-5 md:w-5"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fillRule="evenodd"
                                d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clipRule="evenodd"
                            />
                        </svg>
                        <span>Premium</span>
                    </div>
                ) : (
                    <button
                        type="button"
                        aria-label="Atualizar para plano premium"
                        onClick={onUpgradeClick}
                        className={`${baseButton} flex-1 gap-1.5 ${spacing} ${textSizes} bg-gradient-to-r from-amber-500/90 to-amber-400/90 font-bold text-amber-900 hover:shadow-lg`}
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-4 w-4 md:h-5 md:w-5"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path d="M3 6a1 1 0 012 0l1 3 2-4 2 4 1-3a1 1 0 112 0l1 3 2-4 2 4 1-3a1 1 0 112 0l-2 8H5L3 6z" />
                        </svg>
                        <span>Atualizar Plano</span>
                    </button>
                )}

                {/* Botão Notificações */}
                <button
                    type="button"
                    aria-label={`Abrir notificações (${notificationCount} novas)`}
                    onClick={onNotificationClick}
                    className="relative flex h-10 w-10 items-center justify-center rounded-full bg-white/90 shadow-md backdrop-blur-md hover:bg-gray-100 active:scale-95 md:h-11 md:w-11"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-5 w-5 text-gray-700"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                    </svg>
                    {notificationCount > 0 && (
                        <span className="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full border border-white bg-red-500 text-[10px] font-bold text-white md:border-2">
                            {notificationCount > 9 ? '9+' : notificationCount}
                        </span>
                    )}
                </button>
            </div>

            {/* Barra de pesquisa minimalista */}
            <div className="mx-auto max-w-md">
                <div className="flex items-center rounded-full border border-gray-300 bg-white px-3 transition focus-within:border-gray-400">
                    {/* Prefixo # fixo */}
                    <span className="select-none text-gray-400">#</span>

                    {/* Campo de texto */}
                    <input
                        type="text"
                        placeholder="username"
                        value={searchValue}
                        onChange={handleSearchChange}
                        className="ml-1 flex-1 border-none bg-transparent py-1.5 text-sm text-gray-700 placeholder-gray-400 outline-none"
                    />

                    {/* Ícone lupa */}
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-4 w-4 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        strokeWidth={2}
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1110.5 3a7.5 7.5 0 016.15 13.65z"
                        />
                    </svg>
                </div>
            </div>
        </div>
    );
}
