import type { LikeFeedbackProps } from './types';

export default function LikeFeedback({ isLiked, name }: LikeFeedbackProps) {
    if (!isLiked) return null;

    return (
        <div className="absolute inset-0 z-50 flex items-center justify-center">
            <div className="animate-like-pop rounded-full bg-black/50 px-6 py-3">
                <span className="text-lg font-bold text-white">
                    Você curtiu {name}! ❤️
                </span>
            </div>
        </div>
    );
}
