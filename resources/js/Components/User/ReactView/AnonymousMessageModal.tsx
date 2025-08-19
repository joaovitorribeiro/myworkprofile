import type { AnonymousMessageModalProps } from './types';

export default function AnonymousMessageModal({
    isOpen,
    onClose,
    onSend,
    message,
    setMessage,
}: AnonymousMessageModalProps) {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
            <div className="w-full max-w-md rounded-lg bg-white p-6 shadow-lg">
                <h2 className="mb-4 text-lg font-semibold text-gray-800">
                    Enviar mensagem anônima
                </h2>

                <textarea
                    rows={4}
                    className="w-full resize-none rounded-md border border-gray-300 p-2 text-sm focus:border-indigo-500 focus:outline-none"
                    placeholder="Digite sua mensagem..."
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                />

                <div className="mt-4 flex justify-end gap-2">
                    <button
                        onClick={onClose}
                        className="rounded bg-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-300"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={() => onSend(message)}
                        className="rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"
                    >
                        Enviar
                    </button>
                </div>
            </div>
        </div>
    );
}
