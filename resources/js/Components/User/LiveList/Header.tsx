// resources/js/Components/User/LiveList/Header.tsx
import { FiPause, FiSliders, FiUser } from 'react-icons/fi';
import type { HeaderProps } from './types';

export default function Header({
    isSearching,
    setIsSearching,
    showFilters,
    setShowFilters,
}: HeaderProps) {
    return (
        <div className="sticky top-0 z-20 flex items-center justify-between border-b border-gray-700 bg-gray-800/95 px-4 py-3 backdrop-blur-sm">
            <button
                onClick={() => setIsSearching(!isSearching)}
                className={`flex items-center gap-2 rounded-full px-4 py-2 text-sm ${
                    isSearching
                        ? 'bg-red-600 hover:bg-red-700'
                        : 'bg-green-600 hover:bg-green-700'
                } transition-colors`}
            >
                {isSearching ? (
                    <>
                        <FiPause size={16} /> Parar
                    </>
                ) : (
                    <>
                        <FiUser size={16} /> Conectar
                    </>
                )}
            </button>
            <h1 className="text-lg font-bold">Conexão Aleatória</h1>
            <button
                onClick={() => setShowFilters(!showFilters)}
                className="rounded-full bg-gray-700 p-2 transition-colors hover:bg-gray-600"
            >
                <FiSliders size={18} />
            </button>
        </div>
    );
}
