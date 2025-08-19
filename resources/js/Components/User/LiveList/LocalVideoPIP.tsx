import clsx from 'clsx';
import { FiUser, FiVideoOff } from 'react-icons/fi';

interface LocalVideoPIPProps {
    isVideoOff: boolean;
    menuHeight?: number; // só usado no modo bottom
    position?: 'top' | 'bottom';
}

export default function LocalVideoPIP({
    isVideoOff,
    menuHeight = 0,
    position = 'bottom',
}: LocalVideoPIPProps) {
    const offsetFromFooter = menuHeight + 80;
    const isBottomPosition = position === 'bottom';

    // Determina o estilo baseado na posição
    const positionStyles = isBottomPosition
        ? { bottom: `${offsetFromFooter}px` }
        : {};

    // Define as classes para o contêiner
    const containerClasses = clsx(
        'z-20 overflow-hidden rounded-lg border-2 border-gray-600 bg-gray-800 shadow-lg',
        {
            'absolute right-4 h-32 w-24 md:h-40 md:w-32': isBottomPosition,
            'h-20 w-16': !isBottomPosition,
        },
    );

    // Define o ícone de vídeo ou usuário
    const iconSize = isBottomPosition
        ? isVideoOff
            ? 20
            : 24
        : isVideoOff
          ? 14
          : 18;
    const icon = isVideoOff ? (
        <FiVideoOff size={iconSize} className="opacity-50" />
    ) : (
        <FiUser size={iconSize} />
    );

    return (
        <div className={containerClasses} style={positionStyles}>
            <div
                className={clsx(
                    'flex h-full w-full items-center justify-center',
                    isVideoOff ? 'bg-gray-700' : 'bg-gray-900',
                )}
            >
                {icon}
            </div>
        </div>
    );
}
