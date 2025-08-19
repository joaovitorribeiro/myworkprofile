import type { Page, UserPlan, UserRole } from '@/types';
import React from 'react';

interface Props {
    children: React.ReactNode;
    activePage: Page;
    userRole?: UserRole;
    userPlan?: UserPlan;
    onNavigate?: (page: Page) => void;
}

type MenuItem = {
    page: Page;
    icon: string;
    label: string;
    roles: UserRole[];
    plans?: UserPlan[]; // (opcional)
};

const menuItems: MenuItem[] = [
    {
        page: 'descobrir',
        icon: 'favorite_border',
        label: 'Descobrir',
        roles: ['user'],
    },
    {
        page: 'postagens',
        icon: 'edit',
        label: 'Postagens',
        roles: ['user'],
    },
    {
        page: 'conexoes',
        icon: 'chat',
        label: 'Chat',
        roles: ['user'],
    },
    {
        page: 'transmissoes',
        icon: 'live_tv',
        label: 'Video',
        roles: ['user'],
    },
    {
        page: 'perfil',
        icon: 'person',
        label: 'Perfil',
        roles: ['user'],
    },
];

export default function UserLayout({
    children,
    activePage,
    userRole,
    userPlan,
    onNavigate,
}: Props) {
    if (userRole !== 'user' || !userPlan) {
        return (
            <div className="flex h-screen items-center justify-center bg-white p-6 text-center text-red-600">
                Acesso negado. Apenas usuários com plano ativo e perfil do tipo{' '}
                <strong>user</strong> podem acessar.
            </div>
        );
    }

    const visibleItems = menuItems.filter(
        ({ roles, plans }) =>
            roles.includes(userRole) && (!plans || plans.includes(userPlan)),
    );

    return (
        <div className="relative h-screen w-full overflow-hidden bg-white font-sans text-gray-800">
            <main className="absolute inset-x-0 bottom-20 top-0 overflow-hidden">
                <div className="h-full w-full">{children}</div>
            </main>

            <nav className="fixed bottom-0 left-0 right-0 z-50 h-20 w-full border-t border-gray-200 bg-gradient-to-r from-blue-700 via-blue-600 to-blue-800 shadow-inner">
                <div className="flex h-full w-full">
                    {visibleItems.map(({ page, icon, label }) => {
                        const isActive = activePage === page;

                        return (
                            <button
                                key={page}
                                onClick={() => onNavigate?.(page)}
                                aria-label={label}
                                className={`group relative flex flex-1 flex-col items-center justify-center transition-all duration-300 ease-out active:scale-95 ${
                                    isActive
                                        ? 'scale-110 text-white'
                                        : 'text-white/80 hover:scale-105 hover:text-white'
                                }`}
                            >
                                <span className="absolute inset-0 translate-x-full rounded-xl bg-gradient-to-r from-white/0 via-white/20 to-white/0 opacity-0 transition-all duration-700 group-hover:translate-x-0 group-hover:opacity-100" />

                                <div
                                    className={`relative flex w-full max-w-[90px] flex-col items-center justify-center gap-1 rounded-xl p-3 transition-all ${
                                        isActive
                                            ? 'bg-white/20 shadow-lg backdrop-blur-sm'
                                            : 'hover:bg-white/10'
                                    }`}
                                >
                                    <span className="material-icons text-2xl md:text-3xl">
                                        {icon}
                                    </span>
                                    <span className="text-xs font-semibold md:text-sm">
                                        {label}
                                    </span>

                                    {isActive && (
                                        <span className="absolute right-0 top-0 h-2 w-2 animate-pulse rounded-full bg-white shadow-sm shadow-white/70" />
                                    )}
                                </div>

                                {isActive && (
                                    <div className="absolute bottom-0 h-1.5 w-10 animate-pulse rounded-full bg-white" />
                                )}
                            </button>
                        );
                    })}
                </div>
            </nav>

            <style>{`
                html, body, #app {
                    height: 100%;
                    margin: 0;
                    padding: 0;
                    overflow: hidden;
                }
            `}</style>
        </div>
    );
}
