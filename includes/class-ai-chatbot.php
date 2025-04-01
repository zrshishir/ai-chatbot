<?php

namespace AiChatbot;

use AiChatbot\Services\ContentExtractor;
use AiChatbot\Services\EmbeddingGenerator;

/** 
 * Class AiChatbot
 *
 * Main class for the AI Chatbot plugin
 */
class AiChatbot
{
  /**
   * Plugin version
   */
  private $version = '1.0.0';

  /**
   * Content extractor service
   *
   * @var ContentExtractor
   */
  private $content_extractor;

  /**
   * Embedding generator service
   *
   * @var EmbeddingGenerator
   */
  private $embedding_generator;

  /**
   * Constructor
   */
  public function __construct()
  {
    // Define plugin constants if not already defined
    if (!defined('AICB_VERSION')) {
      define('AICB_VERSION', $this->version);
    }
    if (!defined('AICB_PLUGIN_FILE')) {
      define('AICB_PLUGIN_FILE', dirname(dirname(__FILE__)));
    }
    if (!defined('AICB_DIR')) {
      define('AICB_DIR', plugin_dir_path(AICB_PLUGIN_FILE));
    }
    if (!defined('AICB_URL')) {
      define('AICB_URL', plugin_dir_url(AICB_PLUGIN_FILE));
    }
    if (!defined('AICB_ASSETS_URL')) {
      define('AICB_ASSETS_URL', AICB_URL . '/assets');
    }
    if (!defined('AICB_ROUTE_PREFIX')) {
      define('AICB_ROUTE_PREFIX', 'ai-chatbot/v1');
    }

    // Initialize services
    $this->init_services();

    add_action('init', array($this, 'register_shortcode'));
  }

  /**
   * Initialize services
   *
   * @return void
   */
  private function init_services()
  {
    $this->content_extractor = new ContentExtractor();
    $this->embedding_generator = new EmbeddingGenerator();
  }

  /**
   * Get content extractor service
   *
   * @return ContentExtractor
   */
  public function get_content_extractor()
  {
    return $this->content_extractor;
  }

  /**
   * Get embedding generator service
   *
   * @return EmbeddingGenerator
   */
  public function get_embedding_generator()
  {
    return $this->embedding_generator;
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
