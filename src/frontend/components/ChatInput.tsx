import React, { useState, KeyboardEvent } from 'react';

interface ChatInputProps {
    onSend: (message: string) => void;
    disabled?: boolean;
}

export const ChatInput: React.FC<ChatInputProps> = ({ onSend, disabled }) => {
    const [message, setMessage] = useState('');

    const handleSubmit = () => {
        if (message.trim() && !disabled) {
            onSend(message);
            setMessage('');
        }
    };

    const handleKeyPress = (e: KeyboardEvent<HTMLTextAreaElement>) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSubmit();
        }
    };

    return (
        <div className="flex space-x-2">
            <textarea
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                onKeyPress={handleKeyPress}
                placeholder="Type your message..."
                disabled={disabled}
                className="flex-1 resize-none rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                rows={1}
            />
            <button
                onClick={handleSubmit}
                disabled={!message.trim() || disabled}
                className="rounded-lg bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
                Send
            </button>
        </div>
    );
}; 