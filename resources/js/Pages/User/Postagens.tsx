// resources/js/Pages/User/Postagens.tsx
import type { UserPlan, UserRole } from '@/types';
import { ChangeEvent, useState } from 'react';

interface PostagensProps {
    userRole: UserRole;
    userPlan: UserPlan;
}
// Definindo o tipo para uma postagem
interface Post {
    id: number;
    type: 'instagram' | 'facebook';
    user: string;
    avatar: string;
    time: string;
    text: string;
    cover: string;
    likes: number;
    comments: number;
    saved?: boolean; // Adicionando propriedade para indicar se está salvo
}
export default function Postagens({ userRole, userPlan }: PostagensProps) {
    const [postText, setPostText] = useState('');
    const [visibility, setVisibility] = useState<
        'public' | 'friends' | 'private'
    >('public');
    const [imagePreview, setImagePreview] = useState<string | null>(null);
    // Atualizando o tipo do filtro para incluir 'saved'
    const [filter, setFilter] = useState<
        'all' | 'saved' | 'facebook' | 'instagram'
    >('all');
    // Adicionando estado para armazenar IDs das postagens salvas
    const [savedPosts, setSavedPosts] = useState<number[]>([]);
    // Atualizando o tipo do array de posts
    const posts: Post[] = [
        {
            id: 1,
            type: 'instagram',
            user: 'Maria',
            avatar: 'https://randomuser.me/api/portraits/women/44.jpg',
            time: '2h atrás',
            text: 'Curtindo a tarde na praia! 🌊☀️',
            cover: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&h=800&q=80&fit=crop',
            likes: 245,
            comments: 32,
        },
        {
            id: 2,
            type: 'instagram',
            user: 'Maria',
            avatar: 'https://randomuser.me/api/portraits/women/44.jpg',
            time: 'Ontem',
            text: 'Pôr do sol incrível hoje 🌅✨',
            cover: 'https://images.unsplash.com/photo-1505506874110-6a7a69069a08?w=600&h=600&q=80&fit=crop',
            likes: 189,
            comments: 15,
        },
        {
            id: 3,
            type: 'facebook',
            user: 'João',
            avatar: 'https://randomuser.me/api/portraits/men/36.jpg',
            time: '5h atrás',
            text: 'Novo projeto saindo do forno! 💻🚀',
            cover: 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&h=630&q=80&fit=crop',
            likes: 87,
            comments: 12,
        },
    ];
    const handlePost = () => {
        if (postText.trim()) {
            console.log(
                'Novo post:',
                postText,
                'Visibilidade:',
                visibility,
                'Imagem:',
                imagePreview,
            );
            setPostText('');
            setImagePreview(null);
        }
    };
    const handleImageChange = (e: ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            setImagePreview(URL.createObjectURL(file));
        }
    };
    // Função para alternar o estado de salvamento de uma postagem
    const toggleSavePost = (postId: number) => {
        setSavedPosts((prevSaved) =>
            prevSaved.includes(postId)
                ? prevSaved.filter((id) => id !== postId)
                : [...prevSaved, postId],
        );
    };
    // Filtrando as postagens com base no estado de salvamento e no filtro selecionado
    const filteredPosts = posts.filter((post) => {
        const isSaved = savedPosts.includes(post.id);
        if (filter === 'saved') return isSaved;
        if (filter === 'all') return true;
        return post.type === filter;
    });
    return (
        <div className="scrollbar-hide mx-auto h-full w-full max-w-3xl overflow-y-auto px-3 py-4 pb-20">
            {/* Área de criação de post - CARD MAIS COMPACTO E COLORIDO */}
            <div className="mb-5 rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-100">
                <div className="flex gap-3">
                    {/* Avatar */}
                    <img
                        src="https://randomuser.me/api/portraits/men/1.jpg"
                        alt="Seu avatar"
                        className="h-10 w-10 flex-shrink-0 rounded-full border object-cover"
                    />
                    {/* Conteúdo principal */}
                    <div className="flex-1">
                        {/* Textarea */}
                        <textarea
                            value={postText}
                            onChange={(e) => setPostText(e.target.value)}
                            placeholder="No que você está pensando?"
                            className="w-full resize-none rounded-lg border-none bg-gray-50 p-2 text-gray-700 placeholder:text-gray-400 focus:outline-none"
                            rows={2}
                        />
                        {/* Preview de imagem */}
                        {imagePreview && (
                            <div className="relative mt-2 inline-block max-w-full">
                                <img
                                    src={imagePreview}
                                    alt="Prévia"
                                    className="max-h-32 rounded-lg border object-contain shadow-sm"
                                />
                                <button
                                    onClick={() => setImagePreview(null)}
                                    className="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-xs text-white shadow"
                                >
                                    ×
                                </button>
                            </div>
                        )}
                        {/* Ações e botão de postar - MAIS ORGANIZADOS E COLORIDOS */}
                        <div className="mt-3 flex flex-wrap items-center justify-between gap-2">
                            {/* Botões de ação coloridos */}
                            <div className="flex gap-2">
                                <label className="flex cursor-pointer items-center gap-1 rounded-lg bg-blue-50 px-2 py-1 text-sm text-blue-600 transition hover:bg-blue-100">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        className="hidden"
                                        onChange={handleImageChange}
                                    />
                                </label>
                                <select
                                    value={visibility}
                                    onChange={(e) =>
                                        setVisibility(e.target.value as any)
                                    }
                                    className="rounded-lg border border-gray-200 bg-gradient-to-r from-purple-50 to-blue-50 px-2 py-1 text-xs text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-100"
                                >
                                    <option value="public">🌍</option>
                                    <option value="friends">👥</option>
                                    <option value="private">🔒</option>
                                </select>
                            </div>
                            {/* Botão de publicar mais colorido */}
                            <button
                                onClick={handlePost}
                                disabled={!postText.trim()}
                                className={`rounded-lg px-4 py-1.5 text-sm font-semibold transition ${
                                    postText.trim()
                                        ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-md hover:from-blue-600 hover:to-purple-700 hover:shadow-lg'
                                        : 'cursor-not-allowed bg-gray-200 text-gray-400'
                                }`}
                            >
                                Publicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {/* Filtro de rede social - Atualizado para refletir as novas opções */}
            <div className="mb-4 flex justify-center">
                <select
                    value={filter}
                    onChange={(e) => setFilter(e.target.value as any)}
                    className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-100"
                >
                    <option value="all">Todas as Postagens...</option>
                    <option value="saved">Salvos</option>
                </select>
            </div>
            {/* Lista de Publicações */}
            <div className="flex flex-col gap-5">
                {filteredPosts.map((post) => (
                    <div
                        key={post.id}
                        className="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100"
                    >
                        {/* Cabeçalho */}
                        <div className="flex items-center justify-between p-3">
                            <div className="flex items-center gap-3">
                                <img
                                    src={post.avatar}
                                    alt={post.user}
                                    className="h-10 w-10 rounded-full border object-cover"
                                />
                                <div>
                                    <p className="text-sm font-semibold">
                                        {post.user}
                                    </p>
                                    <p className="text-xs text-gray-500">
                                        {post.time}
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-center gap-2">
                                {/* Botão de salvar */}
                                <button
                                    onClick={() => toggleSavePost(post.id)}
                                    className="text-gray-400 transition-colors hover:text-blue-500"
                                    aria-label={
                                        savedPosts.includes(post.id)
                                            ? 'Remover dos salvos'
                                            : 'Salvar postagem'
                                    }
                                >
                                    {savedPosts.includes(post.id) ? '🔖' : '📑'}
                                </button>
                                <button className="text-gray-400 hover:text-gray-600">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="h-5 w-5"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        {/* Conteúdo */}
                        {post.type === 'facebook' && (
                            <>
                                <div className="px-3 pb-2">
                                    <p className="text-sm text-gray-700">
                                        {post.text}
                                    </p>
                                </div>
                                <div className="overflow-hidden">
                                    {/* Padronizando o tamanho das imagens do Facebook */}
                                    <img
                                        src={post.cover}
                                        alt=""
                                        className="h-96 w-full object-cover" // Altura fixa para padronização
                                    />
                                </div>
                            </>
                        )}
                        {post.type === 'instagram' && (
                            <>
                                {/* Imagem no estilo Instagram - Padronizando o tamanho */}
                                <div className="relative">
                                    <img
                                        src={post.cover}
                                        alt=""
                                        className="h-96 w-full object-cover" // Altura fixa para padronização
                                    />
                                    <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                                </div>
                                <div className="p-3">
                                    <p className="text-sm text-gray-700">
                                        {post.text}
                                    </p>
                                    <div className="mt-3 flex items-center justify-between">
                                        <div className="flex items-center gap-4">
                                            <button className="flex items-center gap-1 text-xs text-gray-500 hover:text-red-500">
                                                ❤️ <span>{post.likes}</span>
                                            </button>
                                            <button className="flex items-center gap-1 text-xs text-gray-500 hover:text-blue-500">
                                                💬 <span>{post.comments}</span>
                                            </button>
                                        </div>
                                        {/* Movendo o botão de salvar para o cabeçalho */}
                                        {/* Removido daqui */}
                                    </div>
                                </div>
                            </>
                        )}
                        {/* Botões de interação padronizados */}
                        <div className="border-t border-gray-50 p-3">
                            <div className="flex justify-around text-sm text-gray-600">
                                <button className="flex items-center gap-2 transition hover:text-red-500">
                                    ❤️ Curtir
                                </button>
                                <button className="flex items-center gap-2 transition hover:text-blue-500">
                                    💬 Comentar
                                </button>
                                <button className="flex items-center gap-2 transition hover:text-green-500">
                                    ↗️ Compartilhar
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
