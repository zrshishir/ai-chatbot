<?php

namespace AiChatbot\Shortcodes;

use AiChatbot\Services\ChatInterface;
use AiChatbot\Services\ChatController;
use AiChatbot\Services\EmbeddingGenerator;

class Chatbot
{
  /**
   * Chat interface instance
   *
   * @var ChatInterface
   */
  private $chat_interface;

  /**
   * Chat controller instance
   *
   * @var ChatController
   */
  private $chat_controller;

  /**
   * Embedding generator instance
   *
   * @var EmbeddingGenerator
   */
  private $embedding_generator;

  /**
   * Initialize the shortcode
   */
  public function init()
  {
    add_shortcode('ai_chatbot', [$this, 'render']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    add_action('wp_ajax_ai_chatbot_send_message', [$this, 'handle_message']);
    add_action('wp_ajax_nopriv_ai_chatbot_send_message', [$this, 'handle_message']);
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
        'assistant' => __('AI Assistant', 'ai-chatbot'),
        'online' => __('Online', 'ai-chatbot'),
        'toggleChat' => __('Toggle Chat', 'ai-chatbot'),
        'closeChat' => __('Close Chat', 'ai-chatbot'),
        'sendMessage' => __('Send Message', 'ai-chatbot'),
        'messageInput' => __('Message Input', 'ai-chatbot'),
        'placeholder' => __('Type your message...', 'ai-chatbot'),
        'thinking' => __('Thinking...', 'ai-chatbot'),
        'error' => __('Error: ', 'ai-chatbot'),
        'errorMessage' => __('Something went wrong. Please try again.', 'ai-chatbot'),
        'retry' => __('Retry', 'ai-chatbot'),
        'loginRequired' => __('Please log in to use the chatbot.', 'ai-chatbot')
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
    if (empty($message)) {
      wp_send_json_error(['message' => __('Message cannot be empty.', 'ai-chatbot')]);
    }

    try {
      $response = $this->chat_controller->generate_response($message);
      wp_send_json_success(['message' => $response]);
    } catch (\Exception $e) {
      wp_send_json_error(['message' => $e->getMessage()]);
    }
  }

  /**
   * Render the chat interface
   *
   * @return string
   */
  public function render()
  {
    if (!is_user_logged_in() && !get_option('ai_chatbot_public_access', false)) {
      return '<p>' . esc_html__('Please log in to use the chatbot.', 'ai-chatbot') . '</p>';
    }

    return '<div id="ai-chatbot-root" class="ai-chatbot-wp"></div>';
  }
}
