import { useMemo } from 'react';
import {
    FaCloud,
    FaCode,
    FaDatabase,
    FaMobileAlt,
    FaRobot,
    FaServer,
} from 'react-icons/fa';
import Particles from 'react-tsparticles';
import type { Engine, ISourceOptions } from 'tsparticles-engine';
import { loadSlim } from 'tsparticles-slim';
import type { Face, FaceIcons, FaceTransforms } from './types';

export default function Hero() {
    const particlesInit = async (engine: Engine) => {
        await loadSlim(engine);
    };

    const options = useMemo<ISourceOptions>(
        () => ({
            background: { color: { value: 'transparent' } },
            fullScreen: { enable: true, zIndex: 0 },
            particles: {
                number: {
                    value: window.innerWidth < 768 ? 30 : 100, // Muito menos partículas no mobile
                    density: { enable: true, area: window.innerWidth < 768 ? 1200 : 700 },
                },
                color: { value: '#00f2fe' },
                shape: { type: 'circle' },
                opacity: {
                    value: window.innerWidth < 768 ? 0.3 : 0.6, // Menos opacidade no mobile
                    random: { enable: true, minimumValue: window.innerWidth < 768 ? 0.1 : 0.25 },
                },
                size: { value: { min: 1, max: window.innerWidth < 768 ? 2 : 3 } }, // Partículas menores no mobile
                links: {
                    enable: window.innerWidth >= 768, // Desabilita links no mobile
                    distance: 150,
                    color: '#00f2fe',
                    opacity: 0.35,
                    width: 1,
                },
                move: { 
                    enable: true, 
                    speed: window.innerWidth < 768 ? 1 : 2, // Movimento mais lento no mobile
                    random: true, 
                    outModes: { default: 'out' } 
                },
            },
            interactivity: {
                events: {
                    onHover: { enable: window.innerWidth >= 768, mode: 'repulse' }, // Desabilita hover no mobile
                    onClick: { enable: window.innerWidth >= 768, mode: 'push' }, // Desabilita click no mobile
                    resize: true,
                },
            },
        }),
        [],
    );

    const faces: Face[] = ['front', 'back', 'right', 'left', 'top', 'bottom'];

    const transforms: FaceTransforms = {
        front: 'translateZ(100px)',
        back: 'rotateY(180deg) translateZ(100px)',
        right: 'rotateY(90deg) translateZ(100px)',
        left: 'rotateY(-90deg) translateZ(100px)',
        top: 'rotateX(90deg) translateZ(100px)',
        bottom: 'rotateX(-90deg) translateZ(100px)',
    };

    const icons: FaceIcons = {
        front: <FaCode />,
        back: <FaServer />,
        right: <FaDatabase />,
        left: <FaMobileAlt />,
        top: <FaRobot />,
        bottom: <FaCloud />,
    };

    return (
        <section
            id="home"
            className="anchor-offset relative flex min-h-screen items-center justify-center px-5 text-center"
        >
            <Particles
                id="tsparticles"
                init={particlesInit}
                options={options}
                className="pointer-events-none"
            />

            <div className="pointer-events-none absolute right-4 top-20 z-0 h-32 w-32 [perspective:1000px] sm:right-8 sm:top-28 sm:h-40 sm:w-40 md:right-10 md:top-32 md:h-48 md:w-48 xl:h-52 xl:w-52">
                <div className="animate-rotate3d relative h-full w-full [transform-style:preserve-3d]">
                    {faces.map((face) => (
                        <div
                            key={face}
                            className="absolute flex h-32 w-32 items-center justify-center border-2 border-accent/80 bg-accent/10 text-2xl text-accent/90 shadow-[0_0_20px_rgba(0,242,255,0.3)] sm:h-40 sm:w-40 sm:text-3xl md:h-48 md:w-48 md:text-4xl xl:h-52 xl:w-52"
                            style={{ transform: transforms[face] }}
                        >
                            {icons[face]}
                        </div>
                    ))}
                </div>
            </div>

            <div className="relative z-10 mx-auto max-w-3xl">
                <h1 className="font-orbitron animate-float mb-5 bg-gradient-to-r from-accent to-secondary bg-clip-text text-5xl text-transparent md:text-6xl">
                    João Vitor Ribeiro Tim
                </h1>
                <h2 className="mb-6 text-2xl font-semibold md:text-3xl">
                    <span className="inline-block transform transition-transform duration-300 hover:scale-105">
                        <span className="text-2xl mr-2">💻</span>
                        <span className="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 bg-clip-text text-transparent">Desenvolvedor Full Stack</span>
                    </span>
                    <span className="mx-2 text-blue-300 font-bold">&</span>
                    <span className="inline-block transform transition-transform duration-300 hover:scale-105">
                        <span className="text-2xl mr-2">🏗️</span>
                        <span className="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 bg-clip-text text-transparent">Arquiteto Digital</span>
                    </span>
                </h2>
                <p className="mx-auto mb-8 max-w-2xl text-lg text-white/90 md:text-xl">
                    Transformando ideias em experiências digitais
                    extraordinárias com código limpo, design inovador e soluções
                    criativas.
                </p>
                <a
                    href="#projects"
                    className="shadow-glow ease-playful inline-block rounded-full bg-gradient-to-r from-primary to-secondary px-8 py-4 font-semibold transition duration-300 hover:-translate-y-1"
                >
                    Ver Portifólio
                </a>
            </div>
        </section>
    );
}
