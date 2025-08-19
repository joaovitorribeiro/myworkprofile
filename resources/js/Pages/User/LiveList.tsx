import ChatPanel from '@/Components/User/LiveList/ChatPanel';
import FiltersPanel from '@/Components/User/LiveList/FiltersPanel';
import Header from '@/Components/User/LiveList/Header';
import MainVideoArea from '@/Components/User/LiveList/MainVideoArea';
import type {
    ChatMessage,
    LiveListPageProps,
    LiveUser,
} from '@/Components/User/LiveList/types';
import { Head } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';

// Simulação de dados - em produção buscar via API
const availableUsers: LiveUser[] = [
    {
        id: 1,
        name: 'Ana',
        age: 24,
        gender: 'female',
        interests: ['Música', 'Viagens'],
        location: 'São Paulo, BR',
        thumbnail: 'https://randomuser.me/api/portraits/women/44.jpg',
        isOnline: true,
    },
    {
        id: 2,
        name: 'Carlos',
        age: 31,
        gender: 'male',
        interests: ['Tecnologia', 'Esportes'],
        location: 'Lisboa, PT',
        thumbnail: 'https://randomuser.me/api/portraits/men/32.jpg',
        isOnline: true,
    },
    {
        id: 3,
        name: 'Julia',
        age: 19,
        gender: 'female',
        interests: ['Arte', 'Moda'],
        location: 'Barcelona, ES',
        thumbnail: 'https://randomuser.me/api/portraits/women/68.jpg',
        isOnline: true,
    },
    {
        id: 4,
        name: 'Miguel',
        age: 27,
        gender: 'male',
        interests: ['Games', 'Cinema'],
        location: 'Cidade do México, MX',
        thumbnail: 'https://randomuser.me/api/portraits/men/75.jpg',
        isOnline: true,
    },
];

const interestsList = [
    ...new Set(availableUsers.flatMap((user) => user.interests)),
];
const locationsList = [...new Set(availableUsers.map((user) => user.location))];

export default function LiveRandomInstantPage({
    userRole,
    userPlan,
}: LiveListPageProps) {
    const [currentLive, setCurrentLive] = useState<LiveUser | null>(null);
    const [isAudioMuted, setIsAudioMuted] = useState(false);
    const [isMicMuted, setIsMicMuted] = useState(false);
    const [isVideoOff, setIsVideoOff] = useState(false);
    const [loading, setLoading] = useState(true);
    const [blockedUsers, setBlockedUsers] = useState<number[]>([]);
    const [lovedUsers, setLovedUsers] = useState<number[]>([]);
    const [isSearching, setIsSearching] = useState(true);
    const [filterGender, setFilterGender] = useState('all');
    const [filterInterests, setFilterInterests] = useState<string[]>([]);
    const [filterLocation, setFilterLocation] = useState('');
    const [messages, setMessages] = useState<ChatMessage[]>([]);
    const [newMessage, setNewMessage] = useState('');
    const [showFilters, setShowFilters] = useState(false);
    const [showChat, setShowChat] = useState(false);
    const [menuHeight, setMenuHeight] = useState(0);
    const [pipBottom, setPipBottom] = useState(0);

    const chatContainerRef = useRef<HTMLDivElement>(null);

    // Ajusta isSearching de acordo com a role
    useEffect(() => {
        if (userRole !== 'user') {
            setIsSearching(false);
        }
    }, [userRole]);

    // Log para plano free
    useEffect(() => {
        if (userPlan === 'free') {
            console.log('Usuário no plano gratuito...');
        }
    }, [userPlan]);

    // Ajusta posição do PiP
    useEffect(() => {
        const menu = document.querySelector('nav');
        const floating = document.querySelector('#floating-controls');
        const mh = menu ? (menu as HTMLElement).offsetHeight : 0;
        const fh = floating ? (floating as HTMLElement).offsetHeight : 0;
        setPipBottom(window.innerWidth >= 1024 ? fh + 15 : mh + fh + 40);
    }, []);

    // Atualiza altura do menu ao redimensionar
    useEffect(() => {
        const updateMenuHeight = () => {
            const menu = document.querySelector('nav');
            setMenuHeight(menu ? (menu as HTMLElement).offsetHeight : 0);
        };
        updateMenuHeight();
        window.addEventListener('resize', updateMenuHeight);
        return () => window.removeEventListener('resize', updateMenuHeight);
    }, []);

    // Scroll automático no chat
    useEffect(() => {
        if (chatContainerRef.current) {
            chatContainerRef.current.scrollTop =
                chatContainerRef.current.scrollHeight;
        }
    }, [messages]);

    // Função de busca de usuário otimizada com useCallback
    const findRandomUser = useCallback(() => {
        if (!isSearching) return;
        setLoading(true);
        setMessages([]);
        const available = availableUsers.filter(
            (user) =>
                !blockedUsers.includes(user.id) &&
                user.isOnline &&
                (filterGender === 'all' || user.gender === filterGender) &&
                (filterInterests.length === 0 ||
                    user.interests.some((i) => filterInterests.includes(i))) &&
                (filterLocation === '' ||
                    user.location.includes(filterLocation)),
        );
        setTimeout(() => {
            if (available.length === 0) {
                setCurrentLive(null);
            } else {
                const randomUser =
                    available[Math.floor(Math.random() * available.length)];
                setCurrentLive(randomUser);
                setTimeout(() => {
                    setMessages([
                        { text: `Oi, eu sou ${randomUser.name}!`, isMe: false },
                        { text: 'Como você está?', isMe: false },
                    ]);
                }, 2000);
            }
            setLoading(false);
        }, 1000);
    }, [
        isSearching,
        blockedUsers,
        filterGender,
        filterInterests,
        filterLocation,
    ]);

    // Busca inicial e ao mudar filtros
    useEffect(() => {
        findRandomUser();
    }, [findRandomUser]);

    const skipUser = () => findRandomUser();

    const blockUser = () => {
        if (currentLive) {
            setBlockedUsers((prev) => [...prev, currentLive.id]);
            findRandomUser();
        }
    };

    const loveUser = () => {
        if (currentLive && !lovedUsers.includes(currentLive.id)) {
            setLovedUsers((prev) => [...prev, currentLive.id]);
        }
        skipUser();
    };

    const followUser = () => skipUser();

    const sendMessage = () => {
        if (!newMessage.trim()) return;
        setMessages((prev) => [...prev, { text: newMessage, isMe: true }]);
        setNewMessage('');
        setTimeout(() => {
            setMessages((prev) => [
                ...prev,
                { text: 'Resposta automática!', isMe: false },
            ]);
        }, 1000);
    };

    if (userRole !== 'user') {
        return (
            <div className="flex h-screen items-center justify-center text-red-600">
                Acesso não permitido.
            </div>
        );
    }

    return (
        <>
            <Head title="Conexão Aleatória | LoveBlock" />
            <div className="relative flex min-h-screen flex-col bg-gray-900 pb-20 text-white">
                <div className="relative">
                    <Header
                        isSearching={isSearching}
                        setIsSearching={setIsSearching}
                        setShowFilters={setShowFilters}
                        showFilters={showFilters}
                    />
                    {showFilters && (
                        <div className="absolute left-0 right-0 top-full z-20">
                            <FiltersPanel
                                filterLocation={filterLocation}
                                setFilterLocation={setFilterLocation}
                                locationsList={locationsList}
                                filterGender={filterGender}
                                setFilterGender={setFilterGender}
                                interestsList={interestsList}
                                filterInterests={filterInterests}
                                setFilterInterests={setFilterInterests}
                            />
                        </div>
                    )}
                </div>

                <MainVideoArea
                    currentLive={currentLive}
                    isSearching={isSearching}
                    loading={loading}
                    showChat={showChat}
                    setShowChat={setShowChat}
                    isVideoOff={isVideoOff}
                    isMicMuted={isMicMuted}
                    setIsMicMuted={setIsMicMuted}
                    setIsVideoOff={setIsVideoOff}
                    isAudioMuted={isAudioMuted}
                    setIsAudioMuted={setIsAudioMuted}
                    blockUser={blockUser}
                    skipUser={skipUser}
                    followUser={followUser}
                    loveUser={loveUser}
                    lovedUsers={lovedUsers}
                    menuHeight={menuHeight}
                    pipBottom={pipBottom}
                />

                {currentLive && isSearching && !loading && showChat && (
                    <ChatPanel
                        messages={messages}
                        newMessage={newMessage}
                        setNewMessage={setNewMessage}
                        sendMessage={sendMessage}
                        chatContainerRef={chatContainerRef}
                        setShowChat={setShowChat}
                    />
                )}
            </div>
        </>
    );
}
