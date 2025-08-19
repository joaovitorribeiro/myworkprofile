import type { UpgradeModalProps } from '@/types';

export default function UpgradeModal({ isOpen, onClose }: UpgradeModalProps) {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div className="rounded-lg bg-white p-6">
                <h2 className="text-lg font-bold">Upgrade para Premium</h2>
                <p className="mt-2 text-sm text-gray-600">
                    Desbloqueie recursos exclusivos e melhore sua experiência.
                </p>
                <button
                    onClick={onClose}
                    className="mt-4 rounded bg-yellow-500 px-4 py-2 text-white"
                >
                    Fechar
                </button>
            </div>
        </div>
    );
}
