import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function UserProfile() {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;

    const [activeTab, setActiveTab] = useState<'fotos' | 'postagens'>('fotos');

    const tabs = [
        { id: 'fotos', label: 'Fotos', count: 12 },
        { id: 'postagens', label: 'Postagens', count: 8 },
    ];

    const posts = Array.from({ length: 6 }); // Simula 6 posts

    if (!user) {
        return (
            <>
                <Head title="Seu Perfil | LoveBlock" />
                <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                    <p className="rounded bg-red-100 px-4 py-2 text-lg text-red-600 shadow">
                        Usuário não autenticado.
                    </p>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title={`#${user.username || user.name} | LoveBlock`} />

            <main className="flex h-screen w-full flex-col bg-gradient-to-b from-white to-gray-50 px-3">
                {/* Cabeçalho fixo */}
                <div className="flex-shrink-0 rounded-2xl bg-white p-6 shadow-lg">
                    <div className="flex items-center gap-4">
                        <div className="relative h-20 w-20 flex-shrink-0">
                            <img
                                src={
                                    user.avatar?.trim()
                                        ? user.avatar
                                        : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                              user.name || 'Usuário',
                                          )}&background=random&color=fff`
                                }
                                alt={user.name || 'Usuário'}
                                className="h-20 w-20 rounded-lg border border-gray-300 object-cover"
                            />
                        </div>

                        <div className="flex flex-col">
                            <h1 className="text-2xl font-bold text-gray-800">
                                #{user.username?.trim() || user.name?.trim()}
                            </h1>
                            <p className="mt-1 text-sm text-gray-500">
                                {user.bio?.trim() ||
                                    'Adicione uma breve descrição sobre você.'}
                            </p>
                        </div>
                    </div>

                    <div className="mt-6 grid grid-cols-3 gap-3">
                        {[
                            { label: 'Publicações', value: 96 },
                            { label: 'Seguidores', value: '1.2M' },
                            { label: 'Seguindo', value: 520 },
                        ].map((stat, i) => (
                            <div
                                key={i}
                                className="rounded-lg bg-gray-100 p-3 text-center shadow-sm"
                            >
                                <p className="text-lg font-bold text-gray-800">
                                    {stat.value}
                                </p>
                                <span className="text-xs text-gray-500">
                                    {stat.label}
                                </span>
                            </div>
                        ))}
                    </div>

                    <div className="mt-5 flex gap-3">
                        <button className="flex-1 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 py-2 text-sm font-semibold text-white shadow-md hover:opacity-90">
                            Publicar Fotos
                        </button>
                        <button className="rounded-lg bg-gray-200 p-2 hover:bg-gray-300">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                strokeWidth={1.8}
                                stroke="currentColor"
                                className="h-5 w-5 text-gray-700"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.53-.88 3.33.92 2.45 2.45a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.88 1.53-.92 3.33-2.45 2.45a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.53.88-3.33-.92-2.45-2.45a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.88-1.53.92-3.33 2.45-2.45.996.574 2.276.235 2.573-1.066z"
                                />
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                {/* Abas e conteúdo */}
                <div
                    className="mt-6 overflow-y-auto"
                    style={{
                        maxHeight: 'calc(100vh - 370px)',
                        paddingBottom: '90px',
                    }}
                >
                    <div className="mb-4 flex justify-center gap-3">
                        {tabs.map((tab) => (
                            <button
                                key={tab.id}
                                className={`flex items-center gap-2 rounded-lg border px-5 py-2 text-sm font-medium transition-all duration-150 ${
                                    activeTab === tab.id
                                        ? 'border-gray-400 bg-gray-100 text-gray-900 shadow-sm'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:text-gray-800'
                                }`}
                                onClick={() =>
                                    setActiveTab(
                                        tab.id as 'fotos' | 'postagens',
                                    )
                                }
                            >
                                {tab.label}
                                <span
                                    className={`rounded-full px-2 py-0.5 text-xs font-semibold ${
                                        activeTab === tab.id
                                            ? 'bg-gray-300 text-gray-800'
                                            : 'bg-gray-100 text-gray-500'
                                    }`}
                                >
                                    {tab.count}
                                </span>
                            </button>
                        ))}
                    </div>

                    {activeTab === 'fotos' ? (
                        <div className="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6">
                            {posts.map((_, index) => (
                                <div
                                    key={index}
                                    className="flex aspect-square items-center justify-center rounded-lg bg-gray-200 transition hover:bg-gray-300"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        className="h-6 w-6 text-gray-400"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={1.5}
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {posts.map((_, index) => (
                                <div
                                    key={index}
                                    className="rounded-lg bg-white p-4 shadow-sm"
                                >
                                    <div className="flex items-center gap-3">
                                        <div className="h-10 w-10 rounded-full bg-gray-200"></div>
                                        <div>
                                            <p className="font-semibold text-gray-800">
                                                @{user.username || user.name}
                                            </p>
                                            <p className="text-xs text-gray-500">
                                                2 horas atrás
                                            </p>
                                        </div>
                                    </div>
                                    <p className="mt-3 text-gray-700">
                                        Esta é uma postagem de exemplo número{' '}
                                        {index + 1}.
                                    </p>
                                    <div className="mt-3 h-48 rounded-lg bg-gray-200"></div>
                                    <div className="mt-3 flex gap-4 text-gray-500">
                                        <button className="flex items-center gap-1">
                                            ❤️ 24
                                        </button>
                                        <button className="flex items-center gap-1">
                                            💬 5
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </main>
        </>
    );
}
