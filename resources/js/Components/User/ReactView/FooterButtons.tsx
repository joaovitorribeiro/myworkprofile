import { forwardRef } from 'react';

type FooterButtonsProps = {
    onLike: () => void;
    onBlock: () => void;
    onAnonymousMessage: () => void;
};

const FooterButtons = forwardRef<HTMLDivElement, FooterButtonsProps>(
    ({ onLike, onBlock, onAnonymousMessage }, ref) => {
        return (
            <div
                ref={ref}
                className="pointer-events-none fixed bottom-[calc(90px+env(safe-area-inset-bottom))] left-0 z-20 flex w-full items-center px-6"
            >
                <div className="pointer-events-auto mx-auto flex w-full max-w-md justify-center gap-6">
                    {/* Botão Bloquear */}
                    <button
                        onClick={onBlock}
                        aria-label="Bloquear usuário"
                        className="group relative flex flex-1 items-center justify-center rounded-full bg-gray-200 p-3 text-xl text-gray-700 shadow-sm transition hover:bg-gray-300 active:scale-95"
                    >
                        🚫
                        <span className="absolute -bottom-6 text-xs font-medium text-gray-300 opacity-0 transition group-hover:opacity-100">
                            Bloquear
                        </span>
                    </button>

                    {/* Botão Mensagem Anônima */}
                    <button
                        onClick={onAnonymousMessage}
                        aria-label="Enviar mensagem anônima"
                        className="group relative flex flex-1 items-center justify-center gap-1 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 p-3 text-sm font-medium text-white shadow-md transition hover:from-blue-400 hover:to-indigo-400 active:scale-95"
                    >
                        💌 Anônima
                    </button>

                    {/* Botão Curtir */}
                    <button
                        onClick={onLike}
                        aria-label="Curtir usuário"
                        className="group relative flex flex-1 items-center justify-center rounded-full bg-gradient-to-r from-pink-500 to-rose-500 p-3 text-xl text-white shadow-md transition hover:from-pink-400 hover:to-rose-400 active:scale-95"
                    >
                        ❤️
                        <span className="absolute -bottom-6 text-xs font-medium text-gray-300 opacity-0 transition group-hover:opacity-100">
                            Amei
                        </span>
                    </button>
                </div>
            </div>
        );
    },
);

FooterButtons.displayName = 'FooterButtons';
export default FooterButtons;
