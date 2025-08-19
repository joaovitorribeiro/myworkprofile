import { LiveUser } from '@/Components/User/LiveList/types';
import clsx from 'clsx';
import {
    FiMapPin,
    FiMessageSquare,
    FiMic,
    FiMicOff,
    FiPause,
    FiUser,
    FiVideo,
    FiVideoOff,
} from 'react-icons/fi';
import FooterButtons from './FooterButtons';
import LocalVideoPIP from './LocalVideoPIP';

interface MainVideoAreaProps {
    currentLive: LiveUser | null;
    isSearching: boolean;
    loading: boolean;
    showChat: boolean;
    setShowChat: (value: boolean) => void;
    isVideoOff: boolean;
    isMicMuted: boolean;
    setIsMicMuted: (value: boolean) => void;
    setIsVideoOff: (value: boolean) => void;
    isAudioMuted: boolean;
    setIsAudioMuted: (value: boolean) => void;
    blockUser: () => void;
    skipUser: () => void;
    followUser: () => void;
    loveUser: () => void;
    lovedUsers: number[];
    menuHeight: number;
    pipBottom: number;
}

export default function MainVideoArea({
    currentLive,
    isSearching,
    loading,
    showChat,
    setShowChat,
    isVideoOff,
    isMicMuted,
    setIsMicMuted,
    setIsVideoOff,
    isAudioMuted,
    setIsAudioMuted,
    blockUser,
    skipUser,
    followUser,
    loveUser,
    lovedUsers,
    menuHeight,
}: MainVideoAreaProps) {
    const isNotSearching = !isSearching;
    const isConnectionPaused = isNotSearching && !loading;

    return (
        <div className="relative flex-1 overflow-hidden bg-black">
            {currentLive && isSearching && !loading ? (
                <div className="absolute inset-0 flex items-center justify-center bg-gray-900">
                    <div className="relative h-full w-full">
                        {/* Barra superior com nome e localização */}
                        <div className="absolute left-4 right-4 top-4 z-10 flex items-center justify-between rounded-lg bg-black/50 px-3 py-2 text-sm text-white">
                            <div className="flex flex-wrap items-center gap-2">
                                <div className="flex items-center gap-2">
                                    <FiUser className="flex-shrink-0" />
                                    <span className="font-medium">
                                        {currentLive.name}, {currentLive.age}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <FiMapPin className="flex-shrink-0" />
                                    <span>{currentLive.location}</span>
                                </div>
                            </div>

                            {/* Botão de chat + botões mic/cam + PIP */}
                            <div className="flex items-center gap-3">
                                {!showChat && (
                                    <button
                                        type="button"
                                        onClick={() => setShowChat(true)}
                                        className="rounded-full bg-gray-800 p-3 shadow-lg hover:bg-gray-700"
                                        aria-label="Abrir chat"
                                    >
                                        <FiMessageSquare size={20} />
                                    </button>
                                )}

                                {/* Botão microfone */}
                                <button
                                    type="button"
                                    onClick={() => setIsMicMuted(!isMicMuted)}
                                    className={clsx(
                                        'rounded-full p-3 text-white shadow-lg transition-colors',
                                        {
                                            'bg-red-500': isMicMuted,
                                            'bg-gray-700 hover:bg-gray-600':
                                                !isMicMuted,
                                        },
                                    )}
                                >
                                    {isMicMuted ? (
                                        <FiMicOff size={20} />
                                    ) : (
                                        <FiMic size={20} />
                                    )}
                                </button>

                                {/* Botão câmera */}
                                <button
                                    type="button"
                                    onClick={() => setIsVideoOff(!isVideoOff)}
                                    className={clsx(
                                        'rounded-full p-3 text-white shadow-lg transition-colors',
                                        {
                                            'bg-red-500': isVideoOff,
                                            'bg-gray-700 hover:bg-gray-600':
                                                !isVideoOff,
                                        },
                                    )}
                                >
                                    {isVideoOff ? (
                                        <FiVideoOff size={20} />
                                    ) : (
                                        <FiVideo size={20} />
                                    )}
                                </button>

                                {/* PIP do vídeo local */}
                                {!showChat && (
                                    <LocalVideoPIP
                                        position="top"
                                        isVideoOff={isVideoOff}
                                        menuHeight={menuHeight}
                                    />
                                )}
                            </div>
                        </div>

                        {/* Conteúdo central */}
                        <div className="absolute inset-0 flex items-center justify-center">
                            <div className="text-center">
                                <img
                                    src={currentLive.thumbnail}
                                    alt={`${currentLive.name}'s thumbnail`}
                                    className="mx-auto mb-4 h-24 w-24 rounded-full object-cover"
                                />
                                <p className="text-lg">
                                    Conectado com {currentLive.name}
                                </p>
                                <div className="mx-auto mt-2 flex max-w-md flex-wrap justify-center gap-2">
                                    {currentLive.interests.map((interest) => (
                                        <span
                                            key={interest}
                                            className="rounded-full bg-gray-800 px-2 py-1 text-xs"
                                        >
                                            {interest}
                                        </span>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            ) : isConnectionPaused ? (
                /* Estado: Conexão pausada */
                <div className="absolute inset-0 flex items-center justify-center">
                    <div className="max-w-md p-6 text-center">
                        <FiPause
                            size={48}
                            className="mx-auto mb-4 opacity-50"
                        />
                        <h3 className="mb-2 text-xl font-bold">
                            Conexão pausada
                        </h3>
                        <p className="text-gray-400">
                            Clique em "Conectar" para iniciar uma nova conversa
                        </p>
                    </div>
                </div>
            ) : loading ? (
                /* Estado: Procurando */
                <div className="absolute inset-0 flex items-center justify-center">
                    <div className="text-center">
                        <div className="mx-auto mb-4 h-16 w-16 animate-spin rounded-full border-4 border-purple-500 border-t-transparent"></div>
                        <p>Procurando conexão...</p>
                    </div>
                </div>
            ) : (
                /* Estado: Nenhum usuário encontrado */
                <div className="absolute inset-0 flex items-center justify-center">
                    <div className="max-w-md p-6 text-center">
                        <h3 className="mb-2 text-xl font-bold">
                            Nenhum usuário encontrado
                        </h3>
                        <p className="mb-4 text-gray-400">
                            Tente ajustar seus filtros de busca
                        </p>
                    </div>
                </div>
            )}

            {/* Botões de ação no rodapé */}
            {currentLive && isSearching && !loading && (
                <FooterButtons
                    isAudioMuted={isAudioMuted}
                    setIsAudioMuted={setIsAudioMuted}
                    blockUser={blockUser}
                    skipUser={skipUser}
                    followUser={followUser}
                    loveUser={loveUser}
                    lovedUsers={lovedUsers}
                    currentLiveId={currentLive.id}
                    bottom={menuHeight + 10}
                />
            )}
        </div>
    );
}
