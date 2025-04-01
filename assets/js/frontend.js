(function($) {
    'use strict';

    class AIChatbot {
        constructor() {
            this.container = $('#ai-chatbot-container');
            this.messagesContainer = $('.ai-chatbot-messages');
            this.input = $('.ai-chatbot-input textarea');
            this.sendButton = $('.ai-chatbot-send');
            this.closeButton = $('.ai-chatbot-close');
            this.sessionId = this.generateSessionId();

            this.bindEvents();
            this.initializeChat();
        }

        bindEvents() {
            this.sendButton.on('click', () => this.sendMessage());
            this.input.on('keypress', (e) => {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            this.closeButton.on('click', () => this.closeChat());
            this.input.on('input', () => this.adjustTextareaHeight());
        }

        generateSessionId() {
            return 'session_' + Math.random().toString(36).substr(2, 9);
        }

        initializeChat() {
            this.addMessage('assistant', aiChatbotData.i18n.welcome || 'Hello! How can I help you today?');
        }

        async sendMessage() {
            const message = this.input.val().trim();
            if (!message) return;

            // Clear input and reset height
            this.input.val('').css('height', 'auto');

            // Add user message
            this.addMessage('user', message);

            // Show thinking indicator
            this.addMessage('assistant', aiChatbotData.i18n.thinking, true);

            try {
                const response = await this.makeRequest(message);
                this.removeThinkingIndicator();
                this.addMessage('assistant', response.message);
            } catch (error) {
                this.removeThinkingIndicator();
                this.addMessage('assistant', aiChatbotData.i18n.error + error.message, false, true);
            }
        }

        async makeRequest(message) {
            const response = await $.ajax({
                url: aiChatbotData.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'ai_chatbot_send_message',
                    nonce: aiChatbotData.nonce,
                    message: message,
                    session_id: this.sessionId
                }
            });

            if (!response.success) {
                throw new Error(response.data.message);
            }

            return response.data;
        }

        addMessage(type, content, isThinking = false, isError = false) {
            const messageElement = $('<div>')
                .addClass(`ai-chatbot-message ai-chatbot-message-${type}`)
                .addClass(isThinking ? 'ai-chatbot-thinking' : '')
                .addClass(isError ? 'ai-chatbot-error' : '')
                .html(content);

            this.messagesContainer.append(messageElement);
            this.scrollToBottom();
        }

        removeThinkingIndicator() {
            this.messagesContainer.find('.ai-chatbot-thinking').remove();
        }

        scrollToBottom() {
            this.messagesContainer.scrollTop(this.messagesContainer[0].scrollHeight);
        }

        adjustTextareaHeight() {
            const textarea = this.input[0];
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        }

        closeChat() {
            this.container.addClass('ai-chatbot-closed');
        }
    }

    // Initialize chat when document is ready
    $(document).ready(() => {
        new AIChatbot();
    });

})(jQuery); 