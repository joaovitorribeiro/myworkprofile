// resources/js/types/index.d.ts

import { Config } from 'ziggy-js';

export type UserRole = 'user';
export type UserPlan = 'free' | 'premium';
export type Page =
    | 'descobrir'
    | 'postagens'
    | 'conexoes'
    | 'transmissoes'
    | 'perfil';

export interface User {
    id: number;
    name: string;
    username?: string; // nome de usuário opcional
    email: string;
    email_verified_at?: string;
    role: UserRole;
    plan: UserPlan;
    is_mock?: boolean;

    // adicionais
    bio?: string; // descrição do usuário
    avatar?: string; // URL do avatar
}

// Tipagem do modal de upgrade
export interface UpgradeModalProps {
    isOpen: boolean;
    onClose: () => void;
}

export interface AuthenticatedProps {
    userRole: UserRole;
    userPlan: UserPlan;
}

// Torna `user` opcional (pode ser null) para frontend funcionar sem backend
export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
    };
    ziggy: Config & { location: string };
};
