import React from 'react';

interface MessageProps {
    message: {
        content: string;
        type: 'user' | 'bot';
        timestamp: Date;
    };
}

export const Message: React.FC<MessageProps> = ({ message }) => {
    const isUser = message.type === 'user';
    const formattedTime = new Date(message.timestamp).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
    });

    return (
        <div className={`flex ${isUser ? 'justify-end' : 'justify-start'}`}>
            <div
                className={`max-w-[80%] rounded-lg p-3 ${
                    isUser
                        ? 'bg-blue-500 text-white rounded-br-none'
                        : 'bg-gray-100 text-gray-800 rounded-bl-none'
                }`}
            >
                <div className="text-sm whitespace-pre-wrap">{message.content}</div>
                <div
                    className={`text-xs mt-1 ${
                        isUser ? 'text-blue-100' : 'text-gray-500'
                    }`}
                >
                    {formattedTime}
                </div>
            </div>
        </div>
    );
}; 