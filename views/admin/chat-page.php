<?php
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="wrap">
  <h1><?php echo esc_html__('Dashboard', 'ai-chatbot'); ?></h1>

  <div class="ai-chatbot-test-container">
    <!-- Chat Interface -->
    <div class="ai-chatbot-test-section">
      <h2><?php echo esc_html__('Chat Interface', 'ai-chatbot'); ?></h2>
      <div class="ai-chatbot-test-box">
        <?php echo $ai_chatbot->get_chat_interface()->render(); ?>
      </div>
    </div>

    <!-- Chat History -->
    <div class="ai-chatbot-test-section">
      <h2><?php echo esc_html__('Chat History', 'ai-chatbot'); ?></h2>
      <div class="ai-chatbot-test-box">
        <h3><?php echo esc_html__('Recent Chat Sessions', 'ai-chatbot'); ?></h3>
        <?php
        global $wpdb;
        $sessions_table = $wpdb->prefix . 'aicb_chat_sessions';
        $messages_table = $wpdb->prefix . 'aicb_chat_messages';

        $recent_sessions = $wpdb->get_results(
          "SELECT s.*, COUNT(m.id) as message_count 
                    FROM $sessions_table s 
                    LEFT JOIN $messages_table m ON s.session_id = m.session_id 
                    GROUP BY s.id 
                    ORDER BY s.updated_at DESC 
                    LIMIT 5"
        );

        if (!empty($recent_sessions)) :
        ?>
          <div class="chat-sessions-list">
            <?php foreach ($recent_sessions as $session) : ?>
              <div class="chat-session-item">
                <div class="session-info">
                  <span class="session-id"><?php echo esc_html($session->session_id); ?></span>
                  <span class="message-count"><?php echo esc_html($session->message_count); ?> messages</span>
                  <span class="last-updated"><?php echo esc_html($session->updated_at); ?></span>
                </div>
                <button class="button view-messages" data-session-id="<?php echo esc_attr($session->session_id); ?>">
                  <?php echo esc_html__('View Messages', 'ai-chatbot'); ?>
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else : ?>
          <p class="no-sessions"><?php echo esc_html__('No chat sessions found.', 'ai-chatbot'); ?></p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Chat Controller -->
    <!-- <div class="ai-chatbot-test-section">
      <h2><?php echo esc_html__('Message Controller', 'ai-chatbot'); ?></h2>
      <div class="ai-chatbot-test-box">
        <form id="ai-chatbot-test-form" class="ai-chatbot-test-form">
          <p>
            <label for="test-message"><?php echo esc_html__('Message:', 'ai-chatbot'); ?></label>
            <textarea id="test-message" name="message" rows="3" class="large-text"></textarea>
          </p>
          <p>
            <button type="submit" class="button button-primary">
              <?php echo esc_html__('Send Message', 'ai-chatbot'); ?>
            </button>
          </p>
        </form>
        <div id="test-response" class="ai-chatbot-test-response"></div>
      </div>
    </div> -->
  </div>
</div>

<style>
  .ai-chatbot-test-container {
    margin-top: 20px;
    max-width: 1200px;
  }

  .ai-chatbot-test-section {
    margin-bottom: 30px;
    background: #fff;
    padding: 25px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
    border-radius: 4px;
  }

  .ai-chatbot-test-section h2 {
    margin-top: 0;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
    color: #1d2327;
    font-size: 1.3em;
  }

  .ai-chatbot-test-box {
    margin-top: 20px;
  }

  .ai-chatbot-test-box h3 {
    margin-top: 0;
    color: #50575e;
    font-size: 1.1em;
  }

  .ai-chatbot-test-form {
    max-width: 600px;
  }

  .ai-chatbot-test-form textarea {
    margin: 10px 0;
    width: 100%;
  }

  .ai-chatbot-test-response {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #e2e4e7;
    border-radius: 4px;
    display: none;
  }

  .ai-chatbot-test-response.show {
    display: block;
  }

  /* Chat sessions list styles */
  .chat-sessions-list {
    margin-top: 15px;
  }

  .chat-session-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    background: #f8f9fa;
    border: 1px solid #e2e4e7;
    border-radius: 4px;
    margin-bottom: 10px;
  }

  .chat-session-item:last-child {
    margin-bottom: 0;
  }

  .session-info {
    display: flex;
    align-items: center;
    gap: 20px;
  }

  .session-id {
    font-family: monospace;
    color: #50575e;
  }

  .message-count {
    color: #646970;
  }

  .last-updated {
    color: #646970;
    font-size: 0.9em;
  }

  .view-messages {
    margin-left: 15px;
  }

  .no-sessions {
    color: #646970;
    font-style: italic;
    padding: 15px;
    background: #f8f9fa;
    border: 1px dashed #e2e4e7;
    border-radius: 4px;
    text-align: center;
  }

  /* Chat interface styles */
  .ai-chatbot-container {
    position: relative;
    height: 600px;
    margin: 20px 0;
    border: 1px solid #e2e4e7;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    background: #f0f2f5;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .ai-chatbot-header {
    background: #ffffff;
    padding: 16px 20px;
    border-bottom: 1px solid #e4e6eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .ai-chatbot-header-content {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .ai-chatbot-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #0084ff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
  }

  .ai-chatbot-avatar svg {
    width: 24px;
    height: 24px;
  }

  .ai-chatbot-info {
    display: flex;
    flex-direction: column;
  }

  .ai-chatbot-name {
    font-weight: 600;
    color: #1c1e21;
    font-size: 1.1em;
  }

  .ai-chatbot-status {
    font-size: 0.85em;
    color: #65676b;
  }

  .ai-chatbot-close {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: #65676b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
  }

  .ai-chatbot-close:hover {
    background: #f2f2f2;
    color: #1c1e21;
  }

  .ai-chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .ai-chatbot-message {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 18px;
    position: relative;
    line-height: 1.4;
    font-size: 0.95em;
  }

  .ai-chatbot-message.user {
    background: #0084ff;
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
    margin-left: 40px;
  }

  .ai-chatbot-message.assistant {
    background: white;
    color: #1c1e21;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
    margin-right: 40px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  }

  .ai-chatbot-input {
    background: white;
    padding: 16px 20px;
    border-top: 1px solid #e4e6eb;
    display: flex;
    align-items: flex-end;
    gap: 12px;
  }

  .ai-chatbot-input textarea {
    flex: 1;
    border: 1px solid #e4e6eb;
    border-radius: 24px;
    padding: 12px 16px;
    resize: none;
    min-height: 44px;
    max-height: 120px;
    font-family: inherit;
    font-size: 0.95em;
    line-height: 1.4;
    margin: 0;
    transition: border-color 0.2s;
  }

  .ai-chatbot-input textarea:focus {
    outline: none;
    border-color: #0084ff;
  }

  .ai-chatbot-send {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: #0084ff;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
    flex-shrink: 0;
  }

  .ai-chatbot-send:hover {
    background: #0073e6;
  }

  .ai-chatbot-send:disabled {
    background: #e4e6eb;
    cursor: not-allowed;
  }

  .ai-chatbot-send svg {
    width: 20px;
    height: 20px;
    transform: translateX(1px);
  }

  /* Scrollbar styles */
  .ai-chatbot-messages::-webkit-scrollbar {
    width: 6px;
  }

  .ai-chatbot-messages::-webkit-scrollbar-track {
    background: transparent;
  }

  .ai-chatbot-messages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
  }

  .ai-chatbot-messages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }

  /* Message timestamp */
  .ai-chatbot-message-timestamp {
    font-size: 0.75em;
    opacity: 0.7;
    margin-top: 4px;
    text-align: right;
  }

  /* Typing indicator */
  .ai-chatbot-typing {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 12px 16px;
    background: white;
    border-radius: 18px;
    width: fit-content;
    margin: 8px 0;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    align-self: flex-start;
  }

  .ai-chatbot-typing span {
    width: 8px;
    height: 8px;
    background: #0084ff;
    border-radius: 50%;
    display: inline-block;
    animation: typing 1s infinite;
  }

  .ai-chatbot-typing span:nth-child(2) {
    animation-delay: 0.2s;
  }

  .ai-chatbot-typing span:nth-child(3) {
    animation-delay: 0.4s;
  }

  @keyframes typing {

    0%,
    100% {
      transform: translateY(0);
    }

    50% {
      transform: translateY(-4px);
    }
  }

  /* Add error message style */
  .ai-chatbot-message.error {
    background: #ffebee;
    color: #c62828;
    align-self: center;
    margin: 10px 40px;
    font-size: 0.9em;
  }

  /* Disabled state styles */
  .ai-chatbot-input textarea:disabled {
    background-color: #f8f9fa;
    cursor: not-allowed;
  }

  .ai-chatbot-send:disabled {
    background-color: #e4e6eb;
    cursor: not-allowed;
  }

  /* Message content styles */
  .ai-chatbot-message-content {
    white-space: pre-wrap;
    word-break: break-word;
  }
</style>

<script>
  jQuery(document).ready(function($) {
    // Chat interface functionality
    const chatMessages = $('.ai-chatbot-messages');
    const chatInput = $('.ai-chatbot-input textarea');
    const sendButton = $('.ai-chatbot-send');
    let isProcessing = false;

    function appendMessage(content, role) {
      const messageDiv = $('<div>', {
        class: `ai-chatbot-message ${role}`,
        html: `
          <div class="ai-chatbot-message-content">${content}</div>
          <div class="ai-chatbot-message-timestamp">${new Date().toLocaleTimeString()}</div>
        `
      });
      chatMessages.append(messageDiv);
      chatMessages.scrollTop(chatMessages[0].scrollHeight);
    }

    function appendTypingIndicator() {
      const typingDiv = $('<div>', {
        class: 'ai-chatbot-typing',
        html: `
          <span></span>
          <span></span>
          <span></span>
        `
      });
      chatMessages.append(typingDiv);
      chatMessages.scrollTop(chatMessages[0].scrollHeight);
      return typingDiv;
    }

    function handleSendMessage() {
      const message = chatInput.val().trim();
      if (!message || isProcessing) return;

      isProcessing = true;
      sendButton.prop('disabled', true);
      chatInput.prop('disabled', true);

      // Append user message
      appendMessage(message, 'user');
      chatInput.val('');

      // Show typing indicator
      const typingIndicator = appendTypingIndicator();

      // Send to AI
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'ai_chatbot_send_message',
          message: message,
          nonce: '<?php echo wp_create_nonce('ai_chatbot_nonce'); ?>'
        },
        success: function(response) {
          typingIndicator.remove();
          if (response.success) {
            appendMessage(response.data.message, 'assistant');
          } else {
            appendMessage('Error: ' + response.data.message, 'error');
          }
        },
        error: function() {
          typingIndicator.remove();
          appendMessage('<?php echo esc_js(__('Error: Failed to get response from the AI.', 'ai-chatbot')); ?>', 'error');
        },
        complete: function() {
          isProcessing = false;
          sendButton.prop('disabled', false);
          chatInput.prop('disabled', false);
          chatInput.focus();
        }
      });
    }

    // Handle send button click
    sendButton.on('click', handleSendMessage);

    // Handle enter key
    chatInput.on('keypress', function(e) {
      if (e.which === 13 && !e.shiftKey) {
        e.preventDefault();
        handleSendMessage();
      }
    });

    // Auto-expand textarea
    chatInput.on('input', function() {
      this.style.height = 'auto';
      this.style.height = (this.scrollHeight) + 'px';
    });

    // Handle test message submission (existing code)
    $('#ai-chatbot-test-form').on('submit', function(e) {
      e.preventDefault();
      var message = $('#test-message').val();
      var responseDiv = $('#test-response');

      responseDiv.html('<p class="loading"><?php echo esc_js(__('Processing...', 'ai-chatbot')); ?></p>').show();

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'ai_chatbot_send_message',
          message: message,
          nonce: '<?php echo wp_create_nonce('ai_chatbot_nonce'); ?>'
        },
        success: function(response) {
          if (response.success) {
            responseDiv.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
          } else {
            responseDiv.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
          }
        },
        error: function() {
          responseDiv.html('<div class="notice notice-error"><p><?php echo esc_js(__('Error occurred while processing the request.', 'ai-chatbot')); ?></p></div>');
        }
      });
    });

    // Handle view messages button (existing code)
    $('.view-messages').on('click', function() {
      var sessionId = $(this).data('session-id');
      var messages = $('<div class="messages-content"></div>');

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'ai_chatbot_get_messages',
          session_id: sessionId,
          nonce: '<?php echo wp_create_nonce('ai_chatbot_nonce'); ?>'
        },
        success: function(response) {
          if (response.success) {
            var html = '<div class="messages-list">';
            response.data.messages.forEach(function(msg) {
              html += '<div class="message ' + msg.role + '">';
              html += '<strong>' + msg.role + ':</strong> ';
              html += '<p>' + msg.content + '</p>';
              html += '</div>';
            });
            html += '</div>';
            messages.html(html);
          } else {
            messages.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
          }
        },
        error: function() {
          messages.html('<div class="notice notice-error"><p><?php echo esc_js(__('Error occurred while fetching messages.', 'ai-chatbot')); ?></p></div>');
        }
      });

      // Show messages in a modal
      var modal = $('<div class="ai-chatbot-modal"></div>').appendTo('body');
      modal.html('<div class="ai-chatbot-modal-content"><span class="close">&times;</span>' + messages.html() + '</div>');

      modal.find('.close').on('click', function() {
        modal.remove();
      });
    });
  });
</script>