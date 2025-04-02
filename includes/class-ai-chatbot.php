<?php

namespace AiChatbot;

use AiChatbot\Services\ContentExtractor;
use AiChatbot\Services\EmbeddingGenerator;
use AiChatbot\Services\ChatInterface;

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
   * Chat interface service
   *
   * @var ChatInterface
   */
  private $chat_interface;

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
      define('AICB_PLUGIN_FILE', dirname(dirname(dirname(__FILE__))));
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
    $this->chat_interface = new ChatInterface();
    $this->chat_interface->init();

    // Initialize REST API endpoints
    add_action('rest_api_init', function () {
      $chat_endpoint = new Rest\ChatEndpoint();
      $chat_endpoint->register_routes();
    });
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
   * Get chat interface service
   *
   * @return ChatInterface
   */
  public function get_chat_interface()
  {
    return $this->chat_interface;
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
    return $this->chat_interface->render();
  }
}
