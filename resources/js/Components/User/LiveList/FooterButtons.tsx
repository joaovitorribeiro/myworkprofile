// resources/js/Components/User/LiveList/FooterButtons.tsx
import {
    FiHeart,
    FiSkipForward,
    FiUserPlus,
    FiVolume2,
    FiVolumeX,
    FiX,
} from 'react-icons/fi';

interface FooterButtonsProps {
    isAudioMuted: boolean;
    setIsAudioMuted: (value: boolean) => void;
    blockUser: () => void;
    skipUser: () => void;
    followUser: () => void;
    loveUser: () => void;
    lovedUsers: number[];
    currentLiveId: number;
    bottom: number;
}

export default function FooterButtons({
    isAudioMuted,
    setIsAudioMuted,
    blockUser,
    skipUser,
    followUser,
    loveUser,
    lovedUsers,
    currentLiveId,
    bottom,
}: FooterButtonsProps) {
    return (
        <div
            className="absolute left-1/2 z-10 flex -translate-x-1/2 transform gap-3"
            style={{ bottom: `${bottom}px` }}
        >
            <button
                type="button"
                onClick={blockUser}
                className="flex flex-col items-center rounded-full bg-red-600 p-3 shadow-lg transition-transform hover:scale-110 hover:bg-red-700"
                title="Bloquear usuário"
                aria-label="Bloquear usuário"
            >
                <FiX size={20} />
                <span className="mt-1 text-xs">Bloquear</span>
            </button>

            <button
                type="button"
                onClick={skipUser}
                className="flex flex-col items-center rounded-full bg-gray-700 p-3 shadow-lg transition-transform hover:scale-110 hover:bg-gray-600"
                title="Pular usuário"
                aria-label="Pular usuário"
            >
                <FiSkipForward size={20} />
                <span className="mt-1 text-xs">Pular</span>
            </button>

            <button
                type="button"
                onClick={() => setIsAudioMuted(!isAudioMuted)}
                className={`flex flex-col items-center rounded-full p-3 shadow-lg transition-transform hover:scale-110 ${
                    isAudioMuted
                        ? 'bg-red-500'
                        : 'bg-gray-700 hover:bg-gray-600'
                }`}
                title={isAudioMuted ? 'Ativar áudio' : 'Desativar áudio'}
                aria-label={isAudioMuted ? 'Ativar áudio' : 'Desativar áudio'}
            >
                {isAudioMuted ? (
                    <FiVolumeX size={20} />
                ) : (
                    <FiVolume2 size={20} />
                )}
                <span className="mt-1 text-xs">
                    {isAudioMuted ? 'Som' : 'Mutar'}
                </span>
            </button>

            <button
                type="button"
                onClick={followUser}
                className="flex flex-col items-center rounded-full bg-blue-600 p-3 shadow-lg transition-transform hover:scale-110 hover:bg-blue-700"
                title="Seguir usuário"
                aria-label="Seguir usuário"
            >
                <FiUserPlus size={20} />
                <span className="mt-1 text-xs">Seguir</span>
            </button>

            <button
                type="button"
                onClick={loveUser}
                className={`flex flex-col items-center rounded-full p-3 shadow-lg transition-transform hover:scale-110 ${
                    lovedUsers.includes(currentLiveId)
                        ? 'bg-red-500'
                        : 'bg-gray-700 hover:bg-gray-600'
                }`}
                title="Adicionar"
                aria-label="Adicionar"
            >
                <FiHeart size={20} />
                <span className="mt-1 text-xs">Adicionar</span>
            </button>
        </div>
    );
}
