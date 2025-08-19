import { useEffect, useMemo, useState, type ReactElement } from 'react';
import type { NavLinkProps } from './types';

const SECTIONS = ['home', 'about', 'skills', 'projects', 'contact'] as const;

export default function Navbar(): ReactElement {
    const [open, setOpen] = useState(false);
    const [scrolled, setScrolled] = useState(false);
    const [active, setActive] = useState<string>('home');
    const [progress, setProgress] = useState(0);

    // ESC fecha
    useEffect(() => {
        const onKey = (e: KeyboardEvent) =>
            e.key === 'Escape' && setOpen(false);
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, []);

    // Blur/shrink + progress
    useEffect(() => {
        const onScroll = () => {
            setScrolled(window.scrollY > 50);
            const h = document.documentElement;
            const max = h.scrollHeight - h.clientHeight || 1;
            setProgress(Math.min(100, Math.max(0, (h.scrollTop / max) * 100)));
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    // Travar scroll do body no menu aberto (robusto p/ iOS/Android)
    useEffect(() => {
        const body = document.body;
        const prevOverflow = body.style.overflow;
        const prevPosition = body.style.position;
        const prevTop = body.style.top;
        const prevWidth = body.style.width;
        let scrollY = 0;

        if (open) {
            scrollY = window.scrollY || window.pageYOffset || 0;
            body.style.overflow = 'hidden';
            body.style.position = 'fixed';
            body.style.top = `-${scrollY}px`;
            body.style.width = '100%';
        }

        return () => {
            // Restaurar
            body.style.overflow = prevOverflow;
            body.style.position = prevPosition;
            body.style.top = prevTop;
            body.style.width = prevWidth;
            if (open) {
                // Voltar ao ponto de scroll anterior
                const y = Math.abs(parseInt(prevTop || '0', 10)) || scrollY;
                window.scrollTo(0, y);
            }
        };
    }, [open]);

    // Fecha quando virar md+
    useEffect(() => {
        const onResize = () => window.innerWidth >= 768 && setOpen(false);
        window.addEventListener('resize', onResize);
        return () => window.removeEventListener('resize', onResize);
    }, []);

    // Destacar link ativo
    useEffect(() => {
        const els = SECTIONS.map((id) => document.getElementById(id)).filter(
            Boolean,
        ) as HTMLElement[];
        if (!els.length || !('IntersectionObserver' in window)) return;
        const obs = new IntersectionObserver(
            (entries) => {
                const v = entries
                    .filter((en) => en.isIntersecting)
                    .sort(
                        (a, b) => b.intersectionRatio - a.intersectionRatio,
                    )[0];
                if (v?.target?.id) setActive(v.target.id);
            },
            {
                rootMargin: '-40% 0px -55% 0px',
                threshold: [0, 0.25, 0.5, 0.75, 1],
            },
        );
        els.forEach((el) => obs.observe(el));
        return () => obs.disconnect();
    }, []);

    const smoothScrollTo = (hash: string) => {
        const id = hash.replace('#', '');
        const el = document.getElementById(id);
        if (!el) return;
        
        // Aguarda um pouco para garantir que o DOM está pronto
        setTimeout(() => {
            const navbarHeight = 100; // altura da navbar + margem
            const noMotion = window.matchMedia?.(
                '(prefers-reduced-motion: reduce)',
            ).matches;
            
            // Calcula a posição considerando scroll atual
            const elementPosition = el.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
            
            window.scrollTo({
                top: elementPosition,
                behavior: noMotion ? 'auto' : 'smooth',
            });
            history.replaceState(null, '', `#${id}`);
        }, 100);
    };

    const NavLink = ({ href, children }: NavLinkProps): ReactElement => {
        const id = href.replace('#', '');
        const isActive = active === id;
        return (
            <a
                href={href}
                onClick={(e) => {
                    e.preventDefault();
                    setOpen(false);
                    smoothScrollTo(href);
                }}
                className={`text-light/90 group relative px-3 py-2 text-sm transition-all duration-300 hover:text-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/70 md:text-base ${
                    isActive ? 'text-accent' : ''
                }`}
                aria-current={isActive ? 'page' : undefined}
            >
                <span className="relative z-[1]">{children}</span>
                <span
                    aria-hidden
                    className={`pointer-events-none absolute inset-x-0 -bottom-0.5 mx-auto h-0.5 origin-left bg-accent transition-all ${
                        isActive
                            ? 'w-full scale-x-100'
                            : 'w-0 scale-x-0 group-hover:w-full group-hover:scale-x-100'
                    }`}
                />
            </a>
        );
    };

    const links = useMemo(
        () => [
            { href: '#home', label: 'Início' },
            { href: '#about', label: 'Sobre' },
            { href: '#skills', label: 'Habilidades' },
            { href: '#projects', label: 'Portfólio' },
            { href: '#contact', label: 'Contato' },
        ],
        [],
    );

    return (
        <>
            {/* Skip link */}
            <a
                href="#content"
                className="focus:text-dark sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-accent focus:px-3 focus:py-2"
            >
                Pular para o conteúdo
            </a>

            {/* Barra de progresso */}
            <div className="pointer-events-none fixed inset-x-0 top-0 z-[60] h-0.5">
                <div
                    className="h-full bg-accent transition-[width] duration-150"
                    style={{ width: `${progress}%` }}
                />
            </div>

            <nav
                className={`fixed z-50 transition-all duration-300 ${
                    open
                        ? 'inset-0 min-h-screen bg-black md:inset-x-0 md:top-0 md:min-h-0 md:bg-transparent'
                        : 'inset-x-0 top-0'
                } ${
                    !open
                        ? scrolled
                            ? 'from-darker/95 to-darker/80 bg-gradient-to-b py-3 shadow-2xl shadow-black/30 backdrop-blur'
                            : 'bg-dark/60 py-5 backdrop-blur'
                        : ''
                }`}
                aria-label="Menu principal"
            >
                <div className="h-navbar mx-auto flex max-w-7xl items-center justify-between px-5">
                    <a
                        href="#home"
                        onClick={(e) => {
                            e.preventDefault();
                            smoothScrollTo('#home');
                        }}
                        className="font-orbitron bg-gradient-to-r from-accent to-secondary bg-clip-text text-2xl font-bold text-transparent"
                        aria-label="Voltar ao início"
                    >
                        JVRT
                    </a>

                    <ul className="hidden items-center gap-1 md:flex">
                        {links.map((l) => (
                            <li key={l.href}>
                                <NavLink href={l.href}>{l.label}</NavLink>
                            </li>
                        ))}
                    </ul>

                    <button
                        aria-label="Abrir menu"
                        aria-controls="mobile-menu"
                        aria-expanded={open}
                        onClick={() => setOpen((v) => !v)}
                        className="bg-dark/80 flex flex-col gap-1.5 rounded-lg p-2 shadow ring-1 ring-white/5 md:hidden"
                    >
                        <span
                            className={`h-[3px] w-7 rounded bg-accent transition ${open ? 'translate-y-[6px] rotate-45' : ''}`}
                        />
                        <span
                            className={`h-[3px] w-7 rounded bg-accent transition ${open ? 'opacity-0' : ''}`}
                        />
                        <span
                            className={`h-[3px] w-7 rounded bg-accent transition ${open ? '-translate-y-[6px] -rotate-45' : ''}`}
                        />
                    </button>
                </div>

                {/* === MOBILE FULLSCREEN MENU (preto sólido real) === */}
                <div
                    id="mobile-menu"
                    role="dialog"
                    aria-modal="true"
                    className={`absolute inset-0 z-[999] bg-black transition-opacity duration-200 md:hidden ${
                        open ? 'opacity-100' : 'pointer-events-none opacity-0'
                    }`}
                    onClick={() => setOpen(false)}
                >
                    {/* Fundo preto sólido aplicado no container */}

                    {/* Conteúdo do menu (sem fundos translúcidos) */}
                    <nav
                        role="menu"
                        className="relative z-[1000] flex min-h-screen flex-col items-stretch justify-center gap-3 px-6 pb-[max(env(safe-area-inset-bottom),24px)] pt-[max(env(safe-area-inset-top),24px)]"
                        onClick={(e) => e.stopPropagation()}
                    >
                        {links.map((l, i) => (
                            <a
                                key={l.href}
                                href={l.href}
                                role="menuitem"
                                onClick={(e) => {
                                    e.preventDefault();
                                    setOpen(false);
                                    smoothScrollTo(l.href);
                                }}
                                className="rounded-xl px-6 py-4 text-center text-2xl font-semibold text-white transition hover:opacity-90 active:scale-[0.99]"
                                style={{
                                    transitionDelay: `${open ? i * 40 : 0}ms`,
                                }}
                            >
                                {l.label}
                            </a>
                        ))}

                        <button
                            onClick={() => setOpen(false)}
                            className="shadow-glow mt-6 rounded-full bg-gradient-to-r from-primary to-secondary px-6 py-4 text-xl font-semibold text-white transition hover:-translate-y-0.5 active:scale-[0.99]"
                        >
                            Fechar
                        </button>
                    </nav>

                    {/* Botão fechar */}
                    <button
                        aria-label="Fechar menu"
                        onClick={() => setOpen(false)}
                        className="absolute right-4 top-4 z-[1000] rounded-lg p-3 text-2xl text-white/90 hover:bg-white/10"
                    >
                        ✕
                    </button>
                </div>
            </nav>
        </>
    );
}
