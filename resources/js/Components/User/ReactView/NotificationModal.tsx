import type { NotificationModalProps } from './types';

export default function NotificationModal({
    isOpen,
    onClose,
}: NotificationModalProps) {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div className="rounded-lg bg-white p-6">
                <h2 className="text-lg font-bold">Notificações</h2>
                <p>Você tem novas mensagens!</p>
                <button
                    onClick={onClose}
                    className="mt-4 rounded bg-gray-500 px-4 py-2 text-white"
                >
                    Fechar
                </button>
            </div>
        </div>
    );
}
