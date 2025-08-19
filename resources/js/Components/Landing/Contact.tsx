// resources/js/Pages/Contact.tsx
import {
    FaEnvelope,
    FaGithub,
    FaInstagram,
    FaLinkedinIn,
    FaMapMarkerAlt,
    FaPaperPlane,
    FaWhatsapp,
} from 'react-icons/fa';

export default function Contact() {
    return (
        <section id="contact" className="anchor-offset px-5 py-16 md:py-24">
            <h2 className="font-orbitron relative mb-6 text-center text-3xl md:text-4xl">
                Entre em Contato
                <span className="absolute -bottom-3 left-1/2 h-1 w-20 -translate-x-1/2 rounded bg-accent" />
            </h2>

            <div className="mx-auto max-w-3xl text-center">
                <p className="mx-auto mb-10 max-w-2xl text-white/90">
                    Bora conversar sobre seu projeto? Me chame por onde preferir
                    👇
                </p>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                    <a
                        href="mailto:joaovitor@solutionsites.com.br"
                        className="card-glass group relative rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur transition-transform duration-300 hover:-translate-y-1 hover:border-accent/60"
                    >
                        <div className="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-full bg-accent/10 text-accent transition-transform group-hover:scale-110">
                            <FaEnvelope className="text-2xl" />
                        </div>
                        <h4 className="font-semibold">Email</h4>
                        <p className="break-all text-white/80">
                            joaovitor@solutionsites.com.br
                        </p>
                    </a>

                    <a
                        href="https://wa.me/5543999273285?text=Oi!%20Vi%20seu%20site%20DevJoaoVitor%20e%20queria%20tirar%20algumas%20d%C3%BAvidas%20com%20voc%C3%AA."
                        target="_blank"
                        rel="noreferrer"
                        className="card-glass group relative rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur transition-transform duration-300 hover:-translate-y-1 hover:border-accent/60"
                    >
                        <div className="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-full bg-accent/10 text-accent transition-transform group-hover:scale-110">
                            <FaWhatsapp className="text-2xl" />
                        </div>
                        <h4 className="font-semibold">WhatsApp</h4>
                        <p className="text-white/80">+55 43 99927-3285</p>
                    </a>

                    <div className="card-glass group relative rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur transition-transform duration-300 hover:-translate-y-1 hover:border-accent/60">
                        <div className="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-full bg-accent/10 text-accent transition-transform group-hover:scale-110">
                            <FaMapMarkerAlt className="text-2xl" />
                        </div>
                        <h4 className="font-semibold">Localização</h4>
                        <p className="text-white/80">Paraná, Brasil</p>
                    </div>
                </div>

                <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a
                        href="https://wa.me/5543999273285?text=Oi!%20Vi%20seu%20site%20DevJoaoVitor%20e%20queria%20tirar%20algumas%20d%C3%BAvidas%20com%20voc%C3%AA."
                        target="_blank"
                        rel="noreferrer"
                        className="shadow-glow inline-flex items-center gap-3 rounded-full bg-gradient-to-r from-primary to-secondary px-7 py-3 font-semibold transition-transform hover:-translate-y-1"
                    >
                        <FaWhatsapp className="text-lg" />
                        Falar no WhatsApp
                    </a>

                    <a
                        href="mailto:joaovitor@solutionsites.com.br?subject=Projeto&body=Olá%20João%2C%20vamos%20falar%20sobre%20um%20projeto!"
                        className="inline-flex items-center gap-3 rounded-full border border-accent/60 bg-white/5 px-7 py-3 font-semibold text-accent backdrop-blur transition-transform hover:-translate-y-1"
                    >
                        <FaPaperPlane className="text-lg" />
                        Enviar Email
                    </a>
                </div>

                <div className="mt-10 flex items-center justify-center gap-3">
                    <a
                        href="https://github.com/joaovitorribeiro"
                        target="_blank"
                        rel="noreferrer"
                        className="hover:text-dark grid h-12 w-12 place-items-center rounded-full bg-white/10 text-accent transition-transform hover:-translate-y-1 hover:bg-accent"
                        aria-label="GitHub"
                    >
                        <FaGithub />
                    </a>
                    <a
                        href="https://www.linkedin.com/in/devcodejoaovitor/"
                        target="_blank"
                        rel="noreferrer"
                        className="hover:text-dark grid h-12 w-12 place-items-center rounded-full bg-white/10 text-accent transition-transform hover:-translate-y-1 hover:bg-accent"
                        aria-label="LinkedIn"
                    >
                        <FaLinkedinIn />
                    </a>
                    <a
                        href="https://instagram.com/"
                        target="_blank"
                        rel="noreferrer"
                        className="hover:text-dark grid h-12 w-12 place-items-center rounded-full bg-white/10 text-accent transition-transform hover:-translate-y-1 hover:bg-accent"
                        aria-label="Instagram"
                    >
                        <FaInstagram />
                    </a>
                </div>
            </div>
        </section>
    );
}
