export default function About() {
    const base = (import.meta.env.VITE_APP_BASE as string) ?? '';

    return (
        <section
            id="about"
            className="anchor-offset relative px-5 py-16 md:py-20"
        >
            <h2 className="relative mb-12 text-center font-orbitron text-3xl md:text-4xl">
                Sobre Mim
                <span className="absolute -bottom-3 left-1/2 h-1 w-20 -translate-x-1/2 rounded bg-accent" />
            </h2>

            <div className="mx-auto flex max-w-6xl flex-col items-center gap-10 md:flex-row">
                {/* Foto */}
                <div className="text-center md:w-1/2">
                    <div className="relative mx-auto h-72 w-72">
                        {/* glow */}
                        <span className="absolute inset-0 -z-0 rounded-full bg-accent/20 blur-2xl" />
                        <img
                            src={`${base}/images/eu.webp`}
                            alt="João Vitor Ribeiro Tim - Desenvolvedor Full Stack no Paraná, Brasil"
                            loading="lazy"
                            width="288"
                            height="288"
                            className="relative z-10 h-72 w-72 rounded-full border-4 border-accent object-cover shadow-[0_0_30px_rgba(0,242,254,0.3)] will-change-transform"
                            onLoad={(e) => {
                                const target = e.target as HTMLImageElement;
                                target.style.opacity = '1';
                            }}
                            style={{
                                opacity: 0,
                                transition: 'opacity 0.3s ease-in-out',
                            }}
                        />
                    </div>
                </div>

                {/* Texto + CTA */}
                <div className="md:w-1/2">
                    <h3 className="mb-4 text-2xl text-accent">
                        Olá, eu sou João Vitor!
                    </h3>

                    <div className="space-y-4 text-white/90">
                        <p>
                            Sou um <strong>desenvolvedor full stack</strong>{' '}
                            apaixonado por tecnologia e inovação. Com mais de{' '}
                            <strong>10 anos de experiência</strong> no mercado,
                            tenho ajudado empresas a transformar ideias em
                            soluções digitais poderosas.
                        </p>
                        <p>
                            Minha jornada começou com curiosidade sobre como os
                            sites funcionam, e hoje sou especialista em criar
                            aplicações web completas, do{' '}
                            <strong>frontend intuitivo</strong> ao
                            <strong> backend robusto</strong>.
                        </p>
                        <p>
                            Quando não estou codificando, estou explorando novas
                            tecnologias, contribuindo com{' '}
                            <strong>open-source</strong> ou compartilhando
                            conhecimento com a comunidade.
                        </p>
                    </div>

                    {/* CTA centralizado no mobile, alinhado à esquerda no desktop */}
                    <div className="mt-6 flex justify-center md:justify-start">
                        <a
                            href="#contact"
                            className="focus-visible-ring mx-auto rounded-full bg-gradient-to-r from-primary to-secondary px-6 py-3 font-semibold shadow-glow transition hover:-translate-y-1 md:mx-0"
                            aria-label="Entre em contato com João Vitor Ribeiro Tim"
                        >
                            Entre em Contato
                        </a>
                    </div>
                </div>
            </div>

            {/* Garante que a imagem fique acima das partículas */}
            <style>{`
        #about { z-index: 1; }
        #about img { position: relative; z-index: 10; }
      `}</style>
        </section>
    );
}
