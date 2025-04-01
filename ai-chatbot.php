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

// Include Composer autoloader
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Include main plugin class
require_once plugin_dir_path(__FILE__) . 'includes/class-ai-chatbot.php';

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
	$prefix = 'AiChatbot\\';
	$base_dir = plugin_dir_path(__FILE__);

	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		return;
	}

	$relative_class = substr($class, $len);

	// Check in includes directory first
	$file = $base_dir . 'includes/' . str_replace('\\', '/', $relative_class) . '.php';
	if (file_exists($file)) {
		require $file;
		return;
	}

	// Then check in database directory
	$file = $base_dir . 'database/' . str_replace('\\', '/', $relative_class) . '.php';
	if (file_exists($file)) {
		require $file;
		return;
	}
});

/**
 * Run database migrations
 */
function ai_chatbot_run_migrations()
{
	global $wpdb;

	// Ensure we have database access
	if (!isset($wpdb) || !$wpdb instanceof wpdb) {
		error_log('AI Chatbot: Database connection not available');
		return;
	}

	// Load WordPress upgrade functions
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	if (!function_exists('dbDelta')) {
		error_log('AI Chatbot: dbDelta function not available');
		return;
	}

	// Check if migrations have been run
	$migrations_run = get_option('ai_chatbot_migrations_run', false);
	if ($migrations_run) {
		error_log('AI Chatbot: Migrations already run, skipping');
		return;
	}

	error_log('AI Chatbot: Starting migrations...');

	// Run migrations in order
	$migrations = [
		new \AiChatbot\Database\Migrations\CreateSettingsTable(),
		new \AiChatbot\Database\Migrations\CreateChatHistoryTable(),
		new \AiChatbot\Database\Migrations\CreatePageEmbeddingsTable(),
		new \AiChatbot\Database\Migrations\CreateChatTables(),
		new \AiChatbot\Database\Migrations\Accounts()
	];

	foreach ($migrations as $migration) {
		try {
			error_log('AI Chatbot: Running migration: ' . get_class($migration));
			$migration->up();
			error_log('AI Chatbot: Successfully completed migration: ' . get_class($migration));
		} catch (\Exception $e) {
			error_log('AI Chatbot Migration Error in ' . get_class($migration) . ': ' . $e->getMessage());
			error_log('AI Chatbot Migration Stack Trace: ' . $e->getTraceAsString());
			// Don't mark migrations as run if there was an error
			return;
		}
	}

	// Verify tables exist
	$tables = [
		$wpdb->prefix . 'aicb_settings',
		$wpdb->prefix . 'aicb_chat_history',
		$wpdb->prefix . 'aicb_page_embeddings',
		$wpdb->prefix . 'aicb_chat_sessions',
		$wpdb->prefix . 'aicb_chat_messages'
	];

	$all_tables_exist = true;
	foreach ($tables as $table) {
		$exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
		if ($exists) {
			error_log("AI Chatbot: Table $table exists");
		} else {
			error_log("AI Chatbot: Table $table does NOT exist");
			$all_tables_exist = false;
		}
	}

	// Only mark migrations as run if all tables exist
	if ($all_tables_exist) {
		update_option('ai_chatbot_migrations_run', true);
		error_log('AI Chatbot: Completed all migrations successfully');
	} else {
		error_log('AI Chatbot: Some tables were not created successfully');
	}
}

// Initialize plugin
function ai_chatbot_init()
{
	static $initialized = false;

	if ($initialized) {
		return;
	}

	$initialized = true;

	// Initialize the main plugin class first to define constants
	$plugin = new \AiChatbot\AiChatbot();
	$plugin->__construct();

	// Initialize components
	$api = new \AiChatbot\Routes\Api();
	$api->init();

	$settings = new \AiChatbot\Admin\Settings();
	$settings->init();

	$chatbot = new \AiChatbot\Shortcodes\Chatbot();
	$chatbot->init();
}

// Run migrations on activation
register_activation_hook(__FILE__, 'ai_chatbot_run_migrations');

// Initialize plugin on plugins_loaded
add_action('plugins_loaded', 'ai_chatbot_init');

// Deactivation hook
register_deactivation_hook(__FILE__, function () {
	// Clean up if necessary
});
