<?php

namespace AiChatbot;

/** 
 * Class AiChatbot
 *
 * Main class for the AI Chatbot plugin
 */
class AiChatbot
{
  /**
   * Constructor
   */
  public function __construct()
  {
    add_action('init', array($this, 'register_shortcode'));
  }
  /**
   * Register shortcode for the chat interface
   */
  public function register_shortcode()
  {
    add_shortcode('ai_chatbot', array($this, 'render_chat_interface'));
  }

  /**
   * Render the chat interface
   *
   * @return string The HTML for the chat interface
   */
  public function render_chat_interface()
  {
    wp_enqueue_style('ai-chatbot-style', plugin_dir_url(dirname(__FILE__)) . 'assets/css/ai-chatbot.css', array(), $this->version);
    wp_enqueue_script('ai-chatbot-script', plugin_dir_url(dirname(__FILE__)) . 'assets/js/frontend.js', array('jquery'), $this->version, true);

    wp_localize_script('ai-chatbot-script', 'aiChatbotData', array(
      'ajaxUrl' => rest_url('ai-chatbot/v1/chat'),
      'nonce' => wp_create_nonce('wp_rest'),
    ));

    ob_start();
?>
    <div id="ai-chatbot-root"></div>
<?php
    return ob_get_clean();
  }
}
