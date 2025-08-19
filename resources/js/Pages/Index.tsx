import { Head } from '@inertiajs/react';
import About from '../Components/Landing/About';
import Contact from '../Components/Landing/Contact';
import Footer from '../Components/Landing/Footer';
import Hero from '../Components/Landing/Hero';
import Navbar from '../Components/Landing/Navbar';
import Projects from '../Components/Landing/Projects';
import Skills from '../Components/Landing/Skills';

export default function Index() {
    return (
        <>
            <Head>
                <title>João Vitor Ribeiro Tim - Desenvolvedor Full Stack | Laravel, React, PHP</title>
                <meta name="description" content="João Vitor Ribeiro Tim - Desenvolvedor Full Stack especializado em Laravel, React, PHP e JavaScript. Criação de sites, aplicações web e soluções digitais personalizadas no Paraná, Brasil." />
                <meta name="keywords" content="desenvolvedor full stack, programador, laravel, react, php, javascript, typescript, desenvolvimento web, sites, aplicações web, paraná, brasil, joão vitor" />
                <meta name="author" content="João Vitor Ribeiro Tim" />

                {/* Open Graph */}
                <meta property="og:title" content="João Vitor Ribeiro Tim - Desenvolvedor Full Stack" />
                <meta property="og:description" content="Desenvolvedor Full Stack especializado em Laravel, React, PHP e JavaScript. Criação de sites, aplicações web e soluções digitais personalizadas." />
                <meta property="og:type" content="website" />
                <meta property="og:url" content="https://devjoaovitor.com.br" />
                <meta property="og:image" content="https://devjoaovitor.com.br/assets/img/og-image.jpg" />

                {/* Twitter */}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content="João Vitor Ribeiro Tim - Desenvolvedor Full Stack" />
                <meta name="twitter:description" content="Desenvolvedor Full Stack especializado em Laravel, React, PHP e JavaScript. Criação de sites, aplicações web e soluções digitais personalizadas." />
                <meta name="twitter:image" content="https://devjoaovitor.com.br/assets/img/og-image.jpg" />

                {/* Additional SEO */}
                <link rel="canonical" href="https://devjoaovitor.com.br" />
                <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
                <meta name="googlebot" content="index, follow" />

                {/* Local Business Schema */}
                <script type="application/ld+json">
                {JSON.stringify({
                    "@context": "https://schema.org",
                    "@type": "LocalBusiness",
                    "name": "João Vitor Ribeiro Tim - Desenvolvedor Full Stack",
                    "description": "Desenvolvedor Full Stack especializado em Laravel, React, PHP e JavaScript",
                    "url": "https://devjoaovitor.com.br",
                    "logo": "https://devjoaovitor.com.br/assets/img/logo.png",
                    "image": "https://devjoaovitor.com.br/assets/img/eu.webp",
                    "address": {
                        "@type": "PostalAddress",
                        "addressRegion": "Paraná",
                        "addressCountry": "BR"
                    },
                    "contactPoint": {
                        "@type": "ContactPoint",
                        "telephone": "+55-43-99927-3285",
                        "contactType": "customer service",
                        "availableLanguage": "Portuguese"
                    },
                    "sameAs": [
                        "https://github.com/joaovitorribeiro",
                        "https://www.linkedin.com/in/devcodejoaovitor/"
                    ],
                    "serviceArea": {
                        "@type": "Country",
                        "name": "Brasil"
                    },
                    "hasOfferCatalog": {
                        "@type": "OfferCatalog",
                        "name": "Serviços de Desenvolvimento Web",
                        "itemListElement": [
                            {
                                "@type": "Offer",
                                "itemOffered": {
                                    "@type": "Service",
                                    "name": "Desenvolvimento de Sites",
                                    "description": "Criação de sites responsivos e modernos"
                                }
                            },
                            {
                                "@type": "Offer",
                                "itemOffered": {
                                    "@type": "Service",
                                    "name": "Aplicações Web",
                                    "description": "Desenvolvimento de aplicações web completas"
                                }
                            },
                            {
                                "@type": "Offer",
                                "itemOffered": {
                                    "@type": "Service",
                                    "name": "Consultoria em Tecnologia",
                                    "description": "Consultoria especializada em desenvolvimento web"
                                }
                            }
                        ]
                    }
                })}
                </script>
            </Head>
            <Navbar />
            <main id="content">
                <Hero />
                <About />
                <Skills />
                <Projects />
                <Contact />
            </main>
            <Footer />
        </>
    );
}
