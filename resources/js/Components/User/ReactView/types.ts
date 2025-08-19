// resources/js/Components/User/ReactView/types.ts

// Tipos compartilhados com todo o app
import type { UserPlan, UserRole } from '@/types';

/**
 * Representa uma pessoa exibida no ReactView
 */
export interface Person {
    id: number;
    name: string;
    age: number;
    city: string;
    photos: string[];
    bio: string;
    interests: string[];
}

/**
 * Props da página ReactView
 */
export interface ReactViewProps {
    userRole: UserRole;
    userPlan: UserPlan;
    people: Person[];
}

/**
 * Props do componente LikeFeedback
 */
export interface LikeFeedbackProps {
    isLiked: boolean;
    name: string;
}

/**
 * Props do componente PhotoCarousel
 */
export interface PhotoCarouselProps {
    photos: string[];
    isPremiumUser: boolean;
    actionHeight: number; // recebido do ReactView
}

/**
 * Props do componente NotificationModal
 */
export interface NotificationModalProps {
    isOpen: boolean;
    onClose: () => void;
}

/**
 * Props do componente LocationModal
 */
export interface LocationModalProps {
    isOpen: boolean;
    onClose: () => void;
}

/**
 * Props do componente FooterButtons
 */
export interface FooterButtonsProps {
    plan: UserPlan;
    onLocationClick: () => void;
    onUpgradeClick: () => void;
    onNotificationClick: () => void;
    notificationCount: number;
}

/**
 * Props do componente HeaderButtons
 */
export interface HeaderButtonsProps {
    plan: UserPlan;
    onLocationClick: () => void;
    onUpgradeClick: () => void;
    onNotificationClick: () => void;
    notificationCount?: number; // opcional
    onSearch?: (value: string) => void; // opcional
}

/**
 * Props do componente AnonymousMessageModal
 */
export interface AnonymousMessageModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSend: (message: string) => void;
    message: string;
    setMessage: (value: string) => void;
}

/**
 * Props do componente UserProfile
 */
export interface UserProfileProps {
    name: string;
    age: number;
    city: string;
    photo: string;
}
