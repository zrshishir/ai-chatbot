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
    wp_enqueue_style(
      'ai-chatbot-frontend',
      Plugin::get_plugin_url() . 'assets/css/frontend.css',
      [],
      Plugin::get_version()
    );

    wp_enqueue_script(
      'ai-chatbot-frontend',
      Plugin::get_plugin_url() . 'assets/js/frontend.js',
      ['react', 'react-dom'],
      Plugin::get_version(),
      true
    );

    wp_localize_script('ai-chatbot-frontend', 'aiChatbotFrontend', [
      'apiUrl' => rest_url('ai-chatbot/v1'),
      'nonce' => wp_create_nonce('wp_rest')
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
    if (!is_user_logged_in()) {
      return '<p>Please log in to use the chatbot.</p>';
    }

    return '<div id="ai-chatbot-root"></div>';
  }
}
