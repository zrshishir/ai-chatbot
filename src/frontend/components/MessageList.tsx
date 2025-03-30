import React from 'react';
import { Message } from '../types';
import MessageItem from './MessageItem';

interface MessageListProps {
  messages: Message[];
}

const MessageList: React.FC<MessageListProps> = ({ messages }) => {
  return (
    <div className="ai-chatbot-message-list">
      {messages.map((message) => (
        <MessageItem key={message.id} message={message} />
      ))}
    </div>
  );
};

export default MessageList; 