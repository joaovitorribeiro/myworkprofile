// resources/js/Components/User/LiveList/ChatPanel.tsx
import { MutableRefObject } from 'react';
import { FiSend, FiX } from 'react-icons/fi';

interface ChatPanelProps {
    messages: { text: string; isMe: boolean }[];
    newMessage: string;
    setNewMessage: (value: string) => void;
    sendMessage: () => void;
    chatContainerRef: MutableRefObject<HTMLDivElement | null>;
    setShowChat: (value: boolean) => void;
}

export default function ChatPanel({
    messages,
    newMessage,
    setNewMessage,
    sendMessage,
    chatContainerRef,
    setShowChat,
}: ChatPanelProps) {
    return (
        <div
            className="fixed bottom-20 left-0 right-0 z-10 border-t border-gray-700 bg-gray-800 transition-all duration-300"
            style={{ height: '30vh', maxHeight: '300px' }}
        >
            <div className="flex items-center justify-between border-b border-gray-700 px-4 py-2">
                <span className="font-medium">Chat</span>
                <button
                    type="button"
                    onClick={() => setShowChat(false)}
                    className="rounded-full p-1 hover:bg-gray-700"
                    aria-label="Fechar chat"
                >
                    <FiX size={18} />
                </button>
            </div>
            <div
                ref={chatContainerRef}
                className="h-[calc(100%-48px-40px)] space-y-3 overflow-y-auto p-4"
            >
                {messages.map((msg, index) => (
                    <div
                        key={index}
                        className={`flex ${msg.isMe ? 'justify-end' : 'justify-start'}`}
                    >
                        <div
                            className={`max-w-xs rounded-lg px-4 py-2 ${
                                msg.isMe
                                    ? 'rounded-br-none bg-purple-600 text-white'
                                    : 'rounded-bl-none bg-gray-700 text-white'
                            }`}
                        >
                            {msg.text}
                        </div>
                    </div>
                ))}
            </div>
            <div className="absolute bottom-0 left-0 right-0 border-t border-gray-700 bg-gray-900 p-2">
                <div className="flex gap-2">
                    <input
                        type="text"
                        value={newMessage}
                        onChange={(e) => setNewMessage(e.target.value)}
                        onKeyPress={(e) => e.key === 'Enter' && sendMessage()}
                        placeholder="Digite uma mensagem..."
                        className="flex-1 rounded-full bg-gray-700 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                    <button
                        type="button"
                        onClick={sendMessage}
                        className="rounded-full bg-purple-600 p-2 text-white transition-colors hover:bg-purple-700"
                        aria-label="Enviar mensagem"
                    >
                        <FiSend size={18} />
                    </button>
                </div>
            </div>
        </div>
    );
}
