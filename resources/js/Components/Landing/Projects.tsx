const ProjectCard = ({
    icon,
    title,
    desc,
    tags,
    link,
}: {
    icon: React.ReactNode;
    title: string;
    desc: string;
    tags: string[];
    link: string;
}) => (
    <div className="card-glass overflow-hidden transition hover:-translate-y-2 hover:border-accent">
        <div className="flex h-48 items-center justify-center gap-6 bg-gradient-to-br from-primary to-secondary text-white">
            {icon}
        </div>
        <div className="space-y-4 p-6">
            <h3 className="text-xl font-semibold text-accent">{title}</h3>
            <p className="text-white/85">{desc}</p>
            <div className="flex flex-wrap gap-2">
                {tags.map((t) => (
                    <span
                        key={t}
                        className="rounded-full bg-accent/20 px-3 py-1 text-sm text-accent"
                    >
                        {t}
                    </span>
                ))}
            </div>
            <a
                href={link}
                target="_blank"
                rel="noopener noreferrer"
                className="shadow-glow inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-primary to-secondary px-5 py-2 font-semibold transition hover:-translate-y-1"
            >
                <i className="fa-solid fa-arrow-up-right-from-square" />
                Ver Projeto
            </a>
        </div>
    </div>
);

export default function Projects() {
    return (
        <section id="projects" className="anchor-offset px-5 py-16 md:py-20">
            <h2 className="font-orbitron relative mb-12 text-center text-3xl md:text-4xl">
                Projetos Recentes
                <span className="absolute -bottom-3 left-1/2 h-1 w-20 -translate-x-1/2 rounded bg-accent" />
            </h2>

            <div className="mx-auto grid max-w-6xl grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                {/* 1) Economia & Aferição */}
                <ProjectCard
                    icon={
                        <div className="flex items-center gap-6 text-6xl">
                            <i className="fa-solid fa-coins" />
                            <i className="fa-solid fa-ruler-combined" />
                        </div>
                    }
                    title="Controle de Economia e Aferição"
                    desc="Sistema interno para pedidos, cotações, aferição e relatórios, com login institucional e trilhas de auditoria."
                    tags={['Laravel', 'Inertia.js', 'React', 'MySQL']}
                    link="https://economiaccb.com.br/login"
                />

                {/* 2) Limpeza */}
                <ProjectCard
                    icon={
                        <div className="flex items-center gap-6 text-6xl">
                            <i className="fa-solid fa-spray-can-sparkles" />
                            <i className="fa-solid fa-broom" />
                        </div>
                    }
                    title="AK Limpeza"
                    desc="Site institucional com vitrine de serviços, landing responsiva, solicitação de orçamento e integração com WhatsApp."
                    tags={['Laravel', 'Inertia.js', 'React', 'Tailwind']}
                    link="https://aklimpeza.com.br"
                />

                {/* 3) Transporte */}
                <ProjectCard
                    icon={
                        <div className="flex items-center gap-6 text-6xl">
                            <i className="fa-solid fa-truck-fast" />
                            <i className="fa-solid fa-box-open" />
                        </div>
                    }
                    title="Os Guri do Pacotinho"
                    desc="Plataforma de logística com rastreamento de encomendas, simulador de frete e área do cliente."
                    tags={['Laravel', 'Inertia.js', 'React', 'MySQL']}
                    link="https://osguridopacotinho.com.br"
                />
            </div>
        </section>
    );
}
