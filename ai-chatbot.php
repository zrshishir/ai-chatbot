<?php
/**
 * Plugin Name: AI Chatbot
 * Description: A chatbot for WordPress.
 * Author: Prappo
 * Author URI: https://prappo.github.io
 * License: GPLv2
 * Version: 1.0.0
 * Text Domain: ai-chatbot
 * Domain Path: /languages
 *
 * @package AI Chatbot
 */

use AiChatbot\Core\Install;

defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'plugin.php';

/**
 * Initializes the AiChatbot plugin when plugins are loaded.
 *
 * @since 1.0.0
 * @return void
 */
function ai_chatbot_init() {
	AiChatbot::get_instance()->init();
}

// Hook for plugin initialization.
add_action( 'plugins_loaded', 'ai_chatbot_init' );

// Hook for plugin activation.
register_activation_hook( __FILE__, array( Install::get_instance(), 'init' ) );
