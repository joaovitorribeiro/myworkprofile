import { usePage } from '@inertiajs/react';
import { useState } from 'react';

import UserLayout from '@/Layouts/UserLayout';
import type { Page, PageProps } from '@/types';

import LiveRandomInstantPage from './LiveList';
import MatchesChat from './MatchesChat';
import Postagens from './Postagens';
import ReactView from './ReactView';
import UserProfile from './UserProfile';

export interface Person {
    id: number;
    name: string;
    age: number;
    city: string;
    photos: string[];
    bio: string;
    interests: string[];
}

export default function Dashboard() {
    const { auth, people = [] } =
        usePage<PageProps<{ people?: Person[] }>>().props;

    const user = auth?.user;
    const userRole = user?.role;
    const userPlan = user?.plan;
    const [activePage, setActivePage] = useState<Page>('descobrir');

    // Bloqueio frontend simples
    if (!user || userRole !== 'user' || !userPlan) {
        return (
            <div className="flex h-screen items-center justify-center p-6 text-center text-red-600">
                Acesso negado.
            </div>
        );
    }

    const renderPage = () => {
        switch (activePage) {
            case 'descobrir':
                return (
                    <ReactView
                        userRole={userRole}
                        userPlan={userPlan}
                        people={people}
                    />
                );
            case 'postagens':
                return <Postagens userRole={userRole} userPlan={userPlan} />;
            case 'conexoes':
                return <MatchesChat userRole={userRole} userPlan={userPlan} />;
            case 'transmissoes':
                return (
                    <LiveRandomInstantPage
                        userRole={userRole}
                        userPlan={userPlan}
                    />
                );
            case 'perfil':
                return <UserProfile />;
            default:
                return null;
        }
    };

    return (
        <UserLayout
            activePage={activePage}
            userRole={userRole}
            userPlan={userPlan}
            onNavigate={setActivePage}
        >
            {renderPage()}
        </UserLayout>
    );
}
