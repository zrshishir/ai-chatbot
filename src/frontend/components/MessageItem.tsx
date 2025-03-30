import React from 'react';
import { Message } from '../types';

interface MessageItemProps {
  message: Message;
}

const MessageItem: React.FC<MessageItemProps> = ({ message }) => {
  const isUser = message.sender === 'user';

  return (
    <div className={`ai-chatbot-message ${isUser ? 'ai-chatbot-message-user' : 'ai-chatbot-message-bot'}`}>
      <div className="ai-chatbot-message-content">
        {message.content}
      </div>
      <div className="ai-chatbot-message-timestamp">
        {new Date(message.timestamp).toLocaleTimeString()}
      </div>
    </div>
  );
};

export default MessageItem; 