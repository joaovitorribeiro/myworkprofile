import type { LocationModalProps } from './types';

export default function LocationModal({ isOpen, onClose }: LocationModalProps) {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div className="rounded-lg bg-white p-6">
                <h2 className="text-lg font-bold">Selecionar Localização</h2>
                {/* Aqui você pode colocar campos de filtro de localização */}
                <button
                    onClick={onClose}
                    className="mt-4 rounded bg-blue-500 px-4 py-2 text-white"
                >
                    Fechar
                </button>
            </div>
        </div>
    );
}
