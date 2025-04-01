<?php

namespace AiChatbot\Services;

/**
 * Class ChatInterface
 * 
 * Handles the chat interface and interactions
 */
class ChatInterface
{
  /**
   * Chat history model
   *
   * @var \AiChatbot\Models\ChatHistory
   */
  private $chat_history;

  /**
   * Chat controller
   *
   * @var ChatController
   */
  private $chat_controller;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->chat_history = new \AiChatbot\Models\ChatHistory();
    $this->chat_controller = new ChatController();
  }

  /**
   * Initialize the chat interface
   */
  public function init()
  {
    add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    add_action('wp_ajax_ai_chatbot_send_message', [$this, 'handle_message']);
    add_action('wp_ajax_nopriv_ai_chatbot_send_message', [$this, 'handle_message']);
    add_action('wp_ajax_ai_chatbot_get_messages', [$this, 'get_messages']);
  }

  /**
   * Enqueue frontend scripts
   */
  public function enqueue_scripts()
  {
    wp_enqueue_script(
      'ai-chatbot-frontend',
      AICB_ASSETS_URL . '/js/frontend.js',
      ['jquery'],
      AICB_VERSION,
      true
    );

    wp_localize_script('ai-chatbot-frontend', 'aiChatbotData', [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('ai_chatbot_nonce'),
      'i18n' => [
        'sendMessage' => __('Send Message', 'ai-chatbot'),
        'thinking' => __('Thinking...', 'ai-chatbot'),
        'error' => __('Error: ', 'ai-chatbot'),
        'retry' => __('Retry', 'ai-chatbot')
      ]
    ]);
  }

  /**
   * Enqueue frontend styles
   */
  public function enqueue_styles()
  {
    wp_enqueue_style(
      'ai-chatbot-frontend',
      AICB_ASSETS_URL . '/css/frontend.css',
      [],
      AICB_VERSION
    );
  }

  /**
   * Handle incoming chat messages
   */
  public function handle_message()
  {
    check_ajax_referer('ai_chatbot_nonce', 'nonce');

    $message = sanitize_textarea_field($_POST['message'] ?? '');
    $session_id = sanitize_text_field($_POST['session_id'] ?? '');

    if (empty($message)) {
      wp_send_json_error(['message' => __('Message cannot be empty', 'ai-chatbot')]);
    }

    try {
      // Save user message
      $this->chat_history->add_message($session_id, 'user', $message);

      // Get AI response using the chat controller
      $response = $this->chat_controller->generate_response($message);

      // Save AI response
      $this->chat_history->add_message($session_id, 'assistant', $response);

      wp_send_json_success([
        'message' => $response,
        'session_id' => $session_id
      ]);
    } catch (\Exception $e) {
      wp_send_json_error([
        'message' => $e->getMessage()
      ]);
    }
  }

  /**
   * Get messages for a chat session
   */
  public function get_messages()
  {
    check_ajax_referer('ai_chatbot_nonce', 'nonce');

    $session_id = sanitize_text_field($_POST['session_id'] ?? '');

    if (empty($session_id)) {
      wp_send_json_error(['message' => __('Session ID is required', 'ai-chatbot')]);
    }

    try {
      $messages = $this->chat_history->get_history($session_id);
      wp_send_json_success(['messages' => $messages]);
    } catch (\Exception $e) {
      wp_send_json_error([
        'message' => $e->getMessage()
      ]);
    }
  }

  /**
   * Render the chat interface
   *
   * @return string HTML for the chat interface
   */
  public function render()
  {
    ob_start();
?>
    <div id="ai-chatbot-container" class="ai-chatbot-container">
      <div class="ai-chatbot-header">
        <h3><?php echo esc_html__('AI Chatbot', 'ai-chatbot'); ?></h3>
        <button class="ai-chatbot-close">&times;</button>
      </div>

      <div class="ai-chatbot-messages"></div>

      <div class="ai-chatbot-input">
        <textarea
          placeholder="<?php echo esc_attr__('Type your message...', 'ai-chatbot'); ?>"
          rows="1"></textarea>
        <button class="ai-chatbot-send">
          <?php echo esc_html__('Send', 'ai-chatbot'); ?>
        </button>
      </div>
    </div>
<?php
    return ob_get_clean();
  }
}
