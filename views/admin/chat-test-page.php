<?php
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="wrap">
  <h1><?php echo esc_html__('Chat Test', 'ai-chatbot'); ?></h1>

  <div class="ai-chatbot-test-container">
    <!-- Chat Interface Test -->
    <div class="ai-chatbot-test-section">
      <h2><?php echo esc_html__('Chat Interface Test', 'ai-chatbot'); ?></h2>
      <div class="ai-chatbot-test-box">
        <?php echo $ai_chatbot->get_chat_interface()->render(); ?>
      </div>
    </div>

    <!-- Chat History Test -->
    <div class="ai-chatbot-test-section">
      <h2><?php echo esc_html__('Chat History Test', 'ai-chatbot'); ?></h2>
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
          <table class="wp-list-table widefat fixed striped">
            <thead>
              <tr>
                <th><?php echo esc_html__('Session ID', 'ai-chatbot'); ?></th>
                <th><?php echo esc_html__('Messages', 'ai-chatbot'); ?></th>
                <th><?php echo esc_html__('Last Updated', 'ai-chatbot'); ?></th>
                <th><?php echo esc_html__('Actions', 'ai-chatbot'); ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_sessions as $session) : ?>
                <tr>
                  <td><?php echo esc_html($session->session_id); ?></td>
                  <td><?php echo esc_html($session->message_count); ?></td>
                  <td><?php echo esc_html($session->updated_at); ?></td>
                  <td>
                    <button class="button view-messages" data-session-id="<?php echo esc_attr($session->session_id); ?>">
                      <?php echo esc_html__('View Messages', 'ai-chatbot'); ?>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else : ?>
          <p><?php echo esc_html__('No chat sessions found.', 'ai-chatbot'); ?></p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Chat Controller Test -->
    <div class="ai-chatbot-test-section">
      <h2><?php echo esc_html__('Chat Controller Test', 'ai-chatbot'); ?></h2>
      <div class="ai-chatbot-test-box">
        <form id="ai-chatbot-test-form" class="ai-chatbot-test-form">
          <p>
            <label for="test-message"><?php echo esc_html__('Test Message:', 'ai-chatbot'); ?></label>
            <textarea id="test-message" name="message" rows="3" class="large-text"></textarea>
          </p>
          <p>
            <button type="submit" class="button button-primary">
              <?php echo esc_html__('Send Test Message', 'ai-chatbot'); ?>
            </button>
          </p>
        </form>
        <div id="test-response" class="ai-chatbot-test-response"></div>
      </div>
    </div>
  </div>
</div>

<style>
  .ai-chatbot-test-container {
    margin-top: 20px;
  }

  .ai-chatbot-test-section {
    margin-bottom: 30px;
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
  }

  .ai-chatbot-test-box {
    margin-top: 15px;
  }

  .ai-chatbot-test-form {
    max-width: 600px;
  }

  .ai-chatbot-test-response {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #e2e4e7;
    display: none;
  }

  .ai-chatbot-test-response.show {
    display: block;
  }

  .view-messages {
    margin-right: 5px;
  }

  /* Chat interface test box specific styles */
  .ai-chatbot-test-box .ai-chatbot-container {
    position: relative;
    height: 400px;
    margin: 20px 0;
  }
</style>

<script>
  jQuery(document).ready(function($) {
    // Handle test message submission
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

    // Handle view messages button
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

<style>
  .ai-chatbot-modal {
    display: block;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
  }

  .ai-chatbot-modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    max-height: 70vh;
    overflow-y: auto;
    position: relative;
  }

  .ai-chatbot-modal .close {
    position: absolute;
    right: 10px;
    top: 5px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
  }

  .messages-list {
    margin-top: 20px;
  }

  .message {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 5px;
  }

  .message.user {
    background-color: #e3f2fd;
    margin-left: 20px;
  }

  .message.assistant {
    background-color: #f5f5f5;
    margin-right: 20px;
  }

  .message strong {
    display: block;
    margin-bottom: 5px;
  }

  .message p {
    margin: 0;
  }
</style>