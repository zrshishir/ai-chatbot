import React, { useState, useRef, useEffect } from 'react';
import MessageList from './MessageList';
import MessageInput from './MessageInput';
import { Message } from '../types';

const ChatContainer: React.FC = () => {
    const [messages, setMessages] = useState<Message[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const handleSendMessage = async (content: string) => {
        if (!content.trim()) return;

        const userMessage: Message = {
            id: Date.now(),
            content,
            sender: 'user',
            timestamp: new Date().toISOString(),
        };

        setMessages((prev) => [...prev, userMessage]);
        setIsLoading(true);

        try {
            const response = await fetch('/wp-json/ai-chatbot/v1/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: content }),
            });

            if (!response.ok) {
                throw new Error('Failed to send message');
            }

            const data = await response.json();
            const botMessage: Message = {
                id: Date.now(),
                content: data.response,
                sender: 'bot',
                timestamp: new Date().toISOString(),
            };

            setMessages((prev) => [...prev, botMessage]);
        } catch (error) {
            console.error('Error sending message:', error);
            const errorMessage: Message = {
                id: Date.now(),
                content: 'Sorry, there was an error processing your message. Please try again.',
                sender: 'bot',
                timestamp: new Date().toISOString(),
            };
            setMessages((prev) => [...prev, errorMessage]);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="ai-chatbot-chat-container">
            <MessageList messages={messages} />
            <div ref={messagesEndRef} />
            <MessageInput onSendMessage={handleSendMessage} isLoading={isLoading} />
        </div>
    );
};

export default ChatContainer; 