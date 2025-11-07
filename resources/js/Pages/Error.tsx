import { Head } from '@inertiajs/react';

type ErrorProps = {
    status?: number;
    message?: string;
};

export default function Error({ status = 500, message }: ErrorProps) {
    const friendlyMessage =
        message ??
        (status === 404
            ? 'Ops! A pagina que voce tentou acessar nao existe.'
            : 'Algo inesperado aconteceu. Tente novamente em instantes.');

    return (
        <>
            <Head>
                <title>Ocorreu um erro</title>
            </Head>
            <div className="min-h-screen flex flex-col items-center justify-center bg-slate-950 text-white px-6 text-center">
                <p className="text-sm uppercase tracking-[0.3em] text-slate-400">Erro {status}</p>
                <h1 className="mt-4 text-3xl font-semibold">{friendlyMessage}</h1>
                <p className="mt-2 text-slate-400">Caso o problema persista, entre em contato comigo para que eu possa ajudar.</p>
                <a
                    href="/"
                    className="mt-8 inline-flex items-center gap-2 rounded-full bg-cyan-500/10 px-6 py-3 text-cyan-300 ring-1 ring-cyan-500/30 transition hover:bg-cyan-500/20"
                >
                    Voltar para a página inicial
                </a>
            </div>
        </>
    );
}
