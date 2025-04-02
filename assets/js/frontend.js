(function($) {
    'use strict';

    class ChatInterface {
        constructor() {
            this.container = $('#ai-chatbot-root');
            this.isOpen = false;
            this.init();
        }

        init() {
            this.render();
            this.bindEvents();
        }

        render() {
            const html = `
                <button class="ai-chatbot-toggle" aria-label="${aiChatbotData.i18n.toggleChat}">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </button>
                <div class="ai-chatbot-container">
                    <div class="ai-chatbot-header">
                        <div class="ai-chatbot-header-content">
                            <div class="ai-chatbot-avatar">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                            </div>
                            <div class="ai-chatbot-info">
                                <div class="ai-chatbot-name">${aiChatbotData.i18n.assistant}</div>
                                <div class="ai-chatbot-status">${aiChatbotData.i18n.online}</div>
                            </div>
                        </div>
                        <button class="ai-chatbot-close" aria-label="${aiChatbotData.i18n.closeChat}">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                <path d="M18 6L6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="ai-chatbot-messages"></div>

                    <div class="ai-chatbot-input">
                        <textarea
                            placeholder="${aiChatbotData.i18n.placeholder}"
                            rows="1"
                            aria-label="${aiChatbotData.i18n.messageInput}"></textarea>
                        <button class="ai-chatbot-send" aria-label="${aiChatbotData.i18n.sendMessage}">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                                <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
            `;

            this.container.html(html);
            this.toggleButton = this.container.find('.ai-chatbot-toggle');
            this.chatContainer = this.container.find('.ai-chatbot-container');
            this.messagesContainer = this.container.find('.ai-chatbot-messages');
            this.input = this.container.find('textarea');
            this.sendButton = this.container.find('.ai-chatbot-send');
            this.closeButton = this.container.find('.ai-chatbot-close');
        }

        bindEvents() {
            this.toggleButton.on('click', () => this.toggleChat());
            this.sendButton.on('click', () => this.handleSendMessage());
            this.input.on('keypress', (e) => {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    this.handleSendMessage();
                }
            });
            this.closeButton.on('click', () => this.closeChat());
            this.input.on('input', () => this.autoExpandTextarea());
        }

        toggleChat() {
            this.isOpen = !this.isOpen;
            this.toggleButton.toggleClass('active');
            this.chatContainer.toggleClass('active');
            if (this.isOpen) {
                this.input.focus();
            }
        }

        closeChat() {
            this.isOpen = false;
            this.toggleButton.removeClass('active');
            this.chatContainer.removeClass('active');
        }

        handleSendMessage() {
            const message = this.input.val().trim();
            if (!message || this.isProcessing) return;

            this.isProcessing = true;
            this.sendButton.prop('disabled', true);
            this.input.prop('disabled', true);

            this.appendMessage(message, 'user');
            this.input.val('');

            const typingIndicator = this.appendTypingIndicator();

            $.ajax({
                url: aiChatbotData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_chatbot_send_message',
                    message: message,
                    nonce: aiChatbotData.nonce
                },
                success: (response) => {
                    typingIndicator.remove();
                    if (response.success) {
                        this.appendMessage(response.data.message, 'assistant');
                    } else {
                        this.appendMessage(aiChatbotData.i18n.error + response.data.message, 'error');
                    }
                },
                error: () => {
                    typingIndicator.remove();
                    this.appendMessage(aiChatbotData.i18n.error + aiChatbotData.i18n.errorMessage, 'error');
                },
                complete: () => {
                    this.isProcessing = false;
                    this.sendButton.prop('disabled', false);
                    this.input.prop('disabled', false);
                    this.input.focus();
                }
            });
        }

        appendMessage(content, role) {
            const messageDiv = $('<div>', {
                class: `ai-chatbot-message ${role}`,
                html: `
                    <div class="ai-chatbot-message-content">${content}</div>
                    <div class="ai-chatbot-message-timestamp">${new Date().toLocaleTimeString()}</div>
                `
            });
            this.messagesContainer.append(messageDiv);
            this.messagesContainer.scrollTop(this.messagesContainer[0].scrollHeight);
        }

        appendTypingIndicator() {
            const typingDiv = $('<div>', {
                class: 'ai-chatbot-typing',
                html: `
                    <span></span>
                    <span></span>
                    <span></span>
                `
            });
            this.messagesContainer.append(typingDiv);
            this.messagesContainer.scrollTop(this.messagesContainer[0].scrollHeight);
            return typingDiv;
        }

        autoExpandTextarea() {
            this.input.css('height', 'auto');
            this.input.css('height', this.input[0].scrollHeight + 'px');
        }
    }

    // Initialize chat interface when document is ready
    $(document).ready(() => {
        new ChatInterface();
    });

})(jQuery); 