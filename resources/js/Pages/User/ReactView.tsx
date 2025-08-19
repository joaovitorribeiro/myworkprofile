import UserLayout from '@/Layouts/UserLayout';
import { Head } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

// Tipos
import type { ReactViewProps } from '@/Components/User/ReactView/types';

// Componentes
import FooterButtons from '@/Components/User/ReactView/FooterButtons';
import HeaderButtons from '@/Components/User/ReactView/HeaderButtons';
import LikeFeedback from '@/Components/User/ReactView/LikeFeedback';
import PhotoCarousel from '@/Components/User/ReactView/PhotoCarousel';
import UserProfile from '@/Components/User/ReactView/UserProfile';

import AnonymousMessageModal from '@/Components/User/ReactView/AnonymousMessageModal';
import LocationModal from '@/Components/User/ReactView/LocationModal';
import NotificationModal from '@/Components/User/ReactView/NotificationModal';
import UpgradeModal from '@/Components/User/UpgradeModal';

export default function ReactView({
    userRole,
    userPlan,
    people,
}: ReactViewProps) {
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isLiked, setIsLiked] = useState(false);
    const [showAnonymousModal, setShowAnonymousModal] = useState(false);
    const [message, setMessage] = useState('');

    const [showLocationModal, setShowLocationModal] = useState(false);
    const [showUpgradeModal, setShowUpgradeModal] = useState(false);
    const [showNotificationsModal, setShowNotificationsModal] = useState(false);

    const footerRef = useRef<HTMLDivElement>(null);
    const [actionHeight, setActionHeight] = useState(0);

    const isPremiumUser = userPlan === 'premium';
    const currentPerson = people[currentIndex] || null;

    // Atualiza altura dos botões
    useEffect(() => {
        if (footerRef.current) {
            setActionHeight(footerRef.current.offsetHeight);
        }
    }, []);

    const nextPerson = () => {
        if (people.length > 0) {
            setCurrentIndex((prev) => (prev + 1) % people.length);
        }
    };

    const handleLike = () => {
        if (!currentPerson) return;
        setIsLiked(true);
        setTimeout(() => {
            setIsLiked(false);
            nextPerson();
        }, 1500);
    };

    const handleBlock = () => {
        if (!currentPerson) return;
        console.log(`Usuário bloqueado: ${currentPerson.name}`);
        nextPerson();
    };

    const handleAnonymousSend = (msg: string) => {
        if (!currentPerson) return;
        console.log(`Mensagem anônima para ${currentPerson.name}:`, msg);
        setMessage('');
        setShowAnonymousModal(false);
    };

    // Nenhuma pessoa disponível
    if (!currentPerson) {
        return (
            <UserLayout
                activePage="descobrir"
                userRole={userRole}
                userPlan={userPlan}
            >
                <div className="flex h-screen items-center justify-center text-gray-500">
                    Nenhuma pessoa disponível no momento.
                </div>
            </UserLayout>
        );
    }

    return (
        <UserLayout
            activePage="descobrir"
            userRole={userRole}
            userPlan={userPlan}
        >
            <Head title="Descobrir Pessoas | LoveBlock" />

            <div className="flex min-h-screen flex-col overflow-y-auto bg-white">
                {/* Topo */}
                <HeaderButtons
                    plan={userPlan}
                    onLocationClick={() => setShowLocationModal(true)}
                    onUpgradeClick={() => setShowUpgradeModal(true)}
                    onNotificationClick={() => setShowNotificationsModal(true)}
                    notificationCount={3}
                />

                {/* Perfil da pessoa atual */}
                <UserProfile
                    name={currentPerson.name}
                    age={currentPerson.age}
                    city={currentPerson.city}
                    photo={currentPerson.photos[0]}
                />

                {/* Carrossel de fotos */}
                <PhotoCarousel
                    photos={currentPerson.photos}
                    isPremiumUser={isPremiumUser}
                    actionHeight={actionHeight}
                />

                {/* Feedback de curtida */}
                <LikeFeedback isLiked={isLiked} name={currentPerson.name} />

                {/* Botões de ação */}
                <FooterButtons
                    ref={footerRef}
                    onLike={handleLike}
                    onBlock={handleBlock}
                    onAnonymousMessage={() => setShowAnonymousModal(true)}
                />
            </div>

            {/* Modais */}
            <AnonymousMessageModal
                isOpen={showAnonymousModal}
                onClose={() => setShowAnonymousModal(false)}
                onSend={handleAnonymousSend}
                message={message}
                setMessage={setMessage}
            />

            <LocationModal
                isOpen={showLocationModal}
                onClose={() => setShowLocationModal(false)}
            />

            <UpgradeModal
                isOpen={showUpgradeModal}
                onClose={() => setShowUpgradeModal(false)}
            />

            <NotificationModal
                isOpen={showNotificationsModal}
                onClose={() => setShowNotificationsModal(false)}
            />
        </UserLayout>
    );
}
