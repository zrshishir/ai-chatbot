<?php

/**
 * Plugin Name: AI Chatbot
 * Plugin URI: https://example.com/ai-chatbot
 * Description: A chatbot that can intelligently answer questions based on your website content.
 * Version: 1.0.0
 * Author: Ziaur Rahman
 * Author URI: https://zrshishir.github.io
 * Text Domain: ai-chatbot
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
	exit;
}

// Autoloader
spl_autoload_register(function ($class) {
	$prefix = 'AiChatbot\\';
	$base_dir = plugin_dir_path(__FILE__) . 'includes/';

	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		return;
	}

	$relative_class = substr($class, $len);
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

	if (file_exists($file)) {
		require $file;
	}
});

// Initialize plugin
function ai_chatbot_init()
{
	// Initialize components
	$api = new \AiChatbot\Routes\Api();
	$api->init();

	$settings = new \AiChatbot\Admin\Settings();
	$settings->init();

	$chatbot = new \AiChatbot\Shortcodes\Chatbot();
	$chatbot->init();

	// Run database migrations
	$migrations = [
		new \AiChatbot\Database\Migrations\CreateChatHistoryTable(),
		new \AiChatbot\Database\Migrations\CreatePageEmbeddingsTable(),
		new \AiChatbot\Database\Migrations\CreateSettingsTable()
	];

	foreach ($migrations as $migration) {
		$migration->up();
	}
}
add_action('plugins_loaded', 'ai_chatbot_init');

// Activation hook
register_activation_hook(__FILE__, function () {
	// Create necessary database tables
	ai_chatbot_init();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function () {
	// Clean up if necessary
});
