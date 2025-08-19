import { Head, router, useForm } from '@inertiajs/react';
import React from 'react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/login'); // Substitua pela sua rota de login real
    };

    return (
        <>
            <Head title="Login | LoveBlock" />
            <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-500 via-blue-600 to-blue-800 p-4">
                {/* Floating hearts background */}
                <div className="absolute inset-0 overflow-hidden">
                    {[...Array(15)].map((_, i) => (
                        <div
                            key={i}
                            className="animate-float absolute text-xl opacity-0"
                            style={{
                                left: `${Math.random() * 100}%`,
                                top: `${Math.random() * 100}%`,
                                animationDelay: `${Math.random() * 5}s`,
                                animationDuration: `${3 + Math.random() * 4}s`,
                            }}
                        >
                            ❤️
                        </div>
                    ))}
                </div>

                <div className="relative w-full max-w-md rounded-xl bg-white/90 p-8 shadow-2xl backdrop-blur-sm">
                    {/* Gradient top border */}
                    <div className="absolute left-0 top-0 h-1 w-full rounded-t-xl bg-gradient-to-r from-blue-500 to-blue-600"></div>

                    <div className="mb-8 text-center">
                        <h1 className="mb-2 bg-gradient-to-r from-blue-500 to-blue-600 bg-clip-text text-4xl font-bold text-transparent">
                            LoveBlock
                        </h1>
                        <p className="text-gray-600">Encontre conexões reais</p>
                    </div>

                    <form onSubmit={submit} className="space-y-6">
                        <div>
                            <label
                                htmlFor="email"
                                className="mb-1 block text-sm font-medium text-gray-700"
                            >
                                E-mail
                            </label>
                            <input
                                id="email"
                                type="email"
                                className="w-full rounded-lg border border-gray-300 p-3 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                                placeholder="seu@email.com"
                                required
                            />
                            {errors.email && (
                                <p className="mt-1 text-sm text-blue-600">
                                    {errors.email}
                                </p>
                            )}
                        </div>
                        <div>
                            <label
                                htmlFor="password"
                                className="mb-1 block text-sm font-medium text-gray-700"
                            >
                                Senha
                            </label>
                            <input
                                id="password"
                                type="password"
                                className="w-full rounded-lg border border-gray-300 p-3 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                value={data.password}
                                onChange={(e) =>
                                    setData('password', e.target.value)
                                }
                                placeholder="Digite sua senha"
                                required
                            />
                            {errors.password && (
                                <p className="mt-1 text-sm text-blue-600">
                                    {errors.password}
                                </p>
                            )}
                        </div>
                        <div className="flex items-center justify-between">
                            <div className="flex items-center">
                                <input
                                    id="remember-me"
                                    name="remember-me"
                                    type="checkbox"
                                    className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label
                                    htmlFor="remember-me"
                                    className="ml-2 block text-sm text-gray-700"
                                >
                                    Lembrar de mim
                                </label>
                            </div>

                            <div className="text-sm">
                                <a
                                    href="/forgot-password"
                                    className="font-medium text-blue-600 hover:text-blue-500"
                                >
                                    Esqueceu a senha?
                                </a>
                            </div>
                        </div>

                        <button
                            type="button"
                            disabled={processing}
                            onClick={() => router.visit('/dashboard')}
                            className="w-full transform rounded-full bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-3 font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl disabled:opacity-70"
                        >
                            {processing ? 'Entrando...' : 'Entrar'}
                        </button>
                    </form>

                    <div className="mt-6">
                        <div className="relative">
                            <div className="absolute inset-0 flex items-center">
                                <div className="w-full border-t border-gray-300"></div>
                            </div>
                            <div className="relative flex justify-center text-sm">
                                <span className="bg-white px-2 text-gray-500">
                                    Ou continue com
                                </span>
                            </div>
                        </div>

                        <div className="mt-6 grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                className="inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 shadow-sm hover:bg-gray-50"
                            >
                                <span className="sr-only">
                                    Entrar com Google
                                </span>
                                <svg
                                    className="h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M12.545 10.239v3.821h5.445c-0.712 2.315-2.647 3.972-5.445 3.972-3.332 0-6.033-2.701-6.033-6.032s2.701-6.032 6.033-6.032c1.498 0 2.866 0.549 3.921 1.453l2.814-2.814c-1.786-1.667-4.167-2.698-6.735-2.698-5.522 0-10 4.477-10 10s4.478 10 10 10c8.396 0 10-7.496 10-10 0-0.67-0.065-1.338-0.195-2.001h-9.805z" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                className="inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 shadow-sm hover:bg-gray-50"
                            >
                                <span className="sr-only">
                                    Entrar com Facebook
                                </span>
                                <svg
                                    className="h-5 w-5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div className="mt-6 text-center text-sm text-gray-600">
                        Não tem uma conta?{' '}
                        <a
                            href="/register"
                            className="font-medium text-blue-600 hover:text-blue-500"
                        >
                            Cadastre-se
                        </a>
                    </div>
                </div>
            </div>

            <style>{`
                @keyframes float {
                    0%,
                    100% {
                        transform: translateY(0);
                        opacity: 0;
                    }
                    50% {
                        transform: translateY(-20px);
                        opacity: 0.7;
                    }
                }
                .animate-float {
                    animation: float 4s ease-in-out infinite;
                }
            `}</style>
        </>
    );
}
