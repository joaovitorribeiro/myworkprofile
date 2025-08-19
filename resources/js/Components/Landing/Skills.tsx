import { FaCode, FaDatabase, FaShieldAlt, FaWrench } from 'react-icons/fa';
import { SkillCardProps } from './types';

const SkillCard = ({ title, icon: Icon, items }: SkillCardProps) => (
    <div className="group relative rounded-2xl bg-gradient-to-br from-accent/30 via-accent/10 to-transparent p-[2px] transition-all duration-500 hover:scale-[1.02] hover:shadow-[0_10px_40px_rgba(0,242,254,0.35)]">
        <div className="relative h-full rounded-2xl bg-[linear-gradient(135deg,rgba(255,255,255,0.08),rgba(15,12,41,0.6))] p-6 backdrop-blur-xl">
            {/* Efeito Glow */}
            <span className="pointer-events-none absolute -top-24 left-1/2 h-48 w-48 -translate-x-1/2 rounded-full bg-accent/10 blur-3xl transition-opacity duration-500 group-hover:opacity-80" />

            {/* Ícone centralizado no mobile */}
            <div className="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-accent/20 to-transparent shadow-inner md:mx-0">
                <Icon className="text-2xl text-accent" />
            </div>

            {/* Título */}
            <h3 className="mb-4 bg-gradient-to-r from-white to-accent bg-clip-text text-center text-xl font-semibold text-transparent md:text-left">
                {title}
            </h3>

            {/* Badges */}
            <div className="flex flex-wrap justify-center gap-2 md:justify-start">
                {items.map((t) => (
                    <span
                        key={t}
                        className="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-white/90 shadow-sm transition-colors group-hover:border-accent/30 group-hover:bg-accent/10"
                    >
                        {t}
                    </span>
                ))}
            </div>

            {/* Linha inferior glow */}
            <div className="pointer-events-none absolute inset-x-6 bottom-3 h-px bg-gradient-to-r from-transparent via-accent/40 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
        </div>
    </div>
);

export default function Skills() {
    return (
        <section id="skills" className="anchor-offset px-5 py-16 md:py-20">
            <h2 className="font-orbitron relative mb-12 text-center text-3xl md:text-4xl">
                Minhas Habilidades
                <span className="absolute -bottom-3 left-1/2 h-1 w-20 -translate-x-1/2 rounded bg-accent" />
            </h2>

            <div className="mx-auto grid max-w-6xl grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <SkillCard
                    title="Linguagens & Frameworks"
                    icon={FaCode}
                    items={[
                        'PHP',
                        'JavaScript',
                        'TypeScript',
                        'Python',
                        'SQL',
                        'HTML5',
                        'CSS3',
                        'JSON',
                        'Bash',
                        'Laravel',
                        'Django',
                        'React',
                        'Tailwind CSS',
                        'Bootstrap',
                        'Inertia.js',
                        'Vite',
                        'jQuery',
                    ]}
                />
                <SkillCard
                    title="Banco de Dados & APIs"
                    icon={FaDatabase}
                    items={[
                        'MySQL',
                        'PostgreSQL',
                        'SQLite',
                        'Redis',
                        'Firebase Auth',
                        'Firestore',
                        'Firebase Hosting',
                        'RESTful APIs',
                        'GraphQL',
                    ]}
                />
                <SkillCard
                    title="Ferramentas & DevOps"
                    icon={FaWrench}
                    items={[
                        'Git',
                        'GitHub',
                        'GitLab',
                        'Docker',
                        'Composer',
                        'NPM',
                        'Yarn',
                        'CI/CD',
                        'XAMPP',
                        'Adobe CS6',
                    ]}
                />
                <SkillCard
                    title="Infra & Segurança"
                    icon={FaShieldAlt}
                    items={[
                        'AWS',
                        'Coolify',
                        'Cloudflare',
                        'Hostinger VPS',
                        'HTTPS/SSL',
                        'OWASP',
                        'Otimização de Queries',
                        'Cache',
                        'Rate Limiting',
                    ]}
                />
            </div>
        </section>
    );
}
