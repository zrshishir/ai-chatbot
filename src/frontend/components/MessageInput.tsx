import React, { useState, KeyboardEvent } from 'react';

interface MessageInputProps {
  onSendMessage: (message: string) => void;
  isLoading: boolean;
}

const MessageInput: React.FC<MessageInputProps> = ({ onSendMessage, isLoading }) => {
  const [message, setMessage] = useState('');

  const handleSubmit = () => {
    if (message.trim() && !isLoading) {
      onSendMessage(message);
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
    <div className="ai-chatbot-input-container">
      <textarea
        className="ai-chatbot-input"
        value={message}
        onChange={(e) => setMessage(e.target.value)}
        onKeyPress={handleKeyPress}
        placeholder="Type your message..."
        disabled={isLoading}
      />
      <button
        className="ai-chatbot-send-button"
        onClick={handleSubmit}
        disabled={isLoading || !message.trim()}
      >
        {isLoading ? 'Sending...' : 'Send'}
      </button>
    </div>
  );
};

export default MessageInput; 