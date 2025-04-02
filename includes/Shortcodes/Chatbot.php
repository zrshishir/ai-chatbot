<?php

namespace AiChatbot\Shortcodes;

use AiChatbot\Core\Plugin;

class Chatbot
{
  /**
   * Initialize the shortcode.
   *
   * @return void
   */
  public function init()
  {
    add_shortcode('ai_chatbot', [$this, 'render']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
  }

  /**
   * Enqueue the required scripts and styles.
   *
   * @return void
   */
  public function enqueue_scripts()
  {
    // Enqueue React and ReactDOM
    wp_enqueue_script(
      'react',
      'https://unpkg.com/react@18/umd/react.production.min.js',
      [],
      '18.0.0',
      true
    );

    wp_enqueue_script(
      'react-dom',
      'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js',
      ['react'],
      '18.0.0',
      true
    );

    // Enqueue our bundled app
    wp_enqueue_script(
      'ai-chatbot-frontend',
      Plugin::get_plugin_url() . 'build/frontend.js',
      ['react', 'react-dom'],
      Plugin::get_version(),
      true
    );

    wp_enqueue_style(
      'ai-chatbot-frontend',
      Plugin::get_plugin_url() . 'build/frontend.css',
      [],
      Plugin::get_version()
    );

    // Pass data to JavaScript
    wp_localize_script('ai-chatbot-frontend', 'aiChatbotData', [
      'apiUrl' => rest_url('ai-chatbot/v1'),
      'nonce' => wp_create_nonce('wp_rest'),
      'settings' => [
        'provider' => get_option('ai_chatbot_provider', 'openai'),
        'maxTokens' => get_option('ai_chatbot_max_tokens', 150),
        'temperature' => get_option('ai_chatbot_temperature', 0.7),
      ],
      'i18n' => [
        'placeholder' => __('Type your message...', 'ai-chatbot'),
        'sendButton' => __('Send', 'ai-chatbot'),
        'errorMessage' => __('An error occurred. Please try again.', 'ai-chatbot'),
        'thinking' => __('AI is thinking...', 'ai-chatbot'),
      ]
    ]);
  }

  /**
   * Render the chatbot shortcode.
   *
   * @param array $atts
   * @return string
   */
  public function render($atts)
  {
    if (!is_user_logged_in() && !get_option('ai_chatbot_public_access', false)) {
      return '<p>' . esc_html__('Please log in to use the chatbot.', 'ai-chatbot') . '</p>';
    }

    return '<div id="ai-chatbot-root" class="ai-chatbot-wp"></div>';
  }
}
