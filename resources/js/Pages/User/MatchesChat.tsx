// resources/js/Pages/User/MatchesChat.tsx
import type { UserPlan, UserRole } from '@/types';
import { Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';

interface Props {
    userRole: UserRole;
    userPlan: UserPlan;
}

interface Story {
    id: number;
    user: string;
    avatar: string;
    hasUnseen: boolean;
}

interface Contact {
    id: number;
    name: string;
    avatar: string;
    lastMessage: string;
    time: string;
    unread: number;
    isOnline: boolean;
    isBlocked?: boolean;
}

interface Message {
    id: number;
    text: string;
    time: string;
    isOwn: boolean;
}

export default function MatchesChat({ userRole, userPlan }: Props) {
    const [activeTab, setActiveTab] = useState<'connections' | 'blocked'>(
        'connections',
    );
    const [selectedContact, setSelectedContact] = useState<Contact | null>(
        null,
    );
    const [allMessages, setAllMessages] = useState<{
        [key: number]: Message[];
    }>({});
    const [newMessage, setNewMessage] = useState('');
    const [windowWidth, setWindowWidth] = useState<number>(window.innerWidth);
    const isMobile = windowWidth < 768;

    useEffect(() => {
        const handleResize = () => {
            setWindowWidth(window.innerWidth);
        };

        // Definir valor inicial
        setWindowWidth(window.innerWidth);

        // Adicionar listener
        window.addEventListener('resize', handleResize);

        // Remover listener no desmontar
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    // Dados de exemplo - Stories
    const stories: Story[] = [
        {
            id: 1,
            user: 'Seu Story',
            avatar: 'https://randomuser.me/api/portraits/men/1.jpg',
            hasUnseen: false,
        },
        {
            id: 2,
            user: 'Maria',
            avatar: 'https://randomuser.me/api/portraits/women/44.jpg',
            hasUnseen: true,
        },
        {
            id: 3,
            user: 'João',
            avatar: 'https://randomuser.me/api/portraits/men/36.jpg',
            hasUnseen: false,
        },
        {
            id: 4,
            user: 'Ana',
            avatar: 'https://randomuser.me/api/portraits/women/68.jpg',
            hasUnseen: true,
        },
        {
            id: 5,
            user: 'Lucas',
            avatar: 'https://randomuser.me/api/portraits/men/14.jpg',
            hasUnseen: false,
        },
        {
            id: 6,
            user: 'Fernanda',
            avatar: 'https://randomuser.me/api/portraits/women/24.jpg',
            hasUnseen: true,
        },
        {
            id: 7,
            user: 'Carlos',
            avatar: 'https://randomuser.me/api/portraits/men/47.jpg',
            hasUnseen: false,
        },
        {
            id: 8,
            user: 'Patrícia',
            avatar: 'https://randomuser.me/api/portraits/women/12.jpg',
            hasUnseen: true,
        },
    ];

    // Lista de contatos de exemplo
    const contacts: Contact[] = [
        {
            id: 1,
            name: 'Maria Silva',
            avatar: 'https://randomuser.me/api/portraits/women/44.jpg',
            lastMessage: 'Oi, tudo bem?',
            time: '10:30',
            unread: 2,
            isOnline: true,
        },
        {
            id: 2,
            name: 'João Santos',
            avatar: 'https://randomuser.me/api/portraits/men/36.jpg',
            lastMessage: 'Vamos marcar aquele encontro?',
            time: '09:15',
            unread: 0,
            isOnline: false,
        },
        {
            id: 3,
            name: 'Fernanda Rocha',
            avatar: 'https://randomuser.me/api/portraits/women/24.jpg',
            lastMessage: 'Enviei as fotos!',
            time: 'Ontem',
            unread: 1,
            isOnline: true,
        },
        {
            id: 4,
            name: 'Pedro Almeida',
            avatar: 'https://randomuser.me/api/portraits/men/22.jpg',
            lastMessage: 'Obrigado pela indicação!',
            time: 'Qua',
            unread: 0,
            isOnline: false,
            isBlocked: true,
        },
        {
            id: 5,
            name: 'Lucas Pereira',
            avatar: 'https://randomuser.me/api/portraits/men/14.jpg',
            lastMessage: 'Gostei muito de te conhecer.',
            time: 'Ter',
            unread: 4,
            isOnline: true,
        },
        {
            id: 6,
            name: 'Patrícia Souza',
            avatar: 'https://randomuser.me/api/portraits/women/12.jpg',
            lastMessage: 'Vamos combinar algo?',
            time: 'Seg',
            unread: 0,
            isOnline: false,
        },
        {
            id: 7,
            name: 'Carlos Mendes',
            avatar: 'https://randomuser.me/api/portraits/men/47.jpg',
            lastMessage: 'Boa noite! 🌙',
            time: 'Dom',
            unread: 0,
            isOnline: false,
        },
        {
            id: 8,
            name: 'Beatriz Oliveira',
            avatar: 'https://randomuser.me/api/portraits/women/76.jpg',
            lastMessage: 'Parabéns pelo seu dia 🎉',
            time: 'Sáb',
            unread: 3,
            isOnline: true,
        },
        {
            id: 9,
            name: 'Ricardo Martins',
            avatar: 'https://randomuser.me/api/portraits/men/52.jpg',
            lastMessage: 'Vamos fazer aquela viagem!',
            time: 'Sex',
            unread: 0,
            isOnline: false,
        },
        {
            id: 10,
            name: 'Juliana Costa',
            avatar: 'https://randomuser.me/api/portraits/women/19.jpg',
            lastMessage: 'Amei nosso último encontro ❤️',
            time: 'Qui',
            unread: 5,
            isOnline: true,
        },
    ];

    const filteredContacts =
        activeTab === 'connections'
            ? contacts.filter((contact) => !contact.isBlocked)
            : contacts.filter((contact) => contact.isBlocked);

    const messages = selectedContact
        ? allMessages[selectedContact.id] || []
        : [];

    const handleSelectContact = (contact: Contact) => {
        setSelectedContact(contact);
    };

    const handleSendMessage = () => {
        if (newMessage.trim() && selectedContact) {
            const newMsg: Message = {
                id: Date.now(),
                text: newMessage,
                time: new Date().toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                }),
                isOwn: true,
            };

            setAllMessages((prev) => ({
                ...prev,
                [selectedContact.id]: [
                    ...(prev[selectedContact.id] || []),
                    newMsg,
                ],
            }));
            setNewMessage('');
        }
    };

    const handleBackToContacts = () => {
        setSelectedContact(null);
    };

    const shouldShowContactsPanel = isMobile ? !selectedContact : true;
    const shouldShowChatPanel = isMobile ? !!selectedContact : true;

    return (
        <>
            <Head title="Suas Conversas | LoveBlock" />
            <div
                className="flex flex-col bg-gradient-to-br from-pink-50 to-purple-50"
                style={{ height: 'calc(100dvh - 90px)' }} // <-- Usando dvh
            >
                {/* Cabeçalho */}
                <div className="bg-gradient-to-r from-pink-500 to-purple-600 p-4 text-white shadow-lg">
                    <div className="flex items-center justify-between">
                        <h1 className="text-xl font-bold">Conversas</h1>
                        <div className="flex items-center space-x-2">
                            {userPlan === 'free' ? (
                                <button className="rounded-full bg-white px-3 py-1 text-xs font-bold text-pink-600 transition hover:bg-gray-100">
                                    💎 Upgrade
                                </button>
                            ) : (
                                <span className="rounded-full bg-yellow-400 px-2 py-1 text-xs font-bold text-yellow-900">
                                    Premium
                                </span>
                            )}
                        </div>
                    </div>
                </div>

                {/* Conteúdo principal */}
                <div className="flex flex-1 overflow-hidden">
                    {/* Painel de Contatos */}
                    {shouldShowContactsPanel && (
                        <div
                            className={`${!isMobile ? 'w-1/3 border-r border-gray-200' : 'w-full'} flex flex-col`}
                        >
                            {/* Stories */}
                            <div className="border-b border-gray-100 bg-white p-4">
                                <div className="hide-scrollbar flex space-x-4 overflow-x-auto pb-2">
                                    <div
                                        className="flex"
                                        style={{ minWidth: 'max-content' }}
                                    >
                                        {stories.map((story) => (
                                            <div
                                                key={story.id}
                                                className="flex flex-shrink-0 flex-col items-center px-1"
                                                style={{ width: '72px' }}
                                            >
                                                <div
                                                    className={`relative mt-2 ${story.hasUnseen ? 'rounded-full p-0.5 ring-2 ring-pink-500' : ''}`}
                                                >
                                                    <img
                                                        src={story.avatar}
                                                        alt={story.user}
                                                        className="h-16 w-16 rounded-full border-2 border-white object-cover shadow"
                                                    />
                                                    {story.id === 1 && (
                                                        <div className="absolute bottom-0 right-0 rounded-full border-2 border-white bg-pink-500 p-1">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                className="h-4 w-4 text-white"
                                                                viewBox="0 0 20 20"
                                                                fill="currentColor"
                                                            >
                                                                <path
                                                                    fillRule="evenodd"
                                                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                                                    clipRule="evenodd"
                                                                />
                                                            </svg>
                                                        </div>
                                                    )}
                                                </div>
                                                <p className="mt-1 w-16 truncate text-center text-xs text-gray-600">
                                                    {story.user.length > 8
                                                        ? `${story.user.substring(0, 8)}...`
                                                        : story.user}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            {/* Abas */}
                            <div className="flex bg-white">
                                <button
                                    className={`flex-1 py-3 text-center text-sm font-medium ${activeTab === 'connections' ? 'border-b-2 border-pink-600 text-pink-600' : 'text-gray-500'}`}
                                    onClick={() => setActiveTab('connections')}
                                >
                                    Conexões
                                </button>
                                <button
                                    className={`flex-1 py-3 text-center text-sm font-medium ${activeTab === 'blocked' ? 'border-b-2 border-pink-600 text-pink-600' : 'text-gray-500'}`}
                                    onClick={() => setActiveTab('blocked')}
                                >
                                    Bloqueados
                                </button>
                            </div>

                            {/* Lista de Contatos */}
                            <div
                                className="hide-scrollbar flex-1 overflow-y-auto bg-white"
                                style={{ height: 'calc(100vh - 220px)' }}
                            >
                                {filteredContacts.length === 0 ? (
                                    <div className="flex h-full flex-col items-center justify-center p-8 text-center">
                                        <div className="mb-4 h-16 w-16 rounded-full border-2 border-dashed bg-gray-200" />
                                        <h3 className="text-lg font-medium text-gray-900">
                                            {activeTab === 'connections'
                                                ? 'Nenhuma conversa ainda'
                                                : 'Nenhum contato bloqueado'}
                                        </h3>
                                        <p className="mt-1 text-sm text-gray-500">
                                            {activeTab === 'connections'
                                                ? 'Suas conversas com matches aparecerão aqui'
                                                : 'Contatos que você bloqueou aparecerão nesta lista'}
                                        </p>
                                    </div>
                                ) : (
                                    <ul className="divide-y divide-gray-100">
                                        {filteredContacts.map((contact) => (
                                            <li
                                                key={contact.id}
                                                className="cursor-pointer p-4 transition-colors hover:bg-pink-50"
                                                onClick={() =>
                                                    handleSelectContact(contact)
                                                }
                                            >
                                                <div className="flex items-center">
                                                    <div className="relative flex-shrink-0">
                                                        <img
                                                            src={contact.avatar}
                                                            alt={contact.name}
                                                            className="h-12 w-12 rounded-full object-cover"
                                                        />
                                                        {contact.isOnline && (
                                                            <div className="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-green-500"></div>
                                                        )}
                                                    </div>
                                                    <div className="ml-3 min-w-0 flex-1">
                                                        <div className="flex items-center justify-between">
                                                            <p className="truncate text-sm font-medium text-gray-900">
                                                                {contact.name}
                                                            </p>
                                                            <p className="text-xs text-gray-500">
                                                                {contact.time}
                                                            </p>
                                                        </div>
                                                        <div className="flex items-center justify-between">
                                                            <p className="truncate text-sm text-gray-500">
                                                                {
                                                                    contact.lastMessage
                                                                }
                                                            </p>
                                                            {contact.unread >
                                                                0 && (
                                                                <span className="inline-flex items-center justify-center rounded-full bg-pink-500 px-2 py-1 text-xs font-bold leading-none text-white">
                                                                    {
                                                                        contact.unread
                                                                    }
                                                                </span>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Painel de Chat */}
                    {shouldShowChatPanel && selectedContact && (
                        <div
                            className={`${isMobile ? 'absolute inset-0 z-10 bg-white' : 'w-2/3'} flex flex-col border-l border-gray-200`}
                        >
                            {/* Cabeçalho do chat */}
                            <div className="flex items-center border-b border-gray-200 bg-white p-4">
                                {isMobile && (
                                    <button
                                        onClick={handleBackToContacts}
                                        className="mr-3 text-gray-500 hover:text-gray-700"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            className="h-5 w-5"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                )}
                                <div className="relative flex-shrink-0">
                                    <img
                                        src={selectedContact.avatar}
                                        alt={selectedContact.name}
                                        className="h-10 w-10 rounded-full object-cover"
                                    />
                                    {selectedContact.isOnline && (
                                        <div className="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-green-500"></div>
                                    )}
                                </div>
                                <div className="ml-3">
                                    <p className="text-sm font-medium text-gray-900">
                                        {selectedContact.name}
                                    </p>
                                    <p className="text-xs text-gray-500">
                                        {selectedContact.isOnline
                                            ? 'Online'
                                            : 'Offline'}
                                    </p>
                                </div>
                            </div>

                            {/* Mensagens */}
                            <div
                                className="hide-scrollbar flex-1 overflow-y-auto bg-gradient-to-b from-white to-pink-50 p-4"
                                // Removido o style={{ height: 'calc(...)' }}
                                style={{
                                    WebkitOverflowScrolling: 'touch', // Mantém o scrolling suave em iOS
                                }}
                            >
                                <div className="space-y-3">
                                    {messages.map((message) => (
                                        <div
                                            key={message.id}
                                            className={`flex ${message.isOwn ? 'justify-end' : 'justify-start'}`}
                                        >
                                            <div
                                                className={`max-w-xs rounded-2xl px-4 py-2 lg:max-w-md ${
                                                    message.isOwn
                                                        ? 'rounded-br-none bg-gradient-to-r from-pink-500 to-purple-600 text-white'
                                                        : 'rounded-bl-none border border-gray-200 bg-white text-gray-700 shadow-sm'
                                                }`}
                                            >
                                                <p className="text-sm">
                                                    {message.text}
                                                </p>
                                                <p
                                                    className={`mt-1 text-xs ${message.isOwn ? 'text-pink-100' : 'text-gray-500'} text-right`}
                                                >
                                                    {message.time}
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Input de mensagem */}
                            <div className="border-t border-gray-200 bg-white p-4">
                                <div className="flex items-center">
                                    <input
                                        type="text"
                                        value={newMessage}
                                        onChange={(e) =>
                                            setNewMessage(e.target.value)
                                        }
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter') {
                                                e.preventDefault();
                                                handleSendMessage();
                                            }
                                        }}
                                        placeholder="Digite uma mensagem..."
                                        className="flex-1 rounded-full border border-gray-300 px-4 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-pink-300"
                                    />
                                    <button
                                        onClick={handleSendMessage}
                                        disabled={!newMessage.trim()}
                                        className={`ml-2 rounded-full p-2 ${
                                            newMessage.trim()
                                                ? 'bg-gradient-to-r from-pink-500 to-purple-600 text-white hover:from-pink-600 hover:to-purple-700'
                                                : 'text-gray-400'
                                        }`}
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            className="h-5 w-5"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            <style>{`
                .hide-scrollbar {
                    -ms-overflow-style: none;
                    scrollbar-width: none;
                }
                .hide-scrollbar::-webkit-scrollbar {
                    display: none;
                }
            `}</style>
        </>
    );
}
