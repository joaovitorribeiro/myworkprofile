// resources/js/Components/User/LiveList/types.ts
import type { UserPlan, UserRole } from '@/types';
import type { MutableRefObject } from 'react';

// Tipo para cada usuário ao vivo
export interface LiveUser {
    id: number;
    name: string;
    age: number;
    gender: 'male' | 'female' | 'other';
    interests: string[];
    location: string;
    thumbnail: string;
    isOnline: boolean;
}

// Tipo para mensagens no chat
export interface ChatMessage {
    text: string;
    isMe: boolean;
}

// Props para o ChatPanel
export interface ChatPanelProps {
    messages: ChatMessage[];
    newMessage: string;
    setNewMessage: (value: string) => void;
    sendMessage: () => void;
    chatContainerRef: MutableRefObject<HTMLDivElement | null>;
    setShowChat: (value: boolean) => void;
}

// Props genéricas para páginas que usam LiveList
export interface LiveListPageProps {
    userPlan: UserPlan;
    userRole: UserRole;
}

// Props do cabeçalho
export interface HeaderProps {
    isSearching: boolean;
    setIsSearching: (value: boolean) => void;
    showFilters: boolean;
    setShowFilters: (value: boolean) => void;
}

// Props específicas do MainVideoArea
export interface MainVideoAreaProps {
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

// Props para o painel de filtros
export interface FiltersPanelProps {
    filterLocation: string;
    setFilterLocation: (value: string) => void;
    locationsList: string[];
    filterGender: string;
    setFilterGender: (value: string) => void;
    interestsList: string[];
    filterInterests: string[];
    setFilterInterests: (value: string[]) => void;
}
